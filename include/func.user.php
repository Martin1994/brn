<?php

function login($username, $password, $encoded = false)
{
	global $db;
	
	if(false === $encoded){
		$password = encode_password($password);
	} 
	
	$condition = array('username' => $username);
	$users = $db->select('users', '*', $condition);
	
	switch(count($users)){
		case '0':
			return false;
			break;
		
		case '1':
			if($users[0]['password'] === $password){
				set_cookie('user', $username); //TODO: token + ip verify
				set_cookie('pass', $password);
				$_SESSION['cuser'] = $users[0];
				return $users[0];
			}else{
				return false;
			}
			break;
			
		default:
			throw_error('More than 1 user with username: '.$username);
			return false;
			break;
	}
	return false;
}

function logout()
{
	if(isset($GLOBALS['cuser'])){
		unset($_SESSION['cuser']);
		set_cookie('user', null); //TODO: token + ip verify
		return true;
	}else{
		return false;
	}
}

function current_user()
{
	if(isset($_SESSION['cuser'])){
		return $_SESSION['cuser'];
	}
	
	if(get_cookie('user') !== null && get_cookie('pass') !== null){
		return login(get_cookie('user'), get_cookie('pass'), true);	
	}else{
		return false;
	}
}

function encode_password($password){
	return md5($password);
}

?>