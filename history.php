<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

//详细战况

if(isset($_GET['detial'])){
	
	$gamenum = intval($_GET['detial']);
	
	$data = $db->select('history', array('gamenum', 'news', 'winners'), array('gamenum' => $gamenum));
	
	if(!$data){
		throw_error('找不到第 '.$gamenum.' 局游戏的战况');
	}
	
	$contents = $g->render_news($data[0]['winners'], $data[0]['news']);
	
	render_page('History_News');
	
	exit();
}

//历史列表

if(isset($_GET['start'])){
	$start = intval($_GET['start']);
	if($start < 1){
		$start = 0;
	}
}else{
	$start = 0;
}

if(isset($_GET['show'])){
	$show = abs(intval($_GET['show']));
}else{
	$show = 20;
}

$total_items = $db->count('history');
$total_pages = ceil($total_items / $show);
$current_page = ceil(($start + 1) / $show);
$listed_max_page = min($total_pages, $current_page + 5);
$listed_min_page = max(1, $current_page - 5);

$pages = array();
for($p = $listed_min_page; $p <= $listed_max_page; $p ++){
	$pages[$p] = 'history.php?start='.(($p - 1) * $show).'&show='.$show;
}

$data = $db->select('history', array('_id', 'type', 'winner_info', 'gamenum'), array(), array($start, $show), array('time' => -1));

render_page('History');

?>