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
	
	$("#admin .blocks .enable-function").click(function(){
		show_function($(this).attr("target"));
		console.debug($(this));
	});
	
	$("#admin .function .back").click(function(){
		hide_function();
	});
	
	var game_settings = eval("("+$("#admin-setting-text").val()+")");
	$("#admin-setting-text").val(JSON.stringify(game_settings, undefined, 2));
	
	$("#admin-setting-submit").click(function(){
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
		$.post('ajax.php', {"action" : 'admin', "admin_action" : 'edit_settings', "settings" : new_settings}, function(data, status){
			if(data['success']){
				alert('修改成功');
			}else{
				alert('修改失败');
			}
		}, 'json');
	});
	
});