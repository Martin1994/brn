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

//Show trace
foreach($error_stack as $msg){
	if($cuser['groupid'] > 1){
		$a->action('trace', array('msg' => $msg));
	}
}

//Show error message
foreach($error as $msg){
	$a->action('error', array('msg' => $msg));
}

$error_data = array();
//Store error message
foreach($error_stack as $msg) {
	$error_data[] = array('time' => time(), 'msg' => $msg);
}
$db->batch_insert('error', $error_data);

unset($GLOBALS['db']);

$a->flush();