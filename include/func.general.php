<?php

function mod_entrance($mod)
{
	$GLOBALS['current_mod'] = $mod;
	return get_mod_path($mod).'/inc.modinit.php';
}

function get_sql($name, $mod = false)
{
	if($mod === false){
		$mod = $GLOBALS['current_mod'];
	}
	if(file_exists(get_mod_path($mod).'/sql/'.$name.'.sql')){
		return file_get_contents(get_mod_path($mod).'/sql/'.$name.'.sql');
	}else{
		return file_get_contents(ROOT_DIR.'/gamedata/sql/'.$name.'.sql');
	}
}

function get_mod_path($mod)
{
	return ROOT_DIR.'/gamedata/mod/'.$mod;
}

function escape_post(&$data)
{
	foreach($data as &$value){
		if(is_array($value)){
			escape_post($value);
		}else{
			$value = htmlspecialchars($value);
		}
	}
}

function get_cookie($key)
{
	if(isset($_COOKIE[COOKIE_PREFIX.$key])){
		return $_COOKIE[COOKIE_PREFIX.$key];
	}else{
		return null;
	}
}

function set_cookie($key, $value, $expire = 0)
{
	return setcookie(COOKIE_PREFIX.$key, $value, $expire);
}

function throw_error($error)
{
	switch(CONNECTION_MODE){
		case 'normal':
			$GLOBALS['err_msg'] = $error;
			render_page('Error');
			die();
			break;
	
		case 'ajax':
			global $a, $cuser;
			//$a->clear();
			$a->action('error', array('msg' => strval($error)));

			//Show trace
			if(isset($cuser) && $cuser['groupid'] > 1){
				$a->action('trace', array('msg' => debug_backtrace()));
			}
			
			unset($GLOBALS['p']);
			unset($GLOBALS['g']);
			unset($GLOBALS['db']);
			
			$a->flush();
			break;
			
		default:
			throw new Exception($error);
			break;
	}
	return;
}

function time_number_format($input)
{
	if($input < 10){
		return '0'.$input;
	}else{
		return $input;
	}
}