<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

$contents = cache_read('news');

if($contents === false){
	$g->update_news_cache(true);
	
	$contents = cache_read('news');
}

if($contents === false){
	throw_error('无法生成战况缓存');
}

render_page('News');

?>