<?php

TODO DCMM / TO BE DELETED / DELETE

/**
* Importations de données géographiques
*
* @copyright (c) Dominique Cavailhez 2016
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

/*
http://localhost/GeoBB/GeoBB319/ext/Dominique92/Reference/import.php?bbox=5,45,6,46
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
/*
$bboxs = explode (',', request_var ('bbox', '-180,-90,180,90'));
$bbox =
	$bboxs[0].' '.$bboxs[1].','.
	$bboxs[2].' '.$bboxs[1].','.
	$bboxs[2].' '.$bboxs[3].','.
	$bboxs[0].' '.$bboxs[3].','.
	$bboxs[0].' '.$bboxs[1];
*/

// Corrige le type de colonne de geom si la table vient d'être crée
$sql = 'SHOW columns FROM geo_reference LIKE "geom"';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if ($row['Type'] == 'text')
	$db->sql_query('ALTER TABLE geo_reference CHANGE geom geom POINT NULL');

// Recherche les numéros des forum avec icones
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
/*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>forums = ".var_export($forums,true).'</pre>';

// Import WRI (dans la bbox vue)
if (!$c2c) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export("http://www.refuges.info/api/bbox?format=xml&bbox=$bbox".($reset ? '&nb_points=all' : ''),true).'</pre>';
	$xml = simplexml_load_file("http://www.refuges.info/api/bbox?format=xml&nb_coms=100&detail=simple&format_texte=bbcode&bbox=$bbox".($reset ? '&nb_points=all' : ''));
	foreach ($xml AS $x)
		if ($x->count()) {
			preg_match('/([a-z]+)/i', $x->type->icone, $icones);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>{$x->id} = ".var_export($forums[$icones[1]],true).'</pre>';
			if ($f = @$forums[$icones[1]])
				$sql_values[] = implode (',', [
					'"'.str_replace ('"', '\\"', $x->nom).'"',
					$f,
					"GeomFromText('POINT({$x->coord->long} {$x->coord->lat})',0)",
					'"http://www.refuges.info/point/'.$x->id.'"',
					time(),
				]);
			else if (!@$sans_icone[$icones[1]]++)
				echo"<pre>Icone WRI inconnue ({$icones[1]}) ".var_export($x->type,true).'</pre>';

			$sql_values_wric[] = implode (',', [
				(int) $x->coms->node->id,
				'"'.str_replace ('"', '\\"', $x->coms->node->texte).'"',
				strtotime ($x->coms->node->date)?:0,
				'"'.(@$x->coms->node->photo->originale).'"',
				strtotime (@$x->coms->node->photo->date)?:0,
				'"'.@$x->coms->node->createur.'"',
				'"http://www.refuges.info/point/'.$x->id.'"',
			]);
		}
		$sql = "
			INSERT INTO geo_wric (id, texte, date, photo, date_photo, auteur, url)
			VALUES \n(".implode ("),\n(", $sql_values_wric).")
			ON DUPLICATE KEY UPDATE
				id=VALUES(id),
				texte=VALUES(texte),
				date=VALUES(date),
				photo=VALUES(photo),
				date_photo=VALUES(date_photo),
				auteur=VALUES(auteur),
				url=VALUES(url)
		";
		$db->sql_query($sql);

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export((int)$x->id,true).'</pre>';
/*
INSERT INTO geo_wric (id, texte, date, photo, date_photo, auteur, point) VALUES ('5', 'zzz', '0', 'azr', '0', 'zra', '86');
*/
			//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql,true).'</pre>';
