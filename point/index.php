<?php

// Initialisation PhpBB
define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Recherche dans les points importés de chem2
$sql = 'SELECT topic_id FROM phpbb_posts WHERE post_edit_reason LIKE \'%'.$request->server('SCRIPT_URL').'\'';
$result = $db->sql_query_limit($sql, 1);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

// recherche d'un n° de topic
preg_match ('/\d+$/', $request->server('SCRIPT_URL'), $m);

?>
<meta http-equiv="refresh" content="0; url=http://chemineur.fr/viewtopic.php?t=<?=$row['topic_id']?:$m[0]?>" />
