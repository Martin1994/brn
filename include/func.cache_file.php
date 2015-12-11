<?php

function cache_read($cache_name)
{
	$file_uri = $GLOBALS['CACHE_CONFIG']['file']['directory'].'/'.$cache_name;
	
	if(file_exists($file_uri)){
		return file_get_contents($file_uri);
	}else{
		return false;
	}
}

function cache_write($cache_name, $contents)
{
	$file_uri = $GLOBALS['CACHE_CONFIG']['file']['directory'].'/'.$cache_name;
	
	$dirname = dirname($file_uri);
	if(!is_dir($dirname)){
		mkdir($dirname, 0755, true);
	}
	
	if(false === file_put_contents($file_uri, $contents)){
		throw_error($file_uri.' is not writable');
	}else{
		return true;
	}
}

function cache_destroy($cache_name)
{
	$file_uri = $GLOBALS['CACHE_CONFIG']['file']['directory'].'/'.$cache_name;
	
	if(file_exists($file_uri)){
		return unlink($file_uri);
	}else{
		return true;
	}
}

function cache_flush()
{
	$dir_uri = $GLOBALS['CACHE_CONFIG']['file']['directory'];
	//TODO
}

?>