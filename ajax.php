<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'ajax');
header('Content-type: text/json');

include(ROOT_DIR.'/include/inc.init.php');
$action = $_POST['action'];

$data = array();

switch($action){
	case 'init':
		if(false === $cuser){
			$data['user'] = false;
		}else{
			$data['user'] = $cuser['username'];
			$data['icon'] = $cuser['iconuri'];
			$data['group'] = $cuser['groupid'];
		}
		break;
	
	case 'login':
		if(false !== $cuser){ //User has logged in.
			$data['success'] = false;
			break;
		}
		$user = $_POST['user'];
		$pass = $_POST['pass'];
		if(isset($user) && isset($pass)){
			global $cuser;
			$cuser = login($user, $pass);
			if(false === $cuser){
				$data['success'] = false;
			}else{
				$data['success'] = true;
				$data['user'] = $cuser['username'];
				$data['icon'] = $cuser['iconuri'];
				$data['group'] = $cuser['groupid'];
			}
		}else{
			$data['success'] = false;
		}
		break;
	
	case 'logout':
		$data['success'] = logout();
		break;
	
	//¹ÜÀí²Ù×÷
	case 'admin':
		if($cuser['groupid'] <= 1){
			$data['success'] = false;
			break;
		}
		
		if(false === isset($_POST['admin_action'])){
			$data['success'] = false;
			break;
		}
		
		switch($_POST['admin_action']){
			case 'game_start':
				$g->gameinfo['starttime'] = time();
				$g->game_start();
				$data['success'] = true;
				break;
				
			case 'game_end':
				$g->game_end('error');
				$data['success'] = true;
				break;
				
			case 'game_restart':
				$g->game_end('restart');
				$g->gameinfo['starttime'] = time();
				$g->game_start();
				$data['success'] = true;
				break;
			
			case 'edit_settings':
				if(!isset($_POST['settings'])){
					$_POST['settings'] = array();
				}
				if(!is_array($_POST['settings'])){
					$data['success'] = false;
					break;
				}
				
				foreach($_POST['settings'] as &$value){
					$value = htmlspecialchars_decode($value);
				}
				
				$settings_name = $g->gameinfo['settings'];
				$db->update('gamesettings', array('settings' => $_POST['settings']), array('name' => $settings_name));
				cache_destroy('localsettings.'.$g->gameinfo['settings'].'.serialize');
				$data['success'] = true;
				break;
			
			default:
				$data['success'] = false;
				break;
		}
		break;
		
	default:
		break;
}

echo json_encode($data);