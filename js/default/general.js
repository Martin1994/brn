var ua = window.navigator.userAgent.toLowerCase();
var is_Chrome = ua.indexOf("chrome") !== -1 ;
var is_iOS = ua["indexOf"]("iphone") > 0 || ua["indexOf"]("ipod") > 0 || ua["indexOf"]("ipad") > 0 || ua["indexOf"]("ios") > 0;

function login(username, icon, admin){
	user = username;
	$("#nav-cuser-register").fadeOut(500);
	$("#nav-cuser-login").fadeOut(500, function(){
		$("#nav-cuser-name").html("<img src='"+icon+"' class='icon'>"+username);
		$("#nav-cuser-name").fadeIn(500);
		$("#nav-cuser-logout").fadeIn(500);
	});
	
	if(admin){
		$("#nav-page-admin").fadeIn(1000);
	}
}

function logout(){
	user = false;
	
	$("#nav-cuser-name").fadeOut(500);
	$("#nav-cuser-logout").fadeOut(500, function(){
		$("#nav-cuser-register").fadeIn(500);
		$("#nav-cuser-login").fadeIn(500);
	});
	
	$("#nav-page-admin").fadeOut(1000);
}

function print_number(num, digit){
	numlength = num.toString().length
	if(numlength < digit){
		result = "";
		for(i = 0; i < (digit - numlength); i ++){
			result += "0";
		}
		result += num.toString();
		return result;
	}else{
		return num.toString();
	}
}

function debug(data){
	if(is_Chrome){
		console.debug(data);
	}
	return;
}

function daemon_time_indicator(){
	$(".time-indicator").each(function(){
		var ts = parseInt($(this).attr("time")) + 1;
		$(this).attr("time", ts);
		var h = time_number_format(Math.floor(ts / 3600));
		var m = time_number_format(Math.floor(ts / 60) - h * 60);
		var s = time_number_format(ts - h * 3600 - m * 60);
		$(this).html(h+":"+m+":"+s);
	});
}

function time_number_format(input){
	if(input < 10){
		return "0" + input.toString();
	}else{
		return input.toString();
	}
}

$(document).ready(function(){
	
	$("#index-page .left-content .toggle").click(function(e){
		if($(this).attr("function") == "hide"){
			$(this).attr("function", "show");
			$(this).html("<button>+</button>");
			$("#index-page .left-content .content").slideUp(400);
		}else{
			$(this).attr("function", "hide");
			$(this).html("<button>-</button>");
			$("#index-page .left-content .content").slideDown(400);
		}
	});
	
	if($(".time-indicator")){
		setInterval("daemon_time_indicator();", 1000);
	}
	
	$("#nav-cuser-login").click(function(){
		if(user == false){
			$("#nav-login").slideToggle("fast");
			$("#nav-login div.output").html("");
			$("#nav-login-user").focus();
		}
	});
	
	$("#nav-cuser-logout").click(function(){
		$.post("ajax.php", {action: "logout"}, function(data, status){
			if(!status){
				$("#nav-login div.output").html("网络错误");
				return;
			}else{
				if(data['success']){
					$("#nav-login div.output").empty();
					
					logout();
					
					//关闭游戏界面
					var filename = location.href;
					filename = filename.substr(filename.lastIndexOf('/')+1);
					if(filename.indexOf("game.php") == 0){
						switch_frame('');
					}
				}
			}
		}, "json");
	});
	
	//Check if logged in
	$.post("ajax.php", { action: "init" }, function(data, status){
		//data = eval('('+data+')');
		if(data['user'] != false){
			login(data['user'], data['icon'], (data['group'] > 1));
		}
	}, "json");
	

	user = false;
	
	$("#nav-login #nav-login-form").submit(function(e){
		e.preventDefault();
		$("#nav-login div.output").html("");
		user = $("#nav-login-user").val();
		pass = $("#nav-login-pass").val();
		$.post("ajax.php", { user: user, pass: pass, action: "login" }, function(data,status){
			if(!status){
				$("#nav-login div.output").html("网络错误");
				return;
			}//TODO: 用jquery的json接收代替eval
			try{
				data = eval('('+data+')');
			}catch(err){
				return debug(data);
			}
			if(data['success']){
				$("#nav-login div.output").html("登录成功");
				login(data['user'], data['icon'], (data['group'] > 1));
				$("#nav-login").slideUp("fast");
				//初始化游戏界面
				var filename = location.href;
				filename = filename.substr(filename.lastIndexOf('/')+1);
				if(filename.indexOf("game.php") == 0){
					request('init');
				}
			}else{
				$("#nav-login div.output").html("用户名或密码错误");
			}
			return;
		});
	});
	
});
