<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

if(isset($_POST['username'])){
	if($_POST['password'] !== $_POST['password_confirm']){
		$info = "两次密码输入不一致";
	}else{
		$username = $_POST['username'];//preg_replace('[\W]', '', $_POST['username']);
		$password = $_POST['password'];//preg_replace('[\W]', '', $_POST['password']);
		if($username == '' || $password == ''){
			$info = '用户名与密码不能为空';
		}else{
			$users = $db->select('users', '_id', array('username' => $username));
			if(!$users){
				$db->insert('users', array(
					'username' => $username,
					'password' => encode_password($password),
					'groupid' => 0,
					'lastgame' => 0,
					'ip' => '0.0.0.0',
					'credits' => 0,
					'validgames' => 0,
					'wingames' => 0,
					'gender' => 'm',
					'icon' => 0,
					'iconuri' => 'img/question.gif',
					'club' => 0,
					'motto' => '',
					'killmsg' => '',
					'lastword' => '',
					'achievement' => array()
					));
				$info = $username.' 注册成功';
			}else{
				$info = $username.' 已经存在';
			}
		}
	}
	render_page('Register');
}else{
	render_page('Register');
}

?>