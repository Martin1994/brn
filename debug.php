<?php
define('ROOT_DIR', dirname(__FILE__));

define("CONNECTION_MODE", 'ajax');

include(ROOT_DIR.'/gamedata/config.php');
include('/include/interface.db.php');

if(isset($_GET['flush'])){
	cache_flush();
}

if(false){
	//player number += 1
	include(ROOT_DIR.'/include/class.db.'.DB_TYPE.'.php');
	
	$db_class = 'chlorodb_'.DB_TYPE;
	$db = @new $db_class(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
	$db->set_table_prefix(DB_TABLE_PREFIX);
	
	set_time_limit(0);
	$players = $db->select('players', '*', false);
	var_dump(sizeof($players));
	foreach($players as &$player){
		$player['number'] = intval($player['number']) + 1;
		unset($player['gender']);
	}
	
	$st = microtime(true);
	
	$db->batch_update('players', $players, true);
	echo 'query time: ',((microtime(true) - $st) * 1000),'ms';
}

if(false){
	//speed test
	foreach(array('mongodb', 'pdo_mysql') as $type){
		
		include_once(ROOT_DIR.'/include/class.db.'.$type.'.php');
		$db_class = 'chlorodb_'.$type;
		$db = @new $db_class(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
		$db->set_table_prefix(DB_TABLE_PREFIX);
		
		//$db->select('users', '*', array());
		
		$start_time = microtime(true);
		
		$player = $db->select('players', '*', array ( 'uid' => '566d41c239e93bfc31000f19', 'type' => 1, ));
		
		$cost = microtime(true) - $start_time;
		echo $type,'(',sizeof($player),') cost: ',$cost/1000,'ms<br>';
	}
}

if(true){
	include(ROOT_DIR.'/include/class.db.'.DB_TYPE.'.php');
	
	$db_class = 'chlorodb_'.DB_TYPE;
	$db = @new $db_class(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
	$db->set_table_prefix(DB_TABLE_PREFIX);
  $ref = array();
	$data = array (
  'gamenum' => 18,
  'type' => 'restart',
  'time' => 1450854475,
  'winners' => 
  &$ref,
  'winner_info' => 
  array (
  ),
  'news' => 
  array (
    0 => 
    array (
      '_id' => '567a2d83b60e63065800ab3d',
      'time' => 1450847619,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，开始了</span>',
    ),
    1 => 
    array (
      '_id' => '567a2d89b60e631a0c005b22',
      'time' => 1450847625,
      'content' => '<span class="join"><span class="username">Martin1994</span>加入了游戏</span>',
    ),
    2 => 
    array (
      '_id' => '567a46abb60e632dd4006641',
      'time' => 1450854059,
      'content' => '<span class="system">Martin1994 在游戏中最后幸存，游戏结束</span>',
    ),
    3 => 
    array (
      '_id' => '567a46abb60e632dd4006642',
      'time' => 1450854059,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    4 => 
    array (
      '_id' => '567a4780b60e632dd4006644',
      'time' => 1450854272,
      'content' => '<span class="system">Martin1994 在游戏中最后幸存，游戏结束</span>',
    ),
    5 => 
    array (
      '_id' => '567a4780b60e632dd4006645',
      'time' => 1450854272,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    6 => 
    array (
      '_id' => '567a47e3b60e632dd4006647',
      'time' => 1450854371,
      'content' => '<span class="system">游戏被重设</span>',
    ),
    7 => 
    array (
      '_id' => '567a47e3b60e632dd4006648',
      'time' => 1450854371,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    8 => 
    array (
      '_id' => '567a480ab60e632dd400664a',
      'time' => 1450854410,
      'content' => '<span class="system">游戏被重设</span>',
    ),
    9 => 
    array (
      '_id' => '567a480ab60e632dd400664b',
      'time' => 1450854410,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    10 => 
    array (
      '_id' => '567a4828b60e632dd400664d',
      'time' => 1450854440,
      'content' => '<span class="system">游戏被重设</span>',
    ),
    11 => 
    array (
      '_id' => '567a4828b60e632dd400664e',
      'time' => 1450854440,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    12 => 
    array (
      '_id' => '567a4840b60e632dd4006650',
      'time' => 1450854464,
      'content' => '<span class="system">游戏被重设</span>',
    ),
    13 => 
    array (
      '_id' => '567a4840b60e632dd4006651',
      'time' => 1450854464,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
    14 => 
    array (
      '_id' => '567a484ab60e632dd4006653',
      'time' => 1450854474,
      'content' => '<span class="system">游戏被重设</span>',
    ),
    15 => 
    array (
      '_id' => '567a484bb60e632dd4006654',
      'time' => 1450854475,
      'content' => '<span class="system">第<span class="gamenum">18</span>局游戏，结束了</span>',
    ),
  ),
);
	
	$db->insert('history', $data);

}

?>
