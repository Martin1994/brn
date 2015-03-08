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
	
	//管理命令
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
				
				$_POST['settings'] = htmlspecialchars_decode($_POST['settings']);
				$settings = json_decode($_POST['settings'], true);
				
				if(!is_array($settings)){
					$data['success'] = false;
					break;
				}
				
				$settings_name = $g->gameinfo['settings'];
				$db->update('gamesettings', array('settings' => $settings), array('name' => $settings_name));
				cache_destroy('localsettings.'.$g->gameinfo['settings'].'.serialize');
				$data['success'] = true;
				break;
			
			case 'edit_playerdata':
				if(!isset($_POST['data'])){
					return false;
				}
				
				$_POST['data'] = htmlspecialchars_decode($_POST['data']);
				$playerdata = json_decode($_POST['data'], true);
				
				if(!is_array($playerdata)){
					$data['success'] = false;
					break;
				}
				
				$id = "";
				
				foreach($playerdata as $key => &$value){
					if($key == '_id'){
						$id = strval($value);
					}
				}
				
				if($id == ""){
					$data['success'] = false;
					break;
				}
				
				unset($playerdata['_id']);
				
				$query_success = $db->update('players', $playerdata, array('_id' => $id));
				$data['success'] = $query_success;
				break;
				
			case 'get_playerdata':
				if(!isset($_POST['query'])){
					$data['success'] = false;
					break;
				}
				if(!is_array($_POST['query'])){
					$data['success'] = false;
					break;
				}
				
				foreach($_POST['query'] as &$value){
					$value = htmlspecialchars_decode($value);
				}
				
				$players = $db->select('players', '*', $_POST['query']);
				
				if($players === false || sizeof($players) != 1){
					$data['success'] = false;
					break;
				}
				
				$data['playerdata'] = $players[0];
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