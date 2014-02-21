<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

if($cuser['groupid'] <= 1){
	throw_error('权限不足');
}

//获取游戏进行状况
$game_state = ($g->gameinfo['gamestate'] === 0) ? 'start' : 'end';

//获取游戏设置
$game_settings = $db->select('gamesettings', array('settings'), array('name' => $g->gameinfo['settings']));

//TODO: 更改游戏配置

render_page('Admin');

?>