<?php

header('Content-type: text/html; charset=utf-8');

$error = array();
$error_stack = array();

function catch_error($errno, $errstr, $errfile, $errline){
	$error_stack[] = debug_backtrace();
	$GLOBALS['error'][] = '#'.$errno.': '.$errstr.' (Line'.$errline.' in '.$errfile.')';
}

set_error_handler('catch_error', E_ALL);

include(ROOT_DIR.'/gamedata/config.php');

include(ROOT_DIR.'/gamedata/settings.default.php');

include(ROOT_DIR.'/include/func.file_lock.php');
include(ROOT_DIR.'/include/func.general.php');
include(ROOT_DIR.'/include/func.user.php');
include(ROOT_DIR.'/include/func.game.php');

include(ROOT_DIR.'/include/func.cache_'.CACHE_TYPE.'.php');

include(ROOT_DIR.'/include/class.factory.php');

include(ROOT_DIR.'/include/class.command.php');

include(ROOT_DIR.'/include/class.player.php');

include(ROOT_DIR.'/include/class.item.php');

include(ROOT_DIR.'/include/class.combat.php');

date_default_timezone_set(GAME_TIMEZONE);

//Load Mod
$GAME_CLASS = 'game';
$COMMAND_CLASS = 'command';
$PLAYER_CLASS = 'player';
$COMBAT_CLASS = 'combat';
$ITEM_CLASS = 'item';
include(mod_entrance(MOD_NAME));

include(ROOT_DIR.'/include/interface.db.php');
include(ROOT_DIR.'/include/class.db.'.DB_TYPE.'.php');

include(ROOT_DIR.'/include/interface.comet.php');
include(ROOT_DIR.'/include/class.comet.'.COMET_TYPE.'.php');

include(ROOT_DIR.'/include/class.ajax.php');

escape_post($_POST);

session_start();

$dbclass = 'chlorodb_'.DB_TYPE;
$db = new $dbclass(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S, DB_PERSISTENT);
unset($dbclass);
$db->set_table_prefix(DB_TABLE_PREFIX);

$cometclass = 'chlorocomet_'.COMET_TYPE;
$c = new $cometclass($COMET_CONFIG);
unset($cometclass);

$a = new chloroajax($c);

$cuser = current_user();

lock();

if(CONNECTION_MODE === 'ajax' && isset($in_game_ajax) && $in_game_ajax){
	$c->set_self($cuser['_id'], $a);
}

$g = new_game();

//include(ROOT_DIR.'/gamedata/settings.local.php');
//Load local settings
$s_cache = cache_read('localsettings.'.$g->gameinfo['settings'].'.serialize');
if(false !== $s_cache){
	extract(unserialize($s_cache));
}else{
	$result = $db->select('gamesettings', array('settings'), array('name' => $g->gameinfo['settings']));
	if(!is_array($result)){
		throw_error('Failed to access to gamesettings.');
		exit();
	}
	extract($result[0]['settings']);
	cache_write('localsettings.'.$g->gameinfo['settings'].'.serialize', serialize($result[0]['settings']));
}
unset($s_cache);

$g->_construct();

?>