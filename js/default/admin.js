function show_function(name){
	$("#admin .functions .function").slideUp(200);
	$("#admin .functions .function."+name).slideDown(200);
	$("#admin .blocks").slideUp(200);
}

function hide_function(){
	$("#admin .functions .function").slideUp(200);
	$("#admin .blocks").slideDown(200);
}

$(document).ready(function(){
	
	$("#admin-game-restart").click(function(){
		$.post('ajax.php', {action : 'admin', admin_action : 'game_restart'}, function(data, status){
			if(data['success']){
				alert("游戏已重新开始");
			}else{
				alert("操作失败");
			}
		}, "json");
	});

	$("#admin-game-start").click(function(){
		$.post('ajax.php', {action : 'admin', admin_action : 'game_start'}, function(data, status){
			if(data['success']){
				alert("游戏已开始");
			}else{
				alert("操作失败");
			}
		}, "json");
	});

	$("#admin-game-stop").click(function(){
		$.post('ajax.php', {action : 'admin', admin_action : 'game_end'}, function(data, status){
			if(data['success']){
				alert("游戏已结束");
			}else{
				alert("操作失败");
			}
		}, "json");
	});
	
	$("#admin .blocks .enable-function").click(function(){
		show_function($(this).attr("target"));
		console.debug($(this));
	});
	
	$("#admin .function .back").click(function(){
		hide_function();
	});
	
	var game_settings = eval("("+$("#admin-setting-text").val()+")");
	$("#admin-setting-text").val(JSON.stringify(game_settings, undefined, 4));
	
	$("#admin-setting-submit").click(function(){
		var new_settings;
		try{
			new_settings = eval("("+$("#admin-setting-text").val()+")");
		}catch(err){
			alert("json输入有误");
			return;
		}
		if(typeof new_settings != "object"){
			alert(typeof new_settings);
			return;
		}
		$.post('ajax.php', {"action" : 'admin', "admin_action" : 'edit_settings', "settings" : JSON.stringify(new_settings)}, function(data, status){
			if(data['success']){
				alert('修改成功');
			}else{
				alert('修改失败');
			}
		}, 'json');
	});
	
	$("#admin-playerdata-submit").click(function(){
		var new_data;
		try{
			new_data = eval("("+$("#admin-playerdata-text").val()+")");
		}catch(err){
			alert("json输入有误");
			return;
		}
		if(typeof new_data != "object"){
			alert(typeof new_data);
			return;
		}
		$.post('ajax.php', {"action" : 'admin', "admin_action" : 'edit_playerdata', "data" : JSON.stringify(new_data)}, function(data, status){
			if(data['success']){
				alert('修改成功');
			}else{
				alert('修改失败');
			}
		}, 'json');
	});
	
	$("#admin-playerdata-querysubmit").click(function(){
		var query;
		try{
			query = eval("("+$("#admin-playerdata-query").val()+")");
		}catch(err){
			alert("json输入有误");
			return;
		}
		if(typeof query != "object"){
			alert(typeof query);
			return;
		}
		$.post('ajax.php', {"action" : 'admin', "admin_action" : 'get_playerdata', "query" : query}, function(data, status){
			if(data['success']){
				$("#admin-playerdata-text").val(JSON.stringify(data['playerdata'], undefined, 4));
			}else{
				alert('查询失败');
			}
		}, 'json');
	});
	
});