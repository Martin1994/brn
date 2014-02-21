<?php

//Timezone
define('GAME_TIMEZONE', 'Asia/Shanghai');

//Mod Configuration
define('MOD_NAME', 'thbr');

//Lock Configuration
define('FILE_LOCK', true);

//Cookie Configuration
define('COOKIE_PREFIX', 'ACBR_');

//Cache Configuration
define('CACHE_TYPE', 'memcache'); //Available: file memcache
$CACHE_CONFIG = array(
	'file' => array(
		'directory' => '/tmp/brn'
		//'directory' => ROOT_DIR.'/cache'
		),
	'memcache' => array(
		'host' => '127.0.0.1',
		'port' => 11211,
		'prefix' => 'cache-'
		)
	);

//Database Configuration
define('DB_TYPE', 'mysql'); //Available: mysql mongodb pdo_mysql
define('DB_HOST_M', '127.0.0.1');
define('DB_HOST_S', false);
define('DB_PERSISTENT', true);
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'brn');
define('DB_TABLE_PREFIX', 'brn_');

//Comet Configuration
define('COMET_SLEEP', 100000); //Microseconds
define('COMET_TIMEOUT', 25); //Seconds
define('COMET_TYPE', 'memcache'); //Available: file mongodb memcache
$COMET_CONFIG = array(
	'file' => array(
		'dir' =>  '/tmp/brn/comet'//ROOT_DIR.'/cache/comet'
		),
	'mongodb' => array(
		'host' => DB_HOST_M,
		'user' => DB_USER,
		'pass' => DB_PASS,
		'db' => DB_NAME,
		'collection' => DB_TABLE_PREFIX.'comet'
		),
	'memcache' => array(
		'host' => '127.0.0.1',
		'port' => 11211,
		'prefix' => 'comet-'
		),
	'channel_SAE' => array(
		'expired_time' => 21600,
		'cache_prefix' => 'channel/'
		)
	);

//Template Configuration
define('TEMPLATE_DIR', ROOT_DIR.'/template');
define('TEMPLATE_NAME', 'default');
?>
