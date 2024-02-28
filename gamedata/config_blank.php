<?php

//Timezone
define('GAME_TIMEZONE', 'Asia/Shanghai');

//Mod Configuration
define('MOD_NAME', 'bra');

//Lock Configuration
define('FILE_LOCK', false);
define('FILE_LOCK_PATH', ROOT_DIR.'/brn.lock');

//Cookie Configuration
define('COOKIE_PREFIX', 'BRN_');

//Cache Configuration
define('CACHE_TYPE', 'file'); //Available: file memcache
$CACHE_CONFIG = array(
	'file' => array(
		'directory' => ROOT_DIR.'/cache'
		),
	'memcache' => array(
		'host' => '127.0.0.1',
		'port' => 11211,
		'prefix' => 'cache-'
		)
	);

//Database Configuration
define('DB_TYPE', 'pdo_mysql'); //Available: mysql mongo mongodb pdo_mysql
define('DB_HOST_M', 'localhost');
define('DB_HOST_S', false);
define('DB_PERSISTENT', false);
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');
define('DB_TABLE_PREFIX', 'brn_');

//Comet Configuration
define('COMET_SLEEP', 250000); //Microseconds
define('COMET_TIMEOUT', 25); //Seconds
define('COMET_TYPE', 'file'); //Available: file mongodb memcache
$COMET_CONFIG = array(
	'file' => array(
		'dir' => 'cache/comet'
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
		),
	'chlorocomet' => array(
		'host' => 'http://localhost:85',
		'client' => 'http://localhost:85',
		'id' => 'test',
		'pass' => 'test',
		'cache_prefix' => 'chlorocomet/'
	));

//Template Configuration
define('TEMPLATE_DIR', ROOT_DIR.'/template');
define('TEMPLATE_NAME', 'default');