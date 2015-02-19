<?php
define('ROOT_DIR', dirname(__FILE__));

define("CONNECTION_MODE", 'ajax');

include(ROOT_DIR.'/include/inc.init.php');
include_once(ROOT_DIR.'/include/class.db.mysql.php');
include_once(ROOT_DIR.'/include/class.db.pdo_mysql.php');
include_once(ROOT_DIR.'/include/class.db.mongodb.php');

$mysql = new chlorodb_mysql(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
$pdo = new chlorodb_mysql(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
$mongodb = new chlorodb_mongodb(DB_USER, DB_PASS, DB_NAME, DB_HOST_M, DB_HOST_S);
$mysql->set_table_prefix(DB_TABLE_PREFIX);
$pdo->set_table_prefix(DB_TABLE_PREFIX);
$mongodb->set_table_prefix(DB_TABLE_PREFIX);

if(isset($_GET['flush'])){
	cache_flush();
}

if(false === $cuser){
	exit(json_encode(array(0 => array('err' => 'Invaild User.'))));
}

//批量更新测试
//效果：获取所有player表条目，并将number+1

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

?>
