<?php
/**
* Fonctions d'import de Chemineur.fr.
*
* @copyright (c) Dominique Cavailhez 2015
* @license GNU General Public License, version 2 (GPL-2.0)
*
* DCMM TODO DELETE QUAND OPERATIONNEL
*
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_user.' . $phpEx);
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx);
include($phpbb_root_path . 'includes/acp/acp_forums.' . $phpEx);
include($phpbb_root_path . 'includes/functions_posting.' . $phpEx);
include($phpbb_root_path . 'includes/message_parser.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('posting'); // Pour avoir les messages d'erreur qui vont bien

$arg = $glob = new StdClass();
$arg->cmd = request_var('cmd', '');
$arg->source = request_var('source', '');
$arg->name = request_var('name', '');
$arg->limit = request_var('limit', 1);
$arg->repeat = request_var('repeat', 0);

//-------------------------------------------------------------------------
// Initialise les liens cliquables des actions
// Format des fonctions
// geo_init_topic -> cmd=init_topic
// geo_import_source_nom_site

$fncs = get_defined_functions(); // Liste les functions de ce source.php
foreach($fncs['user'] AS $f) {
	$fs = explode ('_', $f, 4);
	if ($fs[0] == 'geo' && @$fs[2])
		$template->assign_block_vars('cmd', array(
			'CMD1' => $fs[1],
			'CMD2' => $fs[2],
			'CMD3' => @$fs[3],
			'TXT' => str_replace ('_', ' ', @$fs[3]),
		));
}
//-------------------------------------------------------------------------
// HORRIBLE hack
$file_name = "includes/functions_posting.php";
$file_tag = "if (!@file_exists(\$phpbb_root_path . \$config['upload_path']";
$file_patch = "if(/*GEO*/0&&!@file_exists(\$phpbb_root_path . \$config['upload_path']";
$file_content = file_get_contents_T ($file_name);
if (strpos($file_content, $file_tag))
	file_put_contents ($file_name, str_replace ($file_tag, $file_patch, $file_content));

//-------------------------------------------------------------------------
// Corrige le type de colonne de geom si la table vient d'être crée
// DCMM TODO : mettre dans migration/schema...
$sql = 'SHOW columns FROM geo_reference LIKE "geom"';
$result = $db->sql_query($sql);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);
if ($row['Type'] == 'text')
	$db->sql_query('ALTER TABLE geo_reference CHANGE geom geom POINT NULL');

//-------------------------------------------------------------------------
// Liste des users
$sql = "SELECT username, username_clean FROM phpbb_users";
$result = $db->sql_query($sql);
while ($row = $db->sql_fetchrow($result))
	$users [$row ['username_clean']] = $row ['username'];

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($users,true).'</pre>';

//-------------------------------------------------------------------------
// Exécution de la commande
$fnc = 'geo_'.$arg->cmd;
if ($arg->source)
	$fnc .= '_'.$arg->source;
if (function_exists ($fnc))
	$fnc ();

//-------------------------------------------------------------------------
// Output page
page_header('Initialisation Chemineur', true);

$template->set_filenames(array(
	'body' => 'init_body.html')
);

