<?php

define('IN_PHPBB', true);
$phpbb_root_path = '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$req = str_replace ('diapos', 'diapo', $request->server('SCRIPT_URL'));
$sql = "SELECT topic_id FROM phpbb_posts WHERE post_edit_reason LIKE '%$req'";
$result = $db->sql_query_limit($sql, 1);
$row = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if ($row)
	echo '<meta http-equiv="refresh" content="0; url=http://chemineur.fr/viewtopic.php?t='.$row['topic_id'].'" />';
else
	trigger_error('NO_TOPIC');
