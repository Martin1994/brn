<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

header("Content-type: text/html; charset=utf-8");

if(!isset($_GET['pass']) || $_GET['pass'] !== '!acbr321'){
	exit('Access Denied');
}

include(ROOT_DIR.'/gamedata/config.php');
include(ROOT_DIR.'/include/func.general.php');
include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/interface.db.php');
include(ROOT_DIR.'/include/class.db.'.DB_TYPE.'.php');
include(ROOT_DIR.'/include/func.cache_'.CACHE_TYPE.'.php');

$error = array();
$page_header = 'BRN Installation';
$template_dir = './template/default';

$dbclass = 'chlorodb_'.DB_TYPE;
/* @global IChloroDB $db */
$db = new $dbclass(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
unset($dbclass);
$db->set_table_prefix(DB_TABLE_PREFIX);

$column = file_get_contents(ROOT_DIR.'/gamedata/sql/users.sql');
$db->create_table('users', $column);

$column = file_get_contents(ROOT_DIR.'/gamedata/sql/gameinfo.sql');
$db->create_table('gameinfo', $column);
$db->insert('gameinfo', array(
	'gamenum' => 0, //���ֺ��Զ����1
	'gamestate' => 0,
	'starttime' => time(),
	'winmode' => 0,
	'winner' => '',
	'arealist' => array(),
	'dangerouslist' => array(),
	'forbiddenlist' => array(),
	'areatime' => time(),
	'validnum' => 0,
	'alivenum' => 0,
	'deathnum' => 0,
	'weather' => 0,
	'round' => 0,
	'settings' => 'default'
	));

$column = file_get_contents(ROOT_DIR.'/gamedata/sql/gamesettings.sql');
$db->create_table('gamesettings', $column);
$db->insert('gamesettings', array('name' => 'default', 'settings' => array()));

$column = file_get_contents(ROOT_DIR.'/gamedata/sql/history.sql');
$db->create_table('history', $column);

$column = file_get_contents(ROOT_DIR.'/gamedata/sql/error.sql');
$db->create_table('error', $column);

cache_destroy('localsettings.default.serialize');
cache_destroy('gameinfo.serialize');

throw_error('Installed successfully.');

?>
