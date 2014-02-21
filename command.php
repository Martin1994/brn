<?php
//TODO: 管理员判断
$show_performance = true;

if(true === $show_performance){
	$start_sec = microtime(true);
}

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'ajax');
header('Content-type: text/json');

$in_game_ajax = true;

include(ROOT_DIR.'/include/inc.init.php');

if($cuser === false){
	$a->action('need_login');
	$a->flush();
}

if(($gameinfo['gamestate'] & GAME_STATE_START) === 0){
	$a->action('game_over');
	$a->flush();
}

$cplayer = $g->current_player();

if(false === isset($_POST['action'])){
	throw_error('Wrong Action');
}

$action = $_POST['action'];
$param = isset($_POST['param']) ? $_POST['param'] : array();

if($cplayer === false && $action !== 'enter_game'){
	$a->action('need_join', array(
		'gender' => $cuser['gender'],
		'icon' => $cuser['icon'],
		'avatar' => $cuser['iconuri'],
		'motto' => $cuser['motto'],
		'killmsg' => $cuser['killmsg'],
		'lastword' => $cuser['lastword']
		));
	$a->flush();
}

$command = new_command($cplayer);
$command->action_handler($action, $param);

//Check comet data
$content = $c->query($cuser['_id']);
if(false !== $content){
	$content = json_decode($content, true);
	foreach($content as $value){
		$a->action($value['action'], $value['param']);
	}
}

//Update database
unset($GLOBALS['p']);

unset($GLOBALS['g']);

unlock();

if(true === $show_performance){
	$a->action('performance', array(
		'process_sec' => microtime(true) - $start_sec,
		'db_query_times' => $db->get_query_time()
		));
}

unset($GLOBALS['db']);

foreach($error as $msg){
	$a->action('error', array('msg' => $msg));
}

$a->flush();