page_footer();
//-------------------------------------------------------------------------
// INITIALISATION DE CHEMINEUR - GEO TODO DELETE AFTER -
//-------------------------------------------------------------------------
function RIEN() {
/*
INSTALLATION
============
C:/Windows/System32/drivers/etc/hosts
Ajouter à la fin
127.0.0.1       chemineur.host
127.0.0.1       tortillards.host

Créer une BDD
Créer un répertoire
Tortoise SVB checkout https://github.com/DC92/GeoBB318.git/trunk
http://www.phpbb-fr.com/telechargements -> TÉLÉCHARGER LE PACK COMPLET -> Copier dans le répertoire
Tortoise revert -> Select all
Aller sur le site -> Installation

config.php:
==> Décommenter @define('DEBUG', true);
==> Ajouter:
$config_locale = array (
    'refresh'       => 0,
    'exportchem3'   => '../../chem2/exportchem3/', // Obligatoirement sur le même serveur
    'leaflet'   	=> '../../leaflet/leaflet/src/', // Debug
	'KEY_IGN'       => 'xxxx', // chemineur.fr
	'KEY_OS'        => 'CBE047F823B5E83CE0405F0ACA6042AB', // chemineur.fr
	'KEY_BING'      => 'ArLngay7TxiroomF7HLEXCS7kTWexf1_1s1qiF7nbTYs2IkD3XLcUnvSlKbGRZxt', // chemineur.fr
	'KEY_MAPQUEST'  => 'Fmjtd%7Cluur2968n1%2C70%3Do5-90rs04', // Calcul altitude
);
include ('geobb.php');

Supprimer /install
Connecter sur administration
*/
	$sqls = array(
		//	DEBUG :
		//	GENERAL
		//		Paramètres de charge
		//			Recompiler les composants expirés des styles: oui (** pour Debug ** ) ==> EN PROD = NON
		//		Paramètres des fichiers joints
		//			Taille maximale du fichier : 5 Mo
		//	PERSONNALISER
		//		Gérer les extensions
		//			Geo for Chemineur / Activer
		//		Installer les styles
		//			Cocher chemineur + tortillards -> Installation de styles
		// FORUMS
		//		Créer un nouveau forum
		//			Copier les permissions depuis : "Votre premier forum"
		//			Nom du forum: Cabanes
		//			Description : [all=point][view=point]
		//			Image du forum : ext/Dominique92/GeoBB/types_points/cabane.png

// Aller 
//////// TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO TODO
		//	INIT.php
		//		Initialiser sql
		//		Initialiser users
		//		Initialiser forums
		//		Initialiser aide
		//		Sauvegarder -> Sauf session*
		////////////////////////////////////////////////////////////////////////////////////////////////




/*////////////////////////////////////////////////////////////////////////////////////////////////////////
OU: EN PRODUCTION
GENERAL
	Configuration du forum
		Format de la date: -> Personalisée... = j F Y H\h
		Fuseau horaire des invités: [Europe/Paris]
	Fonctionnalités du forum
		Autoriser les anniversaires : non
		Autoriser la réponse rapide : non
	Paramètres des enregistrements
		Limite de messages d’un nouveau membre : 0
		Activation de compte : => Par mail
	Paramètres de la confirmation visuelle
		Plugins installés : => Q&A
		Configurer les plugins : : configurer
			Ajouter
				Langue : Français
				Question : Quel est le chef lieu de l'Isère ?	
				Réponses : ...
	Paramètres de cookie
		Domaine du cookie : => Effacer
	Paramètres du serveur
		Activer la compression GZip : oui
		Exécuter les tâches récurrentes en utilisant le « cron » système. : oui
		Nom de domaine : => Effacer
	Paramètres de charge
		Options générales : tout non
		Champs de profil personnalisés : tout non
FORUMS
	Gérer les forums
		Editer Votre première catégorie
			Nom: Forums
		Votre première catégorie
			Editer Votre premier forum
				Nom : Aide
				Paramètres généraux du forum : tout non
					Sauf Activer l’indexation de recherche :
	Permissions des forums
		Tous les forums
			Moderateurs globaux
				Modifier les permissions
					Aide : Accés standard
MESSAGES
	Smiley
		Installer un pack de smileys
			geobb.pak
				Tout supprimer
					Installer un pack de smileys
	Paramètres des fichiers joints
		Taille maximale du fichier : 5 M
UTILISATEURS ET GROUPES
	Gérer les utilisateurs
		Sélectionner l’utilisateur invité / Envoyer
			Sélectionner un formulaire: Préférences
				Langue: Français (vouvoiement)
				Fuseau horaire des invités: [UTC + 1(2 l'été) : Europe/Paris]
		IDEM Dominique
	Permissions des groupes
		Modérateurs globaux
			Permisions des utilisateurs
				Permissions avancées
					Messages
						Peut enregistrer des brouillons : Jamais
			Permisions des modérateurs globaux
				Permissions avancées
					Actions sur les messages
						Peut supprimer définitivement un message. : Jamais
			Permisions d'administration
				Permissions avancées
					Utilisateurs & groupes
						Peut gérer les bannissements. : Oui
						Peut gérer les membres. : Oui
						Peut supprimer/trier les membres. : Oui
					Forums : tout sur oui
		Utilisateurs enregistrés
			Permisions des utilisateurs
				Permissions avancées
					Messages
						Peut enregistrer des brouillons : Jamais
PERMISSIONS
	Modèles d’utilisateur
		Fonctionnalités standards -> Editer
			Messages
				Peut enregistrer des brouillons. => Jamais
PERSONNALISER
	Gérer les extensions
		Geo for Chemineur / Activer
		Auto Database Backup / Activer
	Installer les styles
		Cocher chemineur + tortillards -> Installation de styles
MAINTENANCE
	Auto Database Backup settings
		Activé
		gzip
		Stored backups : 0
		Optimize DB before backup : Activé
		Next backup time : 3h du matin
GENERAL
	Purger le cache
MAINTENANCE
	Sauvegarder
	Sélectionner phpbb_* sauf phpbb_sessions* / Envoyer
//DCMM TODO: mettre une clé (mais pb doublon)		"ALTER TABLE geo_trace ADD PRIMARY KEY(trkpt_id)",
INIT.php
	Initialiser sql
	Initialiser users
	Initialiser forums
	Initialiser aide

Descendre Forums vers le bas
*/
	);
	foreach ($sqls AS $sql) {
		$db->sql_query($sql);
		$template->assign_block_vars('status', array('REPORT' => $sql));
	}
//DCMM	$sql = "UPDATE phpbb_forums SET enable_icons = '0' WHERE enable_icons = '1'";
}
//-------------------------------------------------------------------------
// FUNCTIONS
//-------------------------------------------------------------------------
function file_get_contents_T($f) {
/*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>FILE_GET_CONTENTS = ".var_export($f,true).'</pre>';
	return file_get_contents ($f);
}
//-------------------------------------------------------------------------
function geo_init_users() {
	global $db, $config_locale, $template, $config;

	// Importation des users de chemineur
	$users_chem = unserialize (mcrypt_decrypt(
		MCRYPT_RIJNDAEL_256,
		$config_locale ['cypher_key'],
		file_get_contents_T ("http://v2.chemineur.fr/grab.php?user"),
		MCRYPT_MODE_ECB,
		$config_locale ['cypher_iv']
	));

	// NOTE: chemineur, dominique & webmestre ==> Dominique (pas créé de remote !)

	// Ajout pseudo users
	foreach (array('moderateur', 'utilisateur', 'refuges.info', 'pyrenees-refuges.com', 'lacsdespyrenees.com', 'camptocamp.org', 'delamare', 'sanouillet') AS $u)
		$users_chem [$u] = array(
			'username' => strpos($u,'.') ? $u : ucfirst ($u),
			'user_password' => md5 ($u),
			'group_id' => $u == 'moderateur' ? 4 : 2,
			'user_type' => USER_NORMAL,
			'user_regdate' => time(),
			'user_timezone' => 1,
//			'user_dst' => 0, // Timezone
			'user_sig' => $u,
			'user_email' => 'existe-pas@'.$u,
			'user_website' => strpos($u,'.') ? 'http://'.$u : '',
		);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($users_chem,true).'</pre>';

	$default_style = $config['default_style'];
	$config['default_style'] = 1;
	$nb_points = 0;
	foreach ($users_chem AS $u) {
		$searched_users = array($u['username']);
		$ids = array();
		user_get_id_name ($ids, $searched_users);
		if (@$u['user_website']) {
			$cp_data = array ('pf_phpbb_website' => $u['user_website']);
		} else
			$cp_data = false;
		unset ($u['user_website']);
		if (!count ($ids)) {
//$u['user_email'] = 'contact@cavailhez.fr'; //DCMM TODO GEO DELETE à enlever en phase finale
			$u['user_timezone'] = 'Europe/Paris';
			if (!$u['user_sig'])
				$u['user_sig'] = $u['username'];
			if ($u['group_id'] == 2)
				$user_id = user_add ($u, $cp_data);
			else {
				$group_id = $u['group_id'];
				$u['group_id'] = 2;
				$user_id = user_add ($u, $cp_data);
				group_user_add($group_id, $user_id);
			}
			$template->assign_block_vars('status', array(
				'REPORT' => 'Import user '.$u['username'].' : '.$user_id,
			));
			$nb_points++;
		}
	}
	$config['default_style'] = $default_style;
}
//-------------------------------------------------------------------------
function geo_init_forums() {
	global $forums, $db, $config_locale, $template, $phpbb_root_path, $user;
	$liste_forum_chem = liste_forum_chem();
	$priorite = 1;
	foreach ($liste_forum_chem AS $k => $v) // Etablissement des priorités
		$liste_forum_chem [$k] ['priorite'] = ++$priorite * 100;

	// Listage forums chem3 existants
	$sql = 'SELECT * FROM phpbb_forums';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$forums [$row ['forum_name']] = $row;
		// Ajout de l'id forum correspondant aux forums chem
		foreach ($liste_forum_chem AS $k => $v)
			if ($row ['forum_name'] == $v[0])
				$liste_forum_chem [$k] ['forum_id'] = $row['forum_id'];
	}
	// Création des forums
	$nb_points = 0;
	foreach ($liste_forum_chem AS $k => $f)
		if ($f[0] != 'DETRUIRE') {
			if ($f[2])
				$template->assign_block_vars('dump', array(
					'REPORT' => cree_forum (array(
						'forum_name' => $f[2],
						'forum_type' => 0, // Catégorie de forum
						'forum_desc' => @$f[4].'',
					)),
				));
			$template->assign_block_vars('dump', array(
				'REPORT' => cree_forum (array(
					'forum_name' => $f[0],
					'parent_name' => $f[2],
					'parent_id' => $forums [$f[2]] ['forum_id'],
					'forum_desc' => @$f[3].'',
					'forum_image' => $f[1] ? 'ext/Dominique92/GeoBB/types_points/'.$f[1] : '',
					'enable_icons' => false,
				)),
			));
			$nb_points++;
		}
}
//-------------------------------------------------------------------------
function geo_init_aide() {
	cree_post (array(
		'forum_id' => 2,
		'topic_title' => '[exclude=chemineur]',
		'message' =>
"[b]Chemineur.fr[/b] est l'œuvre de quelques copains qui partagent leurs expérience de montagne, randonnée et tourisme sans liens avec une association ni société. Ce site, qui est notre passion, est dépourvu de publicité ou intérêt financier.
Chemineur est un site participatif. Vous devez vous [url=http://chemineur.fr/ucp.php?mode=register][b]inscrire[/b][/url] pour pouvoir contribuer.
Vous pouvez nous joindre à \"[i]contact+arobase+nom_du_site[/i]\"",
	));

	cree_post (array(
		'forum_id' => 2,
		'topic_title' => '[exclude=tortillards]',
		'message' =>
"[b]Tortillards.fr[/b] à pour but de recenser les lignes de chemin de fer, leur tracé, équipements, histoire et photo actuelles ou d'époques.
Nous insistons plus particulièrement sur les petites lignes oubliées ou méconnues, mais les descriptions des plus grandes lignes sont les bienvenues.
Il existe une multitude de sites au sujet des chemins de fer, souvent très bien documentés sur un domaine très précis, dont nous désirons faire la promotion en y faisant au maximum référence.
Tortillards est un site participatif. Vous devez vous [url=http://tortillards.fr/ucp.php?mode=register][b]inscrire[/b][/url] pour pouvoir contribuer.
Nous sommes quelques personnes indépendantes sans liens avec une association ni société. Ce site, qui est notre passion, est dépourvu de publicité.
Vous pouvez nous joindre à [i]\"contact+arobase+nom_du_site[/i]\"",
	));

	cree_post (array(
		'forum_id' => 2,
		'topic_title' => 'Liens avec les autres sites',
		'message' =>
"Les points des autres sites (refuges.info, pyrennes-refuges.com, camptocamp, ...) ne sont plus importés dans chemineur.
Ils sont seulement affichés sur les cartes par simple sélection de la couche et du type d'information correspondant dans les boites de contrôles en haut à droite.
Il est toutefois possible:
- De lier un point externe à une fiche chemineur: aller sur la fiche chemineur, survoler le picto externe et cliquer sur \"Lier à la fiche ...\"
Le lien apparaît en dessous de l'étiquette de la fiche et le picto externe n'est plus affiché sur la carte.
- De créer une fiche chemineur à partir des informations de ce point externe: survoler le picto externe et cliquer sur \"Créer une fiche\"
Un lien vers le point extérieur est automatiquement ajouté à la fiche crée. le picto apparaissant est alors celui de la fiche chemineur.
Il est alors possible d'éditer les informations de la fiche chemineur (et non, bien sur, celles du point externe lié pusiqu'il est sur son site d'origine).
Les fiches et les liens peuvent être supprimés à tout moment: les pictos extérieurs n'étant plus référencés réapparaitront automatiquement sur la carte.
Pour lier 2 points externes, il faut créer une fiche à partir du premier et lier le second à cette fiche.
Les liens crées dans chemineur2 ont été conservés dans chemineur3: ils sont alors modifiables comme ceux nouvellement créés."
	));
}
//-------------------------------------------------------------------------
// IMPORT CHEMINEUR
//-------------------------------------------------------------------------
function geo_init_efface_references () { // Efface la colonne reference
	global $db;
	$sql = 'UPDATE geo_reference SET topic_id = 0';
	$db->sql_query($sql);
	$sql = 'DELETE FROM geo_reference WHERE forum_id = 0';
	$db->sql_query($sql);
}
//-------------------------------------------------------------------------
function geo_init_chemineur_forum () {geoimportchemineur ();}
function geo_init_chemineur_point () {geoimportchemineur ();}
function geo_init_chemineur_diapo () {geoimportchemineur ();}
function geo_init_chemineur_trace () {geoimportchemineur ();}
function geoimportchemineur () {
	global $db, $template, $arg, $glob, $errors, $request;
	// $glob->forum_cle = array ('cle' => forum_id);
	// Populées par descripteur de forum: <cle
	// Ou par nom de l'image forum: <cle
	// Ou par la fonction d'import spécifique
	$sql = "SELECT forum_id,forum_desc,forum_image FROM phpbb_forums";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		foreach (array ('forum_desc'=>'/<([a-z_]+)/', 'forum_image'=>'/\/([a-z_]+)\./') AS $k => $v) {
			preg_match_all($v, html_entity_decode($row[$k]), $cs);
			if (count ($cs[1]))
				foreach ($cs[1] AS $c)
					$glob->forum_cle[$c] = $row['forum_id'];
		}
	}
	$db->sql_freeresult($result);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>forum_cle = ".var_export($glob->forum_cle,true).'</pre>';

	$liste_forum_chem = liste_forum_chem();

	// Recherche des importations déjà faites (posts dont 'post_edit_reason' = source01234)
	// $glob->post_geo_import = array (
	//   'source01234' => array (...post...),
	//   'source56789' => array (...post...),
	// )
	$sql = "SELECT forum_id,topic_id,post_id,post_edit_reason FROM phpbb_posts WHERE post_edit_reason !=''";
	$result = $db->sql_query($sql);
	$glob->post_geo_import = array();
	while ($row = $db->sql_fetchrow($result))
		$glob->post_geo_import[$row['post_edit_reason']] = $row;
	$db->sql_freeresult($result);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>POSTS_DEJA_IMPORTES = ".var_export($glob->post_geo_import,true).'</pre>';

	// Appel de la fonction spécialisée sur le site
	while ($arg->limit > 0 && (
		$posts = import_chemineur ($arg->source) // Rend un tableau de post suivants ou null quand il n'y en a plus
	)) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export(count ($posts),true).'</pre>';
		// Tout un topic en une seule fois, dans l'ordre
		foreach (array_values($posts) AS $k => $p) { // $k doit valoir 0, 1, 2, ...
			// $p = array (
			//		'id' => 01234 // Ce post aura une valeur post_edit_reason = source01234 
			//	ou	'id' => source01234 // Ce post aura une valeur post_edit_reason = source01234 
			//		'cle_forum' => cle du forum (mnemo)
			//		'auteur' => dominique
			// )
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>PPP = ".var_export($p,true).'</pre>';
			$p['post_edit_reason'] = "http://v2.chemineur.fr/{$arg->source}/".$p['id'];
			if (!$k) { // Le premier post du topic
				$topic_first_geo_import = $p['post_edit_reason'];
				$topic_title = $p['topic_title'];
				if (!$topic_title) {
					$template->assign_block_vars('errors', array('REPORT' => ($errors[] = "Topic $topic_first_geo_import n\'a pas de titre: ".@$v['nf'])));
					continue;
				}
			}
			$p['topic_first_geo_import'] = $topic_first_geo_import;
			if (!$p['topic_title'])
				$p['topic_title'] = 'Re: '.ucfirst(trim(@$topic_title));

			if (!isset ($glob->post_geo_import[$p['post_edit_reason']])) { // Si le post n'est pas déja importé
				// Bon, on y va !
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($p,true).'</pre>';
				if (!isset ($glob->forum_cle[$p['cle_forum']])) {
					if ($liste_forum_chem[$p['cle_forum']][0] == 'DETRUIRE') {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($p,true).'</pre>';
						$glob->post_geo_import[$p['post_edit_reason']] = true;// on note pour ne pas repasser dessus
						$a_detruire = true;
					}
					else
						$non_trouves [$p['cle_forum']] = $p;
				}
//				elseif (in_array($p['cle_forum'], array('forum7', 'forum8', 'forum9')))
//					continue; // Il n'y a pas de forums, mais on s'en fout car on ne copiera pas !
				elseif (!isset ($glob->post_geo_import[$topic_first_geo_import])) // C'est un nouveau topic
					$p = // Récupère le topic_id
						cree_post ($p + array(
							'forum_id' => $glob->forum_cle[$p['cle_forum']],
						));
				else {
					$p['topic_id'] = $glob->post_geo_import[$topic_first_geo_import]['topic_id']; // Récupère pour les references si post pas importé
					if (@$p['texte'] || @$p['image'] || @$p['lien']) // C'est un nouveau post rattaché au topic courant
						cree_post ($p + array(
							'forum_id' => $glob->forum_cle[$p['cle_forum']],
//							'topic_id' => $glob->post_geo_import[$topic_first_geo_import]['topic_id'],
						));
//					else
//if(0)////////////////////////////////DCMM
//						$template->assign_block_vars('errors', array('REPORT' => ($errors[] = 'POST VIDE: <a target="_blank" href="http://chemineur.fr/prod/chem/edit_info.php?site=chem&id='.$p['id'].'"><b>EDIT</b></a> '.var_export($p,true).'.php')));
				}

//	preg_match ('/(wric)([0-9]+)/', $data['post_edit_reason'], $refwric);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>refwric = ".var_export($refwric,true).'</pre>';
				preg_match ('/^(wri|prc|c2c)([0-9]+)$/', $p['id'], $ref);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>ref = ".var_export($ref,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>P = ".var_export($p,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>posts = ".var_export($posts,true).'</pre>';

				// Mémorisation du lien
				$url_sites = [
					'wri' => 'http://www.refuges.info/point/',
					'prc' => 'http://www.pyrenees-refuges.com/fr/affiche.php?numenr=',
					'c2c' => 'http://www.camptocamp.org/huts/',//TODO voir les summits !!
				];

				if (count ($ref)) {
					$url = $url_sites[$ref[1]].$ref[2];

					if ($ref[1] == 'c2c') {
						$sql = 'SELECT * FROM geo_reference WHERE url LIKE "%'.$ref[2].'" AND url LIKE "%camptocamp%"';
						$result = $db->sql_query($sql);
						$row = $db->sql_fetchrow($result);
						$db->sql_freeresult($result);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>row = ".var_export($row,true).'</pre>';
						if ($row)
							$url = $row['url'];
					}

					$sql = "
						INSERT INTO geo_reference (topic_id, url)
						VALUES (".$p['topic_id'].", '$url')
						ON DUPLICATE KEY UPDATE topic_id=VALUES(topic_id), url=VALUES(url)
					";
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql,true).'</pre>';
					$db->sql_query($sql);

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>POST_CREATED (".time().") = ".var_export($ref,true).'</pre>';
				}

				$glob->post_geo_import[$p['post_edit_reason']] = $p; // On note qu'on l'a fait
				$arg->limit--; // Pas de degroupage car on ne repasse pas sur le même include (pour chem)
			}
		}
	}

	if (isset ($non_trouves))
		foreach ($non_trouves AS $nt=>$v)
//		if (!in_array($nt, array('forum7', 'forum8', 'forum9')))
			$template->assign_block_vars('errors', array('REPORT' => ($errors[] = 'Forum non trouve: "'.$nt.'" '.@$v['nf'])));

	$suites = [
		'references' => 'forum',
		'forum' => 'point',
		'point' => 'diapo',
		'diapo' => 'trace',
	];

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>NON_TROUVES = ".var_export($non_trouves,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>{$arg->limit} = ".var_export($posts,true).'</pre>';
	if (isset ($errors))
		return;
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>limit = ".var_export($arg->limit,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($arg->repeat,true).'</pre>';
	if ($arg->repeat && $arg->limit <= 0 && count ($posts) && !@$a_detruire) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export('SERVER',true).'</pre>';
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="' . $arg->repeat . '; url=' . $request->server('REQUEST_URI') . '" />')
		);
	}
	elseif (isset($_GET['suite']) && isset ($suites[request_var ('source', '')])) {
/**/echo"<pre style='background-color:white;color:black;font-size:14px;'>FINI = ".var_export($_GET,true).'</pre>';
		$template->assign_vars(array(
			'META' => '<meta http-equiv="refresh" content="' . $arg->repeat . '; url=' .
			str_replace (request_var ('source', ''), $suites[request_var ('source', '')], $request->server('REQUEST_URI'))
			. '" />')
		);
	}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>posts = ".var_export($posts,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($_GET,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($suites[$_GET['source']],true).'</pre>';
}
//-------------------------------------------------------------------------
function import_chemineur($type) {
	global $db, $template, $config_locale, $arg, $glob, $errors, $users;

	if (!isset ($config_locale['exportchem3'])) {
		$template->assign_block_vars('errors', array('REPORT' => ($errors[] = '$config_locale["exportchem3"] non défini')));
		return null;
	}

	// Listage des forum_id pbpbb par le nom du forum: $glob->phpbb_forum_id[]
	$sql = "SELECT * FROM phpbb_forums";
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
		$glob->phpbb_forum_id[$row['forum_name']] = $row['forum_id'];
	$db->sql_freeresult($result);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($glob->phpbb_forum_id,true).'</pre>';

	// Ajout des traductions des imports chemineur V2
	$liste_forum_chem = liste_forum_chem();
	foreach ($liste_forum_chem AS $k => $v)
		if (isset ($glob->phpbb_forum_id[$v[0]]))
			$glob->forum_cle[$k] = $glob->phpbb_forum_id[$v[0]];
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>$k = ".var_export($glob->forum_cle,true).'</pre>';

	if (!isset ($glob->chem_topic)) // la première fois
		$glob->chem_topic = explode ('|', file_get_contents_T ('http://v2.chemineur.fr/grab.php?'.$type)); // Liste des id des topics chemineur à importer

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($glob->post_geo_import,true).'</pre>';
	unset ($posts);
	while (!isset ($posts)) { // On cherche un include avec une déclaration de $post
		if (!count ($glob->chem_topic)) // C'est fini
			return null;

		$chem_topic_id = array_shift ($glob->chem_topic); // On dépile le n° d'un topic à importer


//if ($chem_topic_id == 20160618072250) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($chem_topic_id,true).'</pre>';
//}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($glob->post_geo_import,true).'</pre>';


		if (!isset ($glob->post_geo_import["http://v2.chemineur.fr/{$arg->source}/".$chem_topic_id])) { // On en cherche 1 pas déjà fait

			if (!file_exists($nf = $config_locale['exportchem3'].$chem_topic_id.'.php')) {
					$template->assign_block_vars('errors', array('REPORT' => ($errors[] = "Fichier non trouvé: <a href='http://v2.chemineur.fr/point/$chem_topic_id'>$chem_topic_id.php</a>")));
			} else {
				unset ($forum);
				unset ($posts);
				include ($nf);
				foreach ($posts AS $k => $p) {
					$posts[$k]['liens'] = @$liens;
					$posts[$k]['fichier_php'] = $nf; // Note le fichier origine pour debug
					if (isset ($posts[$k]['point']) && strlen ($posts[$k]['point']) < 3)
						$posts[$k]['point'] = '';
					if (isset ($posts[$k]['forum']) && strlen ($posts[$k]['forum']) < 3)
						$posts[$k]['forum'] = '';
					if (isset ($posts[$k]['diapo']) && strlen ($posts[$k]['diapo']) < 3)
						$posts[$k]['diapo'] = '';
				}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($liste_forum_chem[$forum][0],true).'</pre>';
				if ($liste_forum_chem[$forum][0] == 'DETRUIRE')
					unset ($posts);
				if (isset ($posts) && !isset ($forum)) {
					unset ($posts);
					$template->assign_block_vars('errors', array('REPORT' => ($errors[] = 'Variable forum non trouvée dans: '.$chem_topic_id.'.php')));
				}
			}
		}
	}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>forum = ".var_export($forum,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>liens = ".var_export($liens,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>posts = ".var_export($posts,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>FC = ".var_export($liste_forum_chem[$forum][0],true).'</pre>';

	$auteur_c3 = ['dominique', 'dominique', 'valverco','valverco'];
	$auteur_c2 = ['webmestre', 'chemineur', 'patrice', 'cassandre',
		'claude mauguier', 'mauguier (ex-c2c)', 'artos',
		'las tinquades',
		'http://www.pyrenees-refuges.com/',
		'chantal',
		'wikipedia',
		'cai formaza',
		'mauguier/',
		'y. mauguier',
		'camille mauguier',
		'http://www.hikr.org/gallery/photo373462.html?post_id=27814',
		'http://lh5.ggpht.com',
		'host147-33-static.28-87-b.business.telecomitalia.it',
		'valleedesaurat.free.fr',
		'www.photosariege.com',
		'http://www.rando83.fr',
	];
	$auteur_c3 += array_fill (count ($auteur_c3), count ($auteur_c2) - count ($auteur_c3), 'mauguier');
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($auteur_c2,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($auteur_c3,true).'</pre>';

	// Détermination du premier auteur d'un des posts du topic
	$topic_own = '';
	$topic_synth_time = 0;
	foreach ($posts AS $p) {
		if (!$topic_own && @$p['auteur'] && @$users[$p['auteur']])
			$topic_own = $p['auteur'];
		if (!$topic_own && @$p['correcteur'] && @$users[$p['correcteur']])
			$topic_own = $p['correcteur'];
		if (!$topic_own && @$p['post_username'] && @$users[$p['post_username']])
			$topic_own = $p['post_username'];

		if (!$topic_synth_time)
			$topic_synth_time =
				 is_numeric (@$p['modif']) && $p['modif'] > 1 ? chem_date ($p['modif']) :
				(is_numeric (@$p['id'   ]) && $p['id'   ] > 1 ? chem_date ($p['id'])    :
				(is_numeric (@$p['date' ]) && $p['date' ] > 1 ? chem_date ($p['date'])  :
				 $topic_synth_time));
	}
	if (!$topic_synth_time)
		$topic_synth_time = chem_date ('20080000000000');

	// Pour 1 fichier, donc 1 topic à mettre dans le forum $arg->phpbb_forum_id[$forum]
	// Parcours de tous les posts du topic
	foreach ($posts AS $k => $p)
		if ($type != 'diapo' && @$p['type'] == 'diapo') // Pour ne pas importer les diapos comme des points points ou forum
			unset ($posts[$k]);
		elseif (substr ($p['id'], 0, 4) == 'wric') // Pour ne pas importer commentaires WRI
			unset ($posts[$k]);
		else {
			// Mise en forme de certains champs
			foreach (array ('lon', 'lat', 'altitude', 'massif') AS $t)
				if (isset ($posts[$k][$t]))
					$posts[$k]['geo_'.($t=='lon'?'lng':$t)] = $posts[$k][$t];

			if (@$p['texte'] == '-')
				$p['texte'] = '';
			if (@$p['places'])
				$p['proprietaire'] = "Proprietaire ".$p['places']."\n".$p['texte'];
			if (@$p['places'])
				$p['texte'] = "Nombre de place".($p['places'] > 1 ? "s" : "")." ".$p['places']."\n".$p['texte'];

			preg_match ('/(refuges.info|pyrenees-refuges|camptocamp.org)/', @$p['lien'], $lll);
			if (@$p['lien'] && !count($lll)) // S'il y a un lien, on l'ajoute dans le texte
				$p['texte'] .= "\n[url]{$p['lien']}[/url]";

			preg_match ('/([a-z]+)([0-9]+)/', $p['id'], $ids);
			$posts[$k]['post_time'] =
				 is_numeric (@$p['modif']) && $p['modif'] > 1 ? chem_date ($p['modif']) :
				(is_numeric (@$p['id'   ]) && $p['id'   ] > 1 ? chem_date ($p['id'])    :
				(is_numeric (@$p['date' ]) && $p['date' ] > 1 ? chem_date ($p['date'])  :
				 $topic_synth_time));
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>".date('r',$posts[$k]['post_time'])." = ".var_export($p,true).'</pre>';

			$owner = '';
			if (!$owner && @$p['auteur'] && @$users[$p['auteur']])
				$owner = $p['auteur'];
			if (!$owner && @$p['correcteur'] && @$users[$p['correcteur']])
				$owner = $p['correcteur'];
			if (!$owner && @$p['post_username'] && @$users[$p['post_username']])
				$owner = $p['post_username'];

			$posts[$k] += array (
				'cle_forum' => $forum,
				'topic_title' => @$p['titre'],// ?: 'RE: '.@$titre_precedent,
				'message' => @$p['texte'],
				'nf' => $nf,
				'post_username' => str_replace ( // => TOUT EN MINUSCULE !
					$auteur_c2,
					$auteur_c3,
					strtolower(trim($owner))
				),
			);
			if (isset ($p['image']))
				$posts[$k] = array (
					'image' => str_replace ('../../chem-fich/', 'http://v2.chemineur.fr/chem-fich/', $p['image']),
					'image_date' => chem_date (@$p['cliche']),
					'image_exif' => @$p['exif'],
				) + $posts[$k];
		}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>RETURN IMPORT_CHEMINEUR = ".var_export($posts,true).'</pre>';
	return $posts;
}
//-------------------------------------------------------------------------
function cree_post ($data) {
	global $db, $user, $template, $config, $errors;

//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>CREE_POST = ".var_export($data,true).'</pre>';
/*
	$sql = 'SELECT post_id FROM phpbb_posts WHERE post_subject LIKE "'.str_replace ('"', '\\"', $data['topic_title']).'"';
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$db->sql_freeresult($result);
	if ($row) {
		echo"<pre style='background-color:white;color:black;font-size:14px;'>POST_DEJA_IMPORTE : ".var_export($data,true).'</pre>';
		return;
	}
*/
//	$config['geo_no_attach_file_check'];
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>cree_post = ".var_export($data,true).'</pre>';

//if (@$data['post_username'] == 'errpswd')
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($data,true).'</pre>';
	if (@$data['post_username'] == 'errpswd')
		$data['post_username'] = 'Anonymous';
	if (@$data['post_username']) {
		$searched_users = array($data['post_username']);
		$ids = array();
		user_get_id_name ($ids, $searched_users);
		if (isset ($ids[0]))
			$data['poster_id'] = $ids[0];
		else {
/**/echo"<pre style='background-color:white;color:black;font-size:14px;'>AUTEUR INCONNU : [".$data['post_username']."] = ".var_export($data,true).'</pre>';
			$template->assign_block_vars('errors', array('REPORT' => ($errors[] = 'Auteur inconnu: '.$data['post_username'])));
			return;
		}
	}
	// On change le user courant pour celui du post pour la bonne gestion des attachements
	$user_courant = $user->data['user_id']; // Mémorise pour le remettre après
	$username_courant = $user->data['username'];
	if (@$data['poster_id']) {
		$user->data['user_id'] = $data['poster_id'];
		$user->data['username'] = $data['post_username'];
	} else
		$data['poster_id'] = $user_courant;
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>user_courant = ".var_export($user_courant,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>user->data['user_id'] = ".var_export($user->data['user_id'],true).'</pre>';

	$message_parser = new parse_message(trim (@$data['message']) ?: '');
	$poll = array();
	$data += array( // Valeurs par défaut
		'topic_id' => 0, // Le créé
		'post_id' => 0, // Le créé
		'topic_title' => 'Sans titre',
		'message' => $message_parser->message,
		'message_md5' => md5($message_parser->message),
		'bbcode_bitfield' => $message_parser->bbcode_bitfield,
		'bbcode_uid' => $message_parser->bbcode_uid,
//		'post_edit_reason' => $data['post_edit_reason'],

		'post_time' => time(),
		'icon_id' => 0,
		'enable_bbcode' => true,
		'enable_smilies' => true,
		'enable_urls' => true,
		'enable_sig' => true,
		'topic_visibility' => true,
		'post_visibility' => true,
		'enable_indexing' => true,
		'post_edit_locked' => false,
		'notify_set' => false,
		'notify' => false,

		'geo_trace' => null,
	);
	generate_text_for_storage($data['message'], $data['bbcode_uid'], $data['bbcode_bitfield'], $bidon, true, true, true);

	if (isset ($data['image'])) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($data['image'],true).'</pre>';
		$sql_ary = array(
			'physical_filename'	=> $data['image'],
			'attach_comment'	=> @$data['attach_comment'] ?: '',
			'real_filename'		=> $data['image'], // ??? DCMM BIZARE parse_url ($data['image'], PHP_URL_PATH),
			'extension'			=> 'jpg',
			'mimetype'			=> 'image/jpeg',
			'filesize'			=> 0,
			'filetime'			=> @$data['image_date'] ?: time(),
			'exif'			=> @$data['image_exif'],
			'thumbnail'			=> 0,
			'is_orphan'			=> 1,
			'in_message'		=> false,
			'poster_id'			=> $data['poster_id'],
		);
		$db->sql_query('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary));
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>sql_ary = ".var_export('INSERT INTO ' . ATTACHMENTS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary),true).'</pre>';
		
		$data['attachment_data'] = array(
			0 => $sql_ary + array( // On complète le tableau pour servir d'entrée à attachment_data
				'attach_id' => $db->sql_nextid(),
//				'is_orphan' => 1,
			)
		);
	}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>SUBMIT_POST = ".var_export($data,true).'</pre>';
	submit_post (
		$data['topic_id'] ? 'reply' : 'post',
		trim ($data['topic_title']),
		0,
		0,
		$poll,
		$data
	);

	if (!isset ($data['post_edit_reason']))
		$data['post_edit_reason'] = '';

	$champs = [
		"post_edit_reason = '".$data['post_edit_reason']."'",
		"post_edit_time = '".time()."'",
		"post_edit_user = 1",
		"post_edit_count = 1",
	];
	if (isset ($data['geo_altitude']))
		$champs [] = "geo_altitude = '".$data['geo_altitude']."'";
	if (isset ($data['geo_massif']))
		$champs [] = "geo_massif = '".str_replace("'", "\\'", $data['geo_massif'])."'";
	if (isset ($data['geo_lng']) && isset ($data['geo_lng']))
		$champs [] = "geom = GeomFromText('POINT(".$data['geo_lng']." ".$data['geo_lat'].")',0)";

	if (isset ($data['trace'])) {
/**/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($data['trace'],true).'</pre>';
		include_once('ext/Dominique92/GeoBB/geoPHP/geoPHP.inc');
		$fgpx = file_get_contents_T ("http://v2.chemineur.fr/prod/chem/".$data['trace']); // Rapatrie la trace d'origine
//		$fgpx = preg_replace ('/(\<wpt.*wpt\>)/i', '', $fgpx); // Enlève les points tout seuls
		$g = geoPHP::load($fgpx,'gpx');
		$w = $g->out('wkt');
		$wsp = preg_replace ('/(POINT ?\([0-9. ]+\)\,?)/i', '', $w); // Enlève les points tout seuls
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>WWW = ".var_export($w,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>SORTIE = ".var_export($wsp,true).'</pre>';
		$champs [] = "geom = GeomFromText('$wsp',0)";
	}

	$db->sql_query("UPDATE phpbb_posts SET ".implode(',',$champs)." WHERE post_id = ".$data['post_id']);

	@$template->assign_block_vars(
		'status', array(
			'REPORT' => "Import '{$data['topic_title']}' => <a href='viewtopic.php?t={$data['topic_id']}'>topic {$data['topic_id']}</a> ({$data['post_edit_reason']})"
		)
	);

	// On remet les valeurs de départ
	$user->data['user_id'] = $user_courant;
	$user->data['username'] = $username_courant;

	return $data;
}
//-------------------------------------------------------------------------
// FONCTIONS USUELLES
//-------------------------------------------------------------------------
function cree_forum ($forum_data) {
	global $forums, $db, $cache, $auth, $template;

	if (isset ($forums[$forum_data['forum_name']])) {
		$fd =  $forums[$forum_data['forum_name']];
		$fd['forum_desc'] = $forum_data['forum_desc'];
		generate_text_for_storage($fd['forum_desc'], $fd['forum_desc_uid'], $fd['forum_desc_bitfield'], $fd['forum_desc_options'], true);
		$sql = "UPDATE phpbb_forums
				SET
					forum_desc = '".($fd['forum_desc'] == '-' ? '' : $fd['forum_desc'])."',
					forum_desc_uid = '{$fd['forum_desc_uid']}',
					forum_desc_bitfield = '{$fd['forum_desc_bitfield']}',
					parent_id = ".(isset($forum_data['parent_id']) ? $forum_data['parent_id'] : 0)."
				WHERE forum_id = ".$fd['forum_id'];
		if ($forum_data['forum_desc']) { // On garde la dernière déclaration des desc
			$result = $db->sql_query($sql);
			$template->assign_block_vars('status', array('REPORT' => $sql));
		}
		return 'Update desc '.$forum_data ['forum_name'].' '.var_export(@$result,true);
	}

	if (@$forums [$forum_data ['parent_name']]) {
		$forum_data ['parent_id'] = $forums [$forum_data ['parent_name']] ['forum_id'];
		unset ($forum_data ['parent_name']); // N'existe pas dans la table phpbb_forums
	}
	else if (isset ($forum_data ['parent_name']))
		return 'Erreur forum parent non trouve'.var_export($forum_data,true);

	$forum_data = array_merge (array(
		'forum_type' => 1, // Doit être présent sinon bugge. Par défaut : forum = 1	
		'forum_parents' => '', // Doit être présent sinon bugge
		'forum_desc' => '', // Doit être présent sinon bugge
		'forum_desc_options' => 7,
		'forum_desc_uid' => '',
		'forum_desc_bitfield' => '',
		'forum_rules' => '', // Doit être présent sinon bugge
		'forum_rules_options' => 7,
		'display_subforum_list' => true,
		'display_on_index' => true,
		'forum_topics_per_page' => 0,
		'enable_indexing' => true,
//		'enable_post_review' => true,
		'show_active' => true,
		'parent_id' => 0,

		'forum_password' => '',
		'forum_password_confirm' => '',
		'prune_days' => 0,
		'prune_viewed' => 0,
		'prune_freq' => 0,
		'forum_link_track' => false,
		'prune_announce' => false,
		'prune_sticky' => false,
		'prune_old_polls' => 0,
		'enable_post_review' => true,
		'enable_quick_reply' => true,
		'forum_password_unset' => false,
	), $forum_data);
	generate_text_for_storage($forum_data['forum_desc'], $forum_data['forum_desc_uid'], $forum_data['forum_desc_bitfield'], $forum_data['forum_desc_options'], true);

	// Création du forum
	$cache->destroy('sql', FORUMS_TABLE);
	$forums_admin = new acp_forums();
	$errors_c_for = $forums_admin->update_forum_data($forum_data);

	if ($errors_c_for)
		return "Erreurs création forum\nErreurs: ".var_export($errors_c_for,true)."\nForum: ".var_export($forum_data,true);
	else
		$template->assign_block_vars('status', array('REPORT' => "update_forum_data ({$forum_data['forum_name']})"));

	copy_forum_permissions (
		$forum_data['forum_type'] + 1, // Origine des permissions : Votre première catégorie / Votre premier forum
		$forum_data['forum_id'] // Le nouveau forum créé
	);
	cache_moderators();
	$auth->acl_clear_prefetch();

	$forums [$forum_data ['forum_name']] = $forum_data; // Mémorisation pour programme en faisant plusieurs à la suite
	return "CREE FORUM: ".var_export($forum_data ['forum_name'],true);
}
//-------------------------------------------------------------------------
function validate_range(){} // Stub parce que dans un fichier qu'on ne peut pas inclure: adm/index.php
//-----------------------------------------------------------------------------
function chem_date ($c) {
	global $user;
	date_default_timezone_set ('Europe/Paris');
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>CHEM_DATE = ".var_export($c,true).'</pre>';

	$c = str_replace (array ('-', ':', ' '), '', $c);

	if (substr ($c, 0, 5) == 'artos')
		$c = '20081020000000'; // Date entrée fichier excel Claude
	else if (!is_numeric  ($c))
		return 0;

	if ($c < 1500000000)
		return $c; // C'est un unix-time

	if (substr ($c, 0, 2) < 19)
		return 0; // C'est un nombre bizare

	if (strlen ($c) >= 8) {
		$ut = mktime (
			substr ($c,  8, 2), // h
			substr ($c, 10, 2), // m
			substr ($c, 12, 2), // s
			
			max (1, substr ($c, 4, 2)), // m
			max (1, substr ($c, 6, 2)), // j
			substr ($c, 0, 4)  // a
		);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>$ut = ".var_export(time(),true).'</pre>';
		if ($ut > time ())
			return 0;
		else
			return max (0, $ut);
	}
	else
		return 0;
}
//-------------------------------------------------------------------------
function geo_init_verif () {
	global $db;
	eval (file_get_contents_T ('http://v2.chemineur.fr/grab.php?verif'));
	// On obtiens $chem2 = array ( 'k20080501115206' => 'http://chemineur.fr/diapo/20080501115206', ...
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($chem2,true).'</pre>';

	$sql = 'SELECT post_id, topic_id, forum_id, post_subject, post_edit_reason FROM phpbb_posts';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
		if ($row['post_edit_reason']) {
			$pers = explode ('/', $row['post_edit_reason']);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($pers,true).'</pre>';

			if (!isset ($chem2['k'.$pers[4]]))
				/**/echo"<pre style='background-color:white;color:black;font-size:14px;'>NON DANS CHEM2 {$pers[4]} = ".var_export($row,true).'</pre>';
			
			if (count ($pers) != 5)
				/**/echo"<pre style='background-color:white;color:black;font-size:14px;'>COUNT <> 5 = ".var_export($row,true).'</pre>';
			else
				$chem2['k'.$pers[4]] = false; // On flag ceux déjà transférés
	}

	// References
	$sql = 'SELECT * FROM geo_reference WHERE topic_id != 0';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result)) {
		$id = str_replace ('http://www.camptocamp.org/huts/', 'c2c', $row['url']);
		$id = str_replace ('http://www.pyrenees-refuges.com/fr/affiche.php?numenr=', 'prc', $id);
		$id = str_replace ('http://www.refuges.info/point/', 'wri', $id);
		unset ($chem2['k'.$id]);
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>$id = ".var_export($row,true).'</pre>';
	}
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($chem2 ,true).'</pre>';

	// On enlève les points importés non reliés
	foreach ($chem2 AS $k=>$v)
		if (!$v ||
			strpos ($v, 'LIBRE/wri') ||
			strpos ($v, 'point/wric') ||
			strpos ($v, 'LIBRE/c2c') ||
			strpos ($v, 'LIBRE/prc') ||
			strpos ($v, 'LIBRE/ldp'))
			unset ($chem2[$k]);

	echo '<style>a{font-size:16px!important;margin:10px!important}</style>';
	foreach ($chem2 AS $k=>$v) {
		$vs = explode ('/', $v);
		echo "<a target='_blank' href='http://chemineur.fr/prod/chem/edit_info.php?site=chem&id={$vs[4]}'>$v</a><br/>";
	}
}
//-------------------------------------------------------------------------
function liste_forum_chem() {
	return array(
		//   Type_chem prod_info,			 0=nom,					1=icone,				 2=catégorie	3=description   4=description_catégorie
		//   Type des sites externes
		//   En minuscules
		// Sont pris les derniers comments du forum / catégorie
			'xxxxxx'                => array('Refuge gardé',          	 'refuge.png',       	  'Refuges'), // Entrée inutile, pour éviter l'indice 0

//-------------------------------------------------------------------------------------------------------
			'refuge'                => array('Refuge gardé',         'refuge.png',            'Refuges', '[first=point][view=point][exclude=tortillards]'),
			'refuge_garde'          => array('Refuge gardé',         'refuge.png',            'Refuges'),
			'refuge-garde'          => array('Refuge gardé',         'refuge.png',            'Refuges'),
			
			'cabane'                => array('Cabane',               'cabane.png',            'Refuges', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
			'chalet'                => array('Cabane',               'cabane.png',            'Refuges'),
			'cabane-non-gardee'     => array('Cabane',               'cabane.png',            'Refuges'),
			'abri'                  => array('Cabane',               'cabane.png',            'Refuges'), // Import WRI & chemineur
			'abri sommaire'         => array('Cabane',               'cabane.png',            'Refuges'), // Pour C2C

			'gîte'                  => array('Gîte d\'étape',        'gite.png',              'Refuges', '[first=point][view=point][exclude=tortillards]'),
			'gite'                  => array('Gîte d\'étape',        'gite.png',              'Refuges'),
			'gite_etape'            => array('Gîte d\'étape',        'gite.png',              'Refuges'),
			'gite-d-etape'          => array('Gîte d\'étape',        'gite.png',              'Refuges'),
			
			'cabane_cle'            => array('Cabane avec clé',      'cabane_cle.png',        'Refuges', '[first=point][view=point][exclude=tortillards]'),
'cabanenongardéecléàrécupéreravant' => array('Cabane avec clé',      'cabane_cle.png',        'Refuges'),
'gîtedétapecléàrécupéreravant'      => array('Cabane avec clé',      'cabane_cle.png',        'Refuges'),

//-------------------------------------------------------------------------------------------------------
			'abri_sommaire'         => array('Abri',                 'abri.png',              'Abris', '[first=point][view=point][exclude=tortillards]'),
			'abri_manque_un_mur'    => array('Abri',                 'abri.png',              'Abris'),
			
			'orri'                  => array('Orri',                 'orri.png',              'Abris', '[first=point][view=point][exclude=tortillards]'),
			'abri99'                => array('Abri en pierre sèche', 'orri.png',             'Abris', '[first=point][view=point][exclude=tortillards]'),
			'buron'                 => array('Buron',                'buron.png',             'Abris', '[first=point][view=point][exclude=tortillards]'),

			'bivouac'               => array('Bivouac',              'bivouac.png',           'Abris', '[first=point][view=point][exclude=tortillards]'),
		'emplacement de bivouac'    => array('Bivouac',              'bivouac.png',           'Abris'),
			'camp de base'          => array('Bivouac',              'bivouac.png',           'Abris'),
			
			'camping'               => array('Camping',              'camping.png',           'Abris', '[first=point][view=point][exclude=tortillards]'),
			
			'grotte'                => array('Grotte',               'grotte.png',            'Abris', '[first=point][view=point][exclude=tortillards]'),
			'gouffre'               => array('Grotte',               'grotte.png',            'Abris'),
	
//-------------------------------------------------------------------------------------------------------
			'gite_ferme'            => array('Fermé ou privé',       'ferme.png',             'Inutilisable', '[first=point][view=point][exclude=tortillards]'),
			'refuge_ferme'          => array('Fermé ou privé',       'ferme.png',             'Inutilisable'),
			'refugegardéfermé'      => array('Fermé ou privé',       'ferme.png',             'Inutilisable'),
			'gîtedétapefermé'       => array('Fermé ou privé',       'ferme.png',             'Inutilisable'),
			'cabane_fermee'         => array('Fermé ou privé',       'ferme.png',             'Inutilisable'),
		'cabanenongardéefermée'     => array('Fermé ou privé',       'ferme.png',             'Inutilisable'),

			'inutilisable'          => array('Ruine',  			     'ferme.png',             'Inutilisable', '[first=point][view=point][exclude=tortillards]'),
			'ruine'                 => array('Ruine',                'ruine.png',             'Inutilisable'),
			'cabane_ruinee'         => array('Ruine',                'ruine.png',             'Inutilisable'),
		'cabanenongardéedétruite'   => array('Ruine',                'ruine.png',             'Inutilisable'),
			'refugegardédétruit'    => array('Ruine',                'ruine.png',             'Inutilisable'),

			'non renseigné'         => array('Inconnu',              'inconnu.png',           'Inutilisable', '[first=point][view=point]'),
			'inconnu'               => array('Inconnu',              'inconnu.png',           'Inutilisable'),

//-------------------------------------------------------------------------------------------------------
			'source'                => array('Point d\'eau',         'point_eau.png',         'Alimentation', '[first=point][view=point][exclude=tortillards]'),
			'point_eau'             => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'pointdeau'             => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'point_d_eau'           => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'point-d-eau'           => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'pointdeaufermee'       => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'pointdeaudétruit'      => array('Point d\'eau',         'point_eau.png',         'Alimentation'),
			'ravitaillement'        => array('Ravitaillement',       'ravitaillement.png',    'Alimentation', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
	
//-------------------------------------------------------------------------------------------------------
			'sommet'                => array('Sommet',               'sommet.png',            'Montagne', '[first=point][view=point][exclude=tortillards]'),
			'point culminant'       => array('Sommet',               'sommet.png',            'Montagne'),
			
			'col'                   => array('Col',                  'col.png',               'Montagne', '[first=point][view=point][exclude=tortillards]'),
			'pointdepassage'        => array('Col',                  'col.png',               'Montagne'),
			'point-de-passage'      => array('Col',                  'col.png',               'Montagne'),
			
			'lac'                   => array('Lac',                  'lac.png',               'Montagne', '[first=point][view=point][exclude=tortillards]'),
			'glacier'               => array('Glacier',              'glacier.png',           'Montagne', '[first=point][view=point][exclude=tortillards]'),

//-------------------------------------------------------------------------------------------------------
			'ligne'                 => array('Ligne de chemin de fer', '',                    'Voies ferrées', '[all=line][view=ligne]'),
//-------------------------------------------------------------------------------------------------------
			'gare'                  => array('Gare',                 'gare.png',              'Ferroviaire', '[first=point][view=point]'),
			'fer8'                  => array('Buffet',               'buffet.png',            'Ferroviaire', '[first=point][view=point]'),
			'fer1'                  => array('Arrêt',                'halte.png',             'Ferroviaire', '[first=point][view=point]'),
			'fer6'                  => array('Pont de chemin de fer','pont.png',              'Ferroviaire', '[first=point][view=point]'),
			'fer7'                  => array('Passage à niveau',     'passage_a_niveau.png',  'Ferroviaire', '[first=point][view=point]'),
			'fer5'                  => array('Tunnel',               'tunnel.png',            'Ferroviaire', '[first=point][view=point]'),
			'fer4'                  => array('Signal',               'signal.png',            'Ferroviaire', '[first=point][view=point]'),
			'fer2'                  => array('Dépot',                'depot.png',             'Ferroviaire', '[first=point][view=point]'),
			'fer3'                  => array('Manche à eau',         'manche_eau.png',        'Ferroviaire', '[first=point][view=point]'),

//-------------------------------------------------------------------------------------------------------
			'bus'                   => array('Bus',                  'bus.png',               'Transport', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
			'parking'               => array('Parking',              'parking.png',           'Transport', '[first=point][view=point][exclude=tortillards]'),
			'new_aeroport'          => array('Aéroport',             'aeroport.png',          'Transport', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
		
//-------------------------------------------------------------------------------------------------------
			'hotel'                 => array('Hôtel',                'hotel.png',             'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'auberge'               => array('Hôtel',                'hotel.png',             'Tourisme'),
			
			'chambre_hote'          => array('Gîte ou chambres d\'hote', 'chambre_hote.png',  'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'restaurant'            => array('Restaurant',           'restaurant.png',        'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'cafe'                  => array('Café',                 'cafe.png',              'Tourisme', '[first=point][view=point][exclude=tortillards]'),

			'ville'                 => array('Ville',                'ville.png',             'Tourisme', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
			'village'               => array('Village',              'village.png',           'Tourisme', '[first=point][view=point][exclude=tortillards]'),

			'edifice_religieux'     => array('Édifice religieux',    'edifice_religieux.png', 'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'librairie'             => array('Librairie',            'librairie.png',         'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'vignoble'              => array('Vignoble',             'vignoble.png',          'Tourisme', '[first=point][view=point][exclude=tortillards]'),

			'ouvrage_d_art'         => array('Ouvrage d\'art',       'ouvrage.png',           'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'pont'                  => array('Ouvrage d\'art',       'ouvrage.png',           'Tourisme'),
			'site_industriel'       => array('Site industriel',      'site_industriel.png',   'Tourisme', '[first=point][view=point][exclude=tortillards]'),
			'ouvrage_militaire'     => array('Ouvrage militaire',    'ouvrage_militaire.png', 'Tourisme', '[first=point][view=point][exclude=tortillards]'),

		//    'webcam'              => array('Webcam',              'webcam.png',             'Tourisme', '[first=point][view=point][exclude=tortillards]'),
		
			'site'                  => array('Site remarquable',     'site.png',              'Tourisme', '[first=point][view=point]'),
			'site_remarquable'      => array('Site remarquable',     'site.png',              'Tourisme'),
		
//-------------------------------------------------------------------------------------------------------
			'port'                  => array('Port',                 'port.png',              'Naval', '[first=point][view=point][exclude=tortillards]', '[exclude=tortillards]'),
			'phare'                 => array('Phare ou signal',      'phare.png',             'Naval', '[first=point][view=point][exclude=tortillards]'),
			'ile'                   => array('Île',                  'ile.png',               'Naval', '[first=point][view=point][exclude=tortillards]'),

//-------------------------------------------------------------------------------------------------------
			'diaporama'             => array('Diaporama',           'diaporama.png',         'Diaporamas', '[all=point][view=diapo][exclude=tortillards]', '[exclude=tortillards]'), // Forums 3 & 4
			'diapo'                 => array('Diaporama',           'diaporama.png',         'Diaporamas'),
//			'photo'                 => array('Photos',               'photo.png',             'Photographie', '=diapo[exclude=tortillards]', '[exclude=tortillards]'),

//-------------------------------------------------------------------------------------------------------
			'trace'                 => array('Trace GPS',           '',                      'Randonnée',     '[first=line][exclude=tortillards]', '[exclude=tortillards]'),

//-------------------------------------------------------------------------------------------------------
			'forum2'                => array('La vie du site Chemineur.fr',               '','Forums', '', '[exclude=tortillards]'),
//			'forum2'                => array('La vie du site Tortillards.fr',             '','Forums', '', '[exclude=chemineur]'),
			'forum3'                => array('La vie de la montagne',                     '','Forums', '[exclude=tortillards]'),
			'forum4'                => array('Propositions de randonnées',                '','Forums', '[exclude=tortillards]'),
			'forum5'                => array('Divers',                                    '','Forums'),
//			'forum95'               => array('A propos de lignes de trains',              '','Forums', '[exclude=chemineur]'), // Pour usage futur
//			'forum96'               => array('A propos d\'édifices religieux',            '','Forums', '[exclude=tortillards]'), // Pour usage futur
			'forum9'                => array('Fonctionnalités et bugs',                   '','Forums'),
			'forum99'               => array('Fonctionnalités et bugs résolus',           '','Forums'), // Pour usage futur
//			'forum98'               => array('Aide',                                      '','Forums'), // forum_id = 2 (votre premer forum)
			'forum6'                => array('DETRUIRE',                                  '','Forums'), // Chemineur version 3: bugs
		    'forum7'                => array('DETRUIRE',                                  '','Forums'), // Fonctionnalités & bugs V3.1
		    'forum8'                => array('DETRUIRE',                                  '','Forums'), // Fonctionnalités & bugs V3.1 résolus
		    'forum1'                => array('DETRUIRE',                                  '','Forums'), // Fonctionnalités & bugs V2
		    'foruma'                => array('DETRUIRE',                                  '','Forums'),
		    'NULL'                  => array('DETRUIRE',                                  '','Forums'),
		    'etiquette'             => array('DETRUIRE',                                  '','Forums'),
	);
}
