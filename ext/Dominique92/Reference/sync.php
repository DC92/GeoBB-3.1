<?php
/**
* Importations de données géographiques
*
* @copyright (c) Dominique Cavailhez 2016
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/*
http://localhost/GeoBB/GeoBB319/ext/Dominique92/Reference/sync.php?bbox=5,45,6,46
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$bbox = request_var ('bbox', 'world');
$reset = request_var ('reset', false);
$c2c = request_var ('c2c', '');

//-------------------------------------------------------------------------
ignore_user_abort (true);

//-------------------------------------------------------------------------
// Numéros des forum avec icones
$sql = 'SELECT forum_id, forum_image FROM '.FORUMS_TABLE.' WHERE forum_image != ""';
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result)) {
	preg_match('/([a-z_]+)\./i', $row['forum_image'], $m);
	if (count ($m))
		$forums[$m[1]] = $row['forum_id'];
}
$forums += [
	'gîte' => $forums['gite'],
	'gÃ®te' => $forums['gite'],
	'point culminant' => $forums['sommet'],
	'abri sommaire' => $forums['abri'],
	'emplacement de bivouac' => $forums['bivouac'],
	'camp de base' => $forums['bivouac'],
	'point' => $forums['point_eau'],
	'source' => $forums['point_eau'],
	'inutilisable' => $forums['ferme'],
	'batiment' => $forums['inconnu'],
	'refuge-garde' => $forums['refuge'],
	'' => $forums['inconnu'],
];
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>forums = ".var_export($forums,true).'</pre>';

//-------------------------------------------------------------------------
// Liste des users
$sql = "SELECT user_id, username, username_clean FROM phpbb_users";
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
	$users [$row ['username_clean']] = $row;

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($users,true).'</pre>';

//-------------------------------------------------------------------------
/*
//$bboxs = explode (',', request_var ('bbox', '-180,-90,180,90'));
$bboxs = explode (',', $bbox = request_var ('bbox', ''));
if (count ($bboxs) == 4) {
	$bboxexp =
		$bboxs[0].' '.$bboxs[1].','.
		$bboxs[2].' '.$bboxs[1].','.
		$bboxs[2].' '.$bboxs[3].','.
		$bboxs[0].' '.$bboxs[3].','.
		$bboxs[0].' '.$bboxs[1];
}*/
//-------------------------------------------------------------------------
// Exécution de la commande
$log [] = date('r');
$log [] = $_request->server('REQUEST_SCHEME').'://'.$_request->server('HTTP_HOST').$_request->server('REQUEST_URI');

$fnc = 'geo_'.request_var('cmd', '');
if ($source = request_var('source', ''))
	$fnc .= '_'.$source;
if (function_exists ($fnc))
	$fnc ();
else {
	geo_sync_wri (request_var ('bbox', ''));
	geo_sync_prc (date_last_sync ('pyrenees'));
	geo_sync_c2c ('huts', date_last_sync ('camptocamp'));
//	geo_sync_c2c ('summits', date_last_sync ('camptocamp'));
}

file_put_contents ('../../../SYNC.log', implode (' ', $log) ."\n", FILE_APPEND);

//-------------------------------------------------------------------------
// Output page
page_header('Synchronisation autres sites', true);

$template->set_filenames(array(
	'body' => 'sync_body.html')
);

page_footer();