if(0){/////////////////////
	// Import commentaires
//	$xmlWRI = simplexml_load_file('http://www.refuges.info/api/bbox?bbox=world&nb_points=all&nb_coms=10000&detail=simple&format_texte=texte&format=xml');
//TODO DCMM fusionner avec l'appel ci dessus
//TODO utiliser l'extraction des derniers commentaires
	$xmlWRI = simplexml_load_file('http://www.refuges.info/api/bbox?bbox=world&nb_points=all&nb_coms=10&detail=simple&format_texte=texte&format=xml');
	foreach ($xmlWRI AS $x)
		if ($x->count()) {
	foreach ($x AS $k => $v)
/*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>$k = ".var_export($v,true).'</pre>';
continue;

			$p = arrayXML ($x);
			$ten = $p['type']['valeur'].$p['etat']['id'].((int)$p['places']['valeur']>0?'':'0');
			if (isset ($traduction_type [$ten]))
				$data_gen ['info'] ['wri'.$p['id']] = array (
					'lien' => 'http://www.refuges.info/point/'.$p['id'],
					'titre' => ucfirst (utf8_decode ($p['nom'])),
					'type' => $traduction_type [$ten],
		//			'massif' => 'BÃ©arn',  // DCMM demander export massif
		// DCMM :>+ originale & ?? sont vides
					'altitude' => $p['coord']['alt'],
					'lat' => $p['coord']['lat'],
					'lon' => $p['coord']['long'],
					'places' => $p['places']['valeur'],
					'auteur' => $p['createur']['nom'],
					'date' => strtotime ($p['date']['creation']),
				);
			else
				$typeinconnu [$ten] = 'inconnu';

			foreach ($p['coms'] AS $c)
				if (is_array ($c) && 
					isset ($auteurs_comment [strtolower($c['createur']['nom'])]) &&
					!strpos ($c['texte'], 'http://chemineur.fr/point/') //DCMM TODO voir https ???
					)
					$data_gen ['info'] ['wric'.$c['id']] = array (
						'modif' => '20070720110159',
						'point' => 'wri'.$p['id'],
						'origine' => 'http://refuges.info/point/'.$p['id'].'#C'.$c['id'],
						'texte' => ucfirst (utf8_decode ($c['texte'])),
						'auteur' => ucfirst ($c['createur']['nom']),
						'image' => $c['photo']['nb']
							? 'http://www.refuges.info/photos_points/'.$c['id']. ($c['id'] > 7479 ? '-originale' : '') .'.jpeg'
							: null,
					);
		}
}////////////////
}

// Calcul du délai depuis le dernièr import
$sql = 'SELECT last_update FROM geo_reference WHERE url LIKE "%pyrenees%"';
$result = $db->sql_query($sql);
$date_prc = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

// Import PRC (une fois par jour)
if (!$c2c)
	if ($reset ||
		(time() - $date_prc['last_update'] > 24 * 3600)) {
		eval (str_replace ('var ', '$', file_get_contents ('http://www.pyrenees-refuges.com/lib/refuges.js')));
		foreach ($addressPoints AS $k=>$v)
			$sql_values[] = implode (',', [
				'"'.$v[2].'"',
				$forums['cabane'],
				"GeomFromText('POINT({$v[0]} {$v[1]})',0)",
				'"http://www.pyrenees-refuges.com/fr/affiche.php?numenr='.$v[3].'"',
				time(),
			]);
	}

// Import C2C
?>
	<a href="import.php?c2c=huts">Import C2C huts</a>
	<a href="import.php?c2c=summits">Import C2C summits</a>
<?php
if ($reset ||
	$c2c ||
	(time() - $date_prc['last_update'] > 24 * 3600)) {

	$nbc2c = [
		'huts' => $c2c == 'huts' ? 80 : 1,
		'summits' => $c2c == 'summits' ? 190 : 1,
	];

	foreach (array_keys ($nbc2c) AS $type) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>$type = ".var_export($nbc2c,true).'</pre>';
		for ($page = 1; $page <= $nbc2c[$type]; $page++) {
			$c2cxml = new SimpleXMLElement (str_replace ('geo:', '', (file_get_contents ("http://www.camptocamp.org/$type/rss/npp/100/page/$page"))));
			foreach ($c2cxml->channel->item AS $c) {
				$ds = explode (' - ', $c->description);
				$ls = explode ('/', $c->link);

				if ($f = @$forums[$ds[1]])
					$sql_values[] = implode (',', [
						'"'.str_replace("\"", "\\\"", html_entity_decode ($c->title, ENT_QUOTES)).'"',
						$f,
						"GeomFromText('POINT({$c->long} {$c->lat})',0)",
						'"http://www.camptocamp.org/'.$type.'/'.$ls[4].'"',
						time(),
					]);
				else if (!@$sans_icone[$ds[1]]++)
					echo"<pre>Icone C2C inconnue ".var_export($ds[1],true).'</pre>';
				}
			}
		}
}

// Fin des imports, transfert dans la base
if (isset ($sql_values)) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql_values,true).'</pre>';
	$sql = "
		INSERT INTO geo_reference (post_subject, forum_id, geom, url, last_update)
		VALUES \n(".implode ("),\n(", $sql_values).")
		ON DUPLICATE KEY UPDATE
			post_subject=VALUES(post_subject),
			forum_id=VALUES(forum_id),
			geom=VALUES(geom),
			last_update=VALUES(last_update)
	";
	/*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql,true).'</pre>';
	$db->sql_query($sql);
}

//file_put_contents ('TOTO.LOG', 'aaaaaaaaaaaaaaaaaaa'.var_export(request_var ('bbox', ''),true));
