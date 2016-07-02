<?php
/**
* Extractions de données géographiques
*
* @copyright (c) Dominique Cavailhez 2015
* @license GNU General Public License, version 2 (GPL-2.0)
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// https://geophp.net/api.html
include_once('geoPHP/geoPHP.inc'); // Librairie de conversion WKT <-> geoJson

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$priority = request_var ('priority', 0); // Topic à affichage prioritaire
$limite = request_var ('limite', 250); // Nombre de points maximum
$bboxs = explode (',', request_var ('bbox', '-180,-90,180,90'));
$bbox =
	$bboxs[0].' '.$bboxs[1].','.
	$bboxs[2].' '.$bboxs[1].','.
	$bboxs[2].' '.$bboxs[3].','.
	$bboxs[0].' '.$bboxs[3].','.
	$bboxs[0].' '.$bboxs[1];

$diagBbox = hypot ($bboxs[2] - $bboxs[0], $bboxs[3] - $bboxs[1]);

$sql_array = [
	'SELECT' => [
		'post_subject',
		'f.forum_id',
		't.topic_id',
		'post_id',
		'forum_image',
		'forum_desc',
		'AsText(geom) AS geomwkt',
	],
	'FROM' => [POSTS_TABLE => 'p'],
	'LEFT_JOIN' => [[
		'FROM' => [TOPICS_TABLE => 't'],
		'ON' => 't.topic_id = p.topic_id',
	],[
		'FROM' => [FORUMS_TABLE => 'f'],
		'ON' => 'f.forum_id = p.forum_id',
	]],
	'WHERE' => [
		'geom IS NOT NULL',
		"Intersects (GeomFromText ('POLYGON (($bbox))'),geom)",
		'post_visibility = '.ITEM_APPROVED,
		'OR' => [
			't.topic_first_post_id = p.post_id',
			'forum_desc LIKE "%[all=%"',
		],
	],
	'ORDER_BY'	=> "CASE WHEN f.forum_id = $priority THEN 0 ELSE left_id END",
];

/**
 * Change SQL query for fetching geographic data
 *
 * @event geo.sync_modify_sql
 * @var array     sql_array    Fully assembled SQL query with keys SELECT, FROM, LEFT_JOIN, WHERE
 */
$vars = array(
	'sql_array',
);
extract($phpbb_dispatcher->trigger_event('geo.sync_modify_sql', compact($vars)));

// Build query
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>SQL_ARRAY = ".var_export($sql_array,true).'</pre>';
if (is_array ($sql_array ['SELECT']))
	$sql_array ['SELECT'] = implode (',', $sql_array ['SELECT']);

if (is_array ($sql_array ['WHERE'])) {
	foreach ($sql_array ['WHERE'] AS $k=>&$w)
		if (is_array ($w))
			$w = '('.implode (" $k ", $w).')';
	$sql_array ['WHERE'] = implode (' AND ', $sql_array ['WHERE']);
}
$sql = $db->sql_build_query('SELECT', $sql_array);
$result = $db->sql_query_limit($sql, $limite);
//*DCMM*/echo var_export($sql,true)."\n";

// Ajoute l'adresse complète aux images d'icones
$sp = explode ('/', getenv('REQUEST_SCHEME'));
$ri = explode ('/ext/', getenv('REQUEST_URI'));
$bu = $sp[0].'://'.getenv('SERVER_NAME').$ri[0].'/';

$gjs = [];
while ($row = $db->sql_fetchrow($result)) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>ROW = ".var_export($row,true).'</pre>';

	$properties = [
		'nom' => $row['post_subject'],
		'id' => $row['topic_id'],
		'type_id' => $row['forum_id'],
		'post_id' => $row['post_id'],
		'icone' => $bu.$row['forum_image'],
	];

	preg_match('/\[color=([a-z]+)\]/i', html_entity_decode ($row['forum_desc']), $colors);
	if (count ($colors))
		$properties['color'] = $colors[1];

	/**
	 * Change properties before sending
	 *
	 * @event geo.sync_modify_properties
	 * @var array     sql_array    Fully assembled SQL query with keys SELECT, FROM, LEFT_JOIN, WHERE
	 */
	$vars = array(
		'row',
		'properties',
	);
	extract($phpbb_dispatcher->trigger_event('geo.sync_modify_properties', compact($vars)));

	$g = geoPHP::load ($row['geomwkt'], 'wkt'); // On lit le geom en format WKT forni par MySql
	$gj = $g->out('json'); // On le transforme en format GeoJson
	$gp = json_decode ($gj); // On transforme le GeoJson en objet PHP
	optim ($gp); // On l'optimise
	$gjs[] = [
		'type' => 'Feature',
		'geometry' => $gp, // On ajoute le tout à la liste à afficher sous la forme d'un "Feature" (Sous forme d'objet PHP)
		'properties' => $properties,
	];
}
$db->sql_freeresult($result);

function optim (&$g) { // Fonction récursive d'optimisation d'un objet PHP contenant des objets géographiques
	if (isset ($g->geometries)) // On recurse sur les Collection, ...
		foreach ($g->geometries AS &$gs)
			optim ($gs);

	if (isset ($g->features)) // On recurse sur les Feature, ...
		foreach ($g->features AS &$fs)
			optim ($fs);

	if (preg_match ("/multi/i", $g->type)) {
		foreach ($g->coordinates AS &$gs)
			optim_coordinate_array ($gs);
	} elseif (isset ($g->coordinates)) // On a trouvé une liste de coordonnées à optimiser
		optim_coordinate_array ($g->coordinates);
}
function optim_coordinate_array (&$cs) { // Fonction d'optimisation d'un tableau de coordonnées
	global $diagBbox;

	if (count ($cs) > 2) { // Pour éviter les "Points" et "Poly" à 2 points
		$p = $cs[0]; // On positionne le point de référence de mesure de distance à une extrémité
		$r = []; // La liste de coordonnées optimisées
		foreach ($cs AS $k=>$v)
			if (!$k || // On garde la première extrémité
				$k == count ($cs) - 1) // Et la dernière
				$r[] = $v;
			elseif (hypot ($v[0] - $p[0], $v[1] - $p[1]) > $diagBbox / 200) // La granularité sera de 1/200 de la diagonale de la BBOX
				$r[] = // On copie ce point
				$p = // On repositionne le point de référence
					$v;
		$cs = $r; // On écrase l'ancienne
	}
}

// Formatage du header
$secondes_de_cache = 60;
$ts = gmdate("D, d M Y H:i:s", time() + $secondes_de_cache) . " GMT";
header("Content-disposition: filename=points.json");
header("Content-Type: application/json; UTF-8"); // rajout du charset
header("Content-Transfer-Encoding: binary");
header("Pragma: cache");
header("Expires: $ts");
header("Access-Control-Allow-Origin: *");
header("Cache-Control: max-age=$secondes_de_cache");

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($gjs,true).'</pre>';
echo json_encode ([ // On transforme l'objet PHP en code geoJson
	'type' => 'FeatureCollection',
	'features' => $gjs
]);
