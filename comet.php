<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'comet');

include(ROOT_DIR.'/include/inc.init.php');

include(ROOT_DIR.'/include/func.comet.php');

include(ROOT_DIR.'/include/inc.release.php');
unset($a);

session_write_close();

if(false === $cuser){
	exit(json_encode(array(0 => array('err' => 'Invaild User.'))));
}

if(COMET_TYPE === 'channel_SAE'){
	$url = $c->get_url($cuser['_id']);
	?>
	<html>
		<head>
			<title>BR Comet Client</title>
			<script type="text/javascript" src="js/comet.js"></script>
			<script type="text/javascript" src="http://channel.sinaapp.com/api.js"></script>
			<script type="text/javascript">
				var channel = sae.Channel("<?php echo $url; ?>");
				channel.onmessage = function(message){
					eval("m([" + message["data"] + "])");
				}
			</script>
		</head>
	</html>
	<?php
}else{

	$method = isset($_GET['method']) ? $_GET['method'] : 'streaming';
	$hash = $cuser['_id'];
	$endtime = time() + COMET_TIMEOUT;
	
	set_time_limit(COMET_TIMEOUT + 5);
	
	ob_end_flush();
	//echo str_repeat(' ', 2048);
	echo '<html><head><title>BR Comet Client</title><script type="text/javascript" src="js/comet.js"></script><meta http-equiv="refresh" content="'.(COMET_TIMEOUT + 5).'"> </head><body>';
	echo comet_hello();
	
	do{
		$content = $c->query($hash);
		if(false !== $content){
			echo comet_push($content);
			flush();
			if($method === 'long_polling'){
				break;
			}
		}
		usleep(COMET_SLEEP);
	}while(time() < $endtime);
	echo comet_reconnect();
	echo '</body></html>';
}

?>