//-------------------------------------------------------------------------
// FONCTIONS
//-------------------------------------------------------------------------
function geo_sync_wri ($bbox = 'world') {
	global $forums, $users, $log;

	$wri_upd = $wric_upd = [];
	$log [] = 'SYNC.PHP';
	$log [] = $urlWRI = "http://www.refuges.info/api/bbox?bbox=$bbox&nb_coms=100&detail=simple&format_texte=texte&format=xml";
	$xmlWRI = simplexml_load_file($urlWRI);
	foreach ($xmlWRI AS $x) {
		preg_match('/([a-z]+)/i', $x->type->icone, $icones);
		if ($f = @$forums[$icones[1]])
			$wri_upd [] = [
				'post_subject' => '"'.str_replace ('"', '\\"', $x->nom).'"',
				'forum_id' => $f,
				'geom' => "GeomFromText('POINT({$x->coord->long} {$x->coord->lat})',0)",
				'url' => '"http://www.refuges.info/point/'.$x->id.'"',
				'last_update' => time(),
			];
		else if (!@$sans_icone[$icones[1]]++)
			echo"<pre>Icone WRI inconnue ({$icones[1]}) ".var_export($x->type,true).'</pre>';

		if ($x->coms)
			foreach ($x->coms->node AS $c) {
				$noms = explode (' ', strtolower (@$c->createur->nom));
				if ($u = @$users[$noms[count($noms)-1]]) {
					$logWriId [(int)$c->id] = true;
					$wric_upd [] = [
						'id'               => (int) $c->id,
						'texte'            => '"'.str_replace ('"', '\\"', $c->texte).'"',
						'date'             => strtotime($c->date) ?: 0,
						'photo'            => '"'.@$c->photo->originale.'"',
						'date_photo'       => strtotime(@$c->photo->date) ?: 0,
						'auteur'           => '"'.$c->createur->nom.'"',
						'url'              => '"http://www.refuges.info/point/'.$x->id.'"',
						'wric_last_update' => time(),
					];
				}
			}
	}
	sql_update_table ('geo_reference', $wri_upd);
	sql_update_table ('geo_wric', $wric_upd);
}
//-------------------------------------------------------------------------
function geo_sync_c2c ($type = 'huts', $last_date = 0) {
	global $forums, $template;

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>XXXX = ".var_export(date('r',$last_date),true).'</pre>';

	if (time() - $last_date > 24 * 3600) { // Une fois par jour
		$type = request_var ('type', $type);
		$page = request_var ('page', 1);
		$c2cxml = new SimpleXMLElement (str_replace ('geo:', '', (file_get_contents ("http://www.camptocamp.org/$type/rss/npp/100/page/$page"))));
		foreach ($c2cxml->channel->item AS $c) {
			$ds = explode (' - ', $c->description);
			$ls = explode ('/', $c->link);

			if ($f = @$forums[$ds[1]])
				$sql_values[] = [
					'post_subject' => '"'.str_replace("\"", "\\\"", html_entity_decode ($c->title, ENT_QUOTES)).'"',
					'forum_id'     => $f,
					'geom'         => "GeomFromText('POINT({$c->long} {$c->lat})',0)",
					'url'          => '"http://www.camptocamp.org/'.$type.'/'.$ls[4].'"',
					'last_update'  => time(),
				];
			else if (!@$sans_icone[$ds[1]]++)
				echo"<pre>Icone C2C inconnue ".var_export($ds[1],true).'</pre>';
		}
		sql_update_table ('geo_reference', $sql_values);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export(count($c2cxml->channel->item),true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql_values,true).'</pre>';

		if (isset ($_GET['repeat']) &&
			count($c2cxml->channel->item) == 100) {
			$template->assign_var('REPEAT', "1;url=sync.php?cmd=sync_c2c&type=$type&repeat&page=".($page+1));
		}
	}
}
//-------------------------------------------------------------------------
function geo_sync_prc ($last_date = 0) {
	global $forums;

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export((time() - $last_date)/ 3600,true).'</pre>';

	if (time() - $last_date > 24 * 3600) { // Une fois par jour
		eval (str_replace ('var ', '$', file_get_contents ('http://www.pyrenees-refuges.com/lib/refuges.js')));
		$sql_values = [];
		foreach ($addressPoints AS $k=>$v)
			$sql_values[] = [
				'post_subject' => '"'.$v[2].'"',
				'forum_id'     => $forums['cabane'],
				'geom'         => "GeomFromText('POINT({$v[0]} {$v[1]})',0)",
				'url'          => '"http://www.pyrenees-refuges.com/fr/affiche.php?numenr='.$v[3].'"',
				'last_update'  => time(),
			];
		sql_update_table ('geo_reference', $sql_values);
	}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql_values,true).'</pre>';
}
//-------------------------------------------------------------------------
function sql_update_table ($table, $lignes) {
	global $db, $log;

	$logid = [];
	if (count ($lignes)) {
		foreach ($lignes AS $ligne) {
			$sql_values[] = implode (',', $ligne);
			if (isset ($ligne['id']))
				$logid[] = $ligne['id'];
		}
		foreach ($ligne AS $k=>$v)
			$sql_eq[] = "$k=VALUES($k)";

		$sql = "INSERT INTO $table (".implode (',', array_keys ($ligne)).")
			VALUES \n(".implode ("),\n(", $sql_values).")
			ON DUPLICATE KEY UPDATE ".implode (',', $sql_eq);
		$db->sql_query($sql);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>SQL = ".var_export($sql,true).'</pre>';

		$log [] = $table.' = '.count($lignes);
		$log [] = implode ('|', array_keys ($logid));
	}
}
//-------------------------------------------------------------------------
// Calcul du délai depuis le dernier sync PRC
function date_last_sync ($site) {
	global $db;

	$sql = "SELECT last_update FROM geo_reference WHERE url LIKE '%$site%' ORDER BY last_update DESC";
	$result = $db->sql_query($sql);
	$r =
		$result
		? $db->sql_fetchrow($result) ['last_update']
		: 0;
	$db->sql_freeresult($result);

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>Dernier update : ".var_export(date('r',$r),true).'</pre>';
	return $r;
}
