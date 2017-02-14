<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

$result = $db->sql_query('SELECT * FROM '.CONFIG_TABLE.' WHERE config_name IN ("server_name","cookie_domain","cookie_name")');
// ... "avatar_salt","plupload_salt","questionnaire_unique_id"
while ($row = $db->sql_fetchrow($result))
		$config_upd [] =
			'UPDATE '.CONFIG_TABLE.
			' SET config_value = "'.$row['config_value'].'"'.
			' WHERE config_name = "'.$row['config_name'].'";';
$db->sql_freeresult($result);

echo(implode('<br/>', $config_upd));

/*
UPDATE phpbb_config SET config_value = "test.chemineur.fr" WHERE config_name = "cookie_domain";
UPDATE phpbb_config SET config_value = "phpbb3_71gbo" WHERE config_name = "cookie_name";
UPDATE phpbb_config SET config_value = "test.chemineur.fr" WHERE config_name = "server_name";
*/