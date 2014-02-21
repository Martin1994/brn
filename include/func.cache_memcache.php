<?php

$GLOBALS['cache_mc'] = new Memcache;
$GLOBALS['cache_mc']->connect($GLOBALS['CACHE_CONFIG']['memcache']['host'], $GLOBALS['CACHE_CONFIG']['memcache']['port']);

function cache_read($cache_name)
{
	return $GLOBALS['cache_mc']->get($GLOBALS['CACHE_CONFIG']['memcache']['prefix'].$cache_name);
}

function cache_write($cache_name, $contents)
{
	return $GLOBALS['cache_mc']->set($GLOBALS['CACHE_CONFIG']['memcache']['prefix'].$cache_name, $contents);
}

function cache_destroy($cache_name)
{
	return $GLOBALS['cache_mc']->delete($GLOBALS['CACHE_CONFIG']['memcache']['prefix'].$cache_name);
}

function cache_flush()
{
	$GLOBALS['cache_mc']->flush();
}

?>