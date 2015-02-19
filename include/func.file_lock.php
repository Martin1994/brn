<?php

function lock()
{
	if(FILE_LOCK !== false && CONNECTION_MODE !== 'comet'){
		$GLOBALS['file_lock'] = fopen(FILE_LOCK_PATH, 'a');
		flock($GLOBALS['file_lock'], LOCK_EX);
	}
}

function unlock()
{
	if(FILE_LOCK !== false && CONNECTION_MODE !== 'comet'){
		flock($GLOBALS['file_lock'], LOCK_UN);
	}
}

?>