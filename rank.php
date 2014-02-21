<?php
//TODO: ·ÖÒ³
define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

if(isset($_GET['start'])){
	$start = intval($_GET['start']) - 1;
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

$data = $db->select('users', array('_id', 'username', 'iconuri', 'motto', 'credits'), array(), array($start, $show), array('credits' => -1));

render_page('Rank');

?>