<?php
/**
 *
 * @package Reference
 * @copyright (c) 2016 Dominique Cavailhez
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace Dominique92\Reference\event;

if (!defined('IN_PHPBB'))
{
	exit;
}

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\request\request_interface $request,
		\phpbb\template\template $template,
		\phpbb\user $user,
		\phpbb\auth\auth $auth,
		\phpbb\extension\manager $extension_manager,
		$root_path
	) {
		$this->db = $db;
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->extension_manager = $extension_manager;
		$this->root_path = $root_path;
	}

	// Liste des hooks et des fonctions associées
	static public function getSubscribedEvents() {
		return [
			'core.index_modify_page_title' => 'init_select',

			'core.viewtopic_modify_post_data' => 'viewtopic_modify_post_data',
			'core.viewtopic_assign_template_vars_before' => 'viewtopic_assign_template_vars_before',

			'core.modify_posting_auth' => 'modify_posting_auth',
			'core.submit_post_modify_sql_data' => 'submit_post_modify_sql_data',

			'geo.sync_modify_sql' => 'sync_modify_sql',
			'geo.sync_modify_properties' => 'sync_modify_properties',
		];
	}

	// Sélections des fiches
	function init_select() {
		$this->template->assign_var ('IS_MODERATOR', $this->auth->acl_getf_global(['m_','a_'])); //TODO DCMM mettre dans un endroit plus centralisé

		// Popule le sélecteur de couches overlays
		$this->template->assign_block_vars('map_overlays', [
			'NAME' => 'Chemineur',
			'KEY' => 'site',
			'VALUE' => 'chemineur',
		]);

		$sql = 'SELECT DISTINCT SUBSTRING_INDEX(url, "/", 3) AS domain FROM geo_reference';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
			$this->template->assign_block_vars('map_overlays', [
				'NAME' => ucfirst ($n = str_replace (['http://', 'https://', 'www.', '.org', '.com'], '', $row['domain'])),
				'KEY' => 'site',
				'VALUE' => $n,
			]);
		$this->db->sql_freeresult($result);

		$sql = "
			SELECT DISTINCT c.forum_name, c.forum_id
			FROM ".FORUMS_TABLE." AS c
			JOIN ".FORUMS_TABLE." AS f ON (f.parent_id = c.forum_id)
			WHERE f.forum_desc REGEXP '\[[all|first]=[a-z]+\]'
		";
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($sql,true).'</pre>';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
			$this->template->assign_block_vars('map_overlays', [
				'NAME' => $row['forum_name'],
				'KEY' => 'poi',
				'VALUE' => $row['forum_id'],
			]);
		$this->db->sql_freeresult($result);
	}

	// Affichage des refs en tête de fiche
	function viewtopic_assign_template_vars_before($vars) {
		$this->init_select();

		$sql = 'SELECT * FROM geo_reference WHERE topic_id = '.$vars['topic_id'];
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
			$this->template->assign_block_vars('reference', [
				'URL' => $row['url'],
				'NAME' =>  $row['post_subject'],
				'DOMAIN' =>  str_replace('www.', '', parse_url ($row['url'],PHP_URL_HOST)),
			]);
		$this->db->sql_freeresult($result);
	}

	// Ajout des commentaires WRIC
	function viewtopic_modify_post_data($vars) {
		if (request_var('view','') == 'point') {
			$post_list = $vars['post_list'];
			$rowset = $vars['rowset'];
			$attachments = $vars['attachments'];
			$user_cache = $vars['user_cache'];
			$topic_data = $vars['topic_data'];

			$user_cache[ANONYMOUS] = array(
				'user_type'			=> USER_IGNORE,
				'username'			=> 'Anonymous',
				'user_colour'		=> '',
				'joined'			=> '',
				'posts'				=> '',
				'avatar'			=> '',
				'rank_image'		=> '',
				'rank_image_src'	=> '',
				'rank_title'		=> '',
				'sig'				=> '',
				'email'				=> '',
				'jabber'			=> '',
				'search'			=> '',
				'contact_user'		=> '',
				'age'				=> '',
				'warnings'			=> 0,
			);

			$sql = "SELECT *
				FROM geo_wric
				JOIN geo_reference USING (url)
				WHERE topic_id = ".$rowset[$post_list[0]]['topic_id'];
			$result = $this->db->sql_query($sql);
			while ($row = $this->db->sql_fetchrow($result)) {
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'> = ".var_export($topic_data,true).'</pre>';

				$post_username =
					'"<a target="_blank"'
					.' href="'.$row['url'].'#C'.$row['id'].'"'
					.' title="Commentaire issu de http://www.refuges.info"'
					.'>'
					.$row['auteur'].'@refuges.info</a>"';
				$post_list[] = $pseudo_idc = 1000000 + $row['id'];
				$rowset [$pseudo_idc] = array (
					'hide_post' => false,
					'post_id' => $pseudo_idc,
					'post_time' => $row['date'],
					'user_id' => ANONYMOUS,
					'username' => '',
					'user_colour' => 'AA0000',
					'topic_id' => $topic_data['topic_id'],
					'post_subject' => '',
					'post_edit_count' => 0,
					'post_edit_time' => 0,
					'post_edit_reason' => '',
					'post_edit_user' => ANONYMOUS,
					'post_edit_locked' => 0,
					'post_delete_time' => 0,
					'post_delete_reason' => '',
					'post_visibility' => 1,
					'post_reported' => 0,
					'post_username' => $post_username,
					'post_text' =>$row['texte'],
					'bbcode_uid' => '',
					'bbcode_bitfield' => '',
					'enable_smilies' => 1,
					'enable_sig' => 1,
					'friend' => NULL,
					'foe' => NULL,
				);

				if ($row['photo'])
					$attachments [$pseudo_idc][0]= array (
						'attach_id' => $pseudo_idc,
						'post_msg_id' => $pseudo_idc,
						'topic_id' => $row['topic_id'],
						'in_message' => 0,
						'poster_id' => ANONYMOUS,
						'is_orphan' => 0,
						'physical_filename' => 'http://www.refuges.info'.$row['photo'],
						'real_filename' => 'http://www.refuges.info'.str_replace ('-originale', '', $row['photo']),
						'download_count' => 0,
						'attach_comment' => '',
						'extension' => 'jpg',
						'mimetype' => 'image/jpeg',
						'filesize' => 0,
						'filetime' => $row['date_photo'],
						'thumbnail' => 0,
						'exif' => $post_username,
					);
			}
			$this->db->sql_freeresult($result);
			
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>post_list = ".var_export($post_list,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>rowset = ".var_export($rowset,true).'</pre>';
//*DCMM*/echo"<pre style='background-color:white;color:black;font-size:14px;'>attachments = ".var_export($attachments,true).'</pre>';

			$vars['post_list'] = $post_list;
			$vars['rowset'] = $rowset;
			$vars['attachments'] = $attachments;
			$vars['user_cache'] = $user_cache;
		}
	}

	function sync_modify_sql($vars) {
		// Insère l'extraction des données externes dans le flux géographique
		$sql_array = $vars['sql_array'];

		$sql_array ['SELECT'][] = 'url'; // 1 donnée supplèmentaire

		// Fusionne la table interne et externe
		$posts_it = 'post_subject,      post_id, topic_id, forum_id,                      post_visibility, geom, NULL AS url';
		$ref_it   = 'post_subject, 0 AS post_id, topic_id, forum_id, '.ITEM_APPROVED.' AS post_visibility, geom,         url';
		$sql_array['FROM'] = ["((SELECT $posts_it FROM ".POSTS_TABLE.") UNION (SELECT $ref_it FROM geo_reference))" => 'p'];

		$sql_array ['WHERE']['OR'][] = 't.topic_id IS NULL'; // Affiche un point externe s'il n'est pas associé à un topic ou si le topic associé n'existe plus
		$sql_array ['WHERE']['OR'][] = 't.topic_visibility != '.ITEM_APPROVED; // Ou si le topic est masqué

		$sql_array ['WHERE'][] = 'f.parent_id IN ('.request_var ('poi', '9999').')'; // Liste des types de points
		$sql_array ['WHERE'][] = '(url IS NULL OR url REGEXP "'.str_replace (',', '|', request_var ('site', 'NONE')).'")'; // Liste des sites

		$vars['sql_array'] = $sql_array;

		// Lancement en asynchrone de l'importations de données des autres sites
		$ru = explode ('/ext/', getenv('REQUEST_URI'));
		$ns = explode ('\\', __NAMESPACE__);
		$sp = explode ('/', getenv('SERVER_PROTOCOL'));
		$url = $sp[0].'://'.getenv('SERVER_NAME').$ru[0].'/ext/'.$ns[0].'/'.$ns[1].'/sync.php?bbox='.request_var ('bbox', '');

		$ch = curl_init ($url);
		curl_setopt_array ($ch, [
			CURLOPT_FRESH_CONNECT => true,
			CURLOPT_FORBID_REUSE => true,
			CURLOPT_TIMEOUT_MS => 1,
		]);
		$r = curl_exec($ch);
		file_put_contents ('../../../SYNC.log', 'GIS.PHP sync_modify_sql = '.curl_error($ch).' '.date('r').' '.$url."\n", FILE_APPEND);
		curl_close($ch);
	}

	// Insère les données externes extraites dans le flux géographique
	function sync_modify_properties($vars) {
		$properties = $vars['properties'];
		$properties ['url'] =  $vars['row']['url'];
		$vars['properties'] = $properties;
	}

	/* Appelé aprés vérifications autorisations à l'affichage de la page
	Crée une fiche: http://localhost/GeoBB/GeoBB319/posting.php?sid=...&mode=post&f=12&url=http://wri/...&nom=nnnn&lon=2&lat=45
	Lien à un topic: http://localhost/GeoBB/GeoBB319/posting.php?sid=...&mode=post&f=12&t=34&url=http://wri/...&nt=34
	Supprime un lien à un topic: http://localhost/GeoBB/GeoBB319/posting.php?sid=...&mode=post&f=12&t=34&url=http://wri/...
	*/
	function modify_posting_auth($vars) {
		global $is_authed;

		$this->init_select();

		// Création d'une fiche
		if ($url = request_var('url', '')) {
			if (!$is_authed ||
				$this->user->session_id != request_var('sid', ''))
					trigger_error('NOT_AUTHORISED');

			$nt = request_var('nt', 0);
			if ($nom = @$_GET['nom']) { // On ne passe pas par request_var car il ne récupère pas les accents
				$data = [
					'forum_id' => $vars['forum_id'],
					'post_subject' => $nom,
					'geo_ref' => $url,
					'geo_lon' => request_var('lon', 0.0),
					'geo_lat' => request_var('lat', 0.0),
					'post_id' => 0, // Le créer
					'topic_id' => 0, // Le créer
					'message' => '',
					'message_md5' => md5(''),
					'bbcode_bitfield' => 0,//$message_parser->bbcode_bitfield, // TODO DCMM
					'bbcode_uid' => 0,//$message_parser->bbcode_uid,
					'icon_id' => 0,
					'enable_bbcode' => true,
					'enable_smilies' => true,
					'poster_id' => $this->user->data['user_id'],
					'enable_urls' => true,
					'enable_sig' => true,
					'topic_visibility' => true,
					'post_visibility' => true,
					'enable_indexing' => true,
					'post_edit_locked' => false,
					'notify_set' => false,
					'notify' => false,
				];
				$poll = [];
				\submit_post(
					'post',
					urldecode ($nom),
					$this->user->data['username'],
					POST_NORMAL,
					$poll,
					$data
				);
				$nt = $data['topic_id'];
			}

			$sql = "UPDATE geo_reference SET topic_id = $nt WHERE url = '$url'";
			$this->db->sql_query($sql);

			// On arrête tout et on recharge
			header('Location: viewtopic.php?t='.($nt ?: $vars['topic_id']));
			exit;
		}
	}

	// Appelé à l'intérieur de submit_post
	function submit_post_modify_sql_data($vars) {
		if (isset ($vars['data']['geo_lon'])) {
			$sql_data = $vars['sql_data'];
			$sql_data[POSTS_TABLE]['sql']['geom'] =  'GeomFromText("POINT('.$vars['data']['geo_lon'].' '.$vars['data']['geo_lat'].')")';
			$vars['sql_data'] = $sql_data;
		}
	}
}