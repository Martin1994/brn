var UIconfig;
var UIvar;
var request_msec = 0;

function daemon(){
	//防止服务器错误导致客户端卡死
	if(ajax_plock == true){
		ajax_plock = false;
	}else if(ajax_lock == true){
		ajax_lock = false;
		//TODO: 警告
	}
	
	update_health();
	update_buff_time();
	update_notice_time();
}

function update_health(){
	if(UIvar['alive'] == false){
		return;
	}
	
	//补血
	UIconfig['hp'] += UIconfig['hpps'];
	UIconfig['sp'] += UIconfig['spps'];
	if(UIconfig['hp'] >= UIconfig['mhp']){
		UIconfig['hp'] = UIconfig['mhp'];
	}
	if(UIconfig['hp'] < 0){
		UIconfig['hp'] = 0;
		//HP降到0时向服务器更新玩家信息
		request('update');
	}
	if(UIconfig['sp'] >= UIconfig['msp']){
		UIconfig['sp'] = UIconfig['msp'];
	}
	if(UIconfig['sp'] < 0){
		UIconfig['sp'] = 0
	}
	
	//更新血条
	$("#F-console-hp .indicator").width((100 * UIconfig['hp'] / UIconfig['mhp'])+"%");
	$("#F-console-sp .indicator").width((100 * UIconfig['sp'] / UIconfig['msp'])+"%");
	$("#F-console-hp-label").html("生命 "+parseInt(UIconfig['hp'])+" / "+parseInt(UIconfig['mhp']));
	$("#F-console-sp-label").html("体力 "+parseInt(UIconfig['sp'])+" / "+parseInt(UIconfig['msp']));
	
}

function insert_chat_msg(time, msg){
	d = new Date(time * 1000);
	time = print_number(d.getHours(), 2) + ":" + print_number(d.getMinutes(), 2) + ":" + print_number(d.getSeconds(), 2);
	content = 
		"<div class='piece' id='F-console-chat-piece" + UIvar['chat_num'] + "'>" + 
		"<span class='time'>" + time + "</span>" + 
		msg +
		"</div>";
	$("#F-console-chat-display").append(content);
	$("#F-console-chat-piece"+(UIvar['chat_num'])).fadeIn();
	
	//清除多余的聊天内容
	if(UIvar['chat_num'] > 9){
		$("#F-console-chat-piece"+(UIvar['chat_num']-10)).fadeOut(400, function(){
			$(this).remove();
		});
	}
	
	UIvar['chat_num'] ++;
}

function switch_frame(new_frame){
	if(current_frame != ''){
		$('#frame-'+current_frame).fadeOut(250);
	}
	if(new_frame != ''){
		$('#frame-'+new_frame).fadeIn(250);
	}
	current_frame = new_frame;
}

function show_brief(block, time){
	if(time == undefined){
		time = 200;
	}
	$("#F-console-brief .frame").fadeOut(time);
	$("#F-console-brief .frame."+block).fadeIn(time);
	$("#F-console-brief").fadeIn(time * 2);
}

function hide_brief(){
	$("#F-console-brief").fadeOut(400, function(){
		$("#F-console-brief .frame").hide();
	});
}

function panel_block(name){
	if(name != UIvar['panel']){
		$("#F-console-panel .block[acceptor='"+UIvar['panel']+"']").slideUp(200);
		$("#F-console-panel .block[acceptor='"+name+"']").slideDown(200);
		$("#F-console-panel .title[target='"+UIvar['panel']+"']").removeClass("selected");
		$("#F-console-panel .title[target='"+name+"']").addClass("selected");
		UIvar['panel'] = name;
	}
}

function panel_selector(element){
	element = $(element);
	block = element.attr("block");
	action = element.attr("action");
	
	if(block == 'equipment' && action == 'switch'){
		request('switch');
	}else{
		$("#F-console-panel .selector[block='"+block+"'][action='"+UIvar["panel_action"][block]+"']").removeClass("selected");
		$("#F-console-panel .selector[block='"+block+"'][action='"+action+"']").addClass("selected");
		UIvar["panel_action"][block] = action;
	}
	
	if(block == 'package'){
		switch(action){
			case 'merge':
			case 'compose':
				$("#F-console-panel .item_panel .submit").slideDown(200);
				break;
			
			default:
				$("#F-console-panel .item_panel .submit").slideUp(200);
				$("#F-console-panel .item").removeClass("selected");
				break;
		}
	}
}

function panel_selector_default(block){
	panel_selector($("#F-console-panel .selector[block='"+block+"'][default]")[0])
}

function battle_action(element){
	if(typeof(element) == "string"){
		action = element;
	}else{
		action = element.attr("action");
	}
	
	switch(action){
		case 'attack':
			request('attack');
			break;
		
		case 'escape':
			request('escape');
			$("#F-console-center .wrapper[content='battle']").fadeOut(200);
			break;
		
		case 'back':
			$("#F-console-center .wrapper[content='battle']").fadeOut(200);
			break;
		
		case 'strip':
			//禁用丢弃按钮
			var disable_btn = true;
			$("F-console-package item").each(function(e){
				if($(this).find("null").length > 0){
					disable_btn = false;
				}
			});
			if(disable_btn && element.attr("target") != 'money'){
				UIvar['disable_drop'] = true;
			}
			request('strip', {iid: element.attr("target")});
			break;
		
		case 'give':
			request('give', {iid: element.attr("target")});
			break;
		
		case 'item':
			request('use', {iid: element.attr("target")});
			break;
		
		case 'leave':
			request('leave');
			break;
		
		default:
			break;
	}
}

function parse_goods(goods){
	result = "";
	for(var i in goods){
		item = goods[i];
		
		result += '<div class="item" iid="'+item['_id']+'">';
		result += '<div class="controller"><button action="add">△</button><input type="text" value="0" max="'+item['max']+'"price="'+item['price']+'" /><button action="cut">▽</button></div>';
		result += '<div class="detial">';
		result += '<div class="n">'+item['n']+'</div>';
		result += '<div class="k">'+item['k']+'</div>';
		result += '<div class="e">效果：'+item['e']+'</div>';
		result += '<div class="s">耐久：'+item['s']+'</div>';
		result += '<div class="price">价格：'+item['price']+UIconfig['currency']+'</div>';
		result += '<div class="stock">存货：'+item['num']+'</div>';
		result += '</div></div>';
	}
	result += '<div class="clear"></div>';
	return result;
}

function update_price(){
	total = 0;
	
	$("#F-console-shop .goods .controller input").each(function(){
		total += parseInt($(this).val()) * parseInt($(this).attr("price"));
	});
	
	if(total > parseInt($("#F-console-money").html())){
		total = '<span class="unaffordable">'+total+"</span>";
	}else{
		total = '<span class="affordable">'+total+"</span>";
	}
	$("#F-console-shop .subtotal").html("小计："+total+UIconfig['currency']);
}

function update_item(param){
	if(param["equipment"] != undefined){
		//需要更新装备物品
		parse_item($("#F-console-wep"), param["equipment"]["wep"], '武器');
		parse_item($("#F-console-arb"), param["equipment"]["arb"], '身体防具');
		parse_item($("#F-console-arh"), param["equipment"]["arh"], '头部防具');
		parse_item($("#F-console-ara"), param["equipment"]["ara"], '手部防具');
		parse_item($("#F-console-arf"), param["equipment"]["arf"], '足部防具');
		parse_item($("#F-console-art"), param["equipment"]["art"], '饰品');
	}
	
	if(param["capacity"] != undefined && param["capacity"] != UIconfig["capacity"]){
		//需要更新背包容量
		result = "";
		for(i = 1; i <= param["capacity"]; i ++){
			result += '<div class="item" iid="' + i + '"></div>';
		}
		result += '<div class="item" iid="0"></div>'; //拾取物置于最下方
		$("#F-console-package .list").html(result);
		UIconfig["capacity"] = param["capacity"];
	}
	
	if(param["package"] != undefined){
		//需要更新背包物品
		for(i = 0; i <= UIconfig["capacity"]; i ++){
			parse_item($("#F-console-package .item[iid='"+i+"']"), param["package"][i], '物品');
		}
		if(param["package"][0] != undefined){
			//存在拾取物品，更改空栏位文字
			$("#F-console-package .item[iid='0']").slideDown(200);
			$("#F-console-center .wrapper[content='collecting']").fadeIn(200);
			
			//更新中央显示框文字
			$("#F-console-collecting .item .n").html(param["package"][0]['n']);
			$("#F-console-collecting .item .k").html(param["package"][0]['k']);
			$("#F-console-collecting .item .e").html("效果："+param["package"][0]['e']);
			$("#F-console-collecting .item .s").html("耐久："+param["package"][0]['s']);
			if(UIvar["disable_drop"]){
				UIvar["disable_drop"] = false;
				$('#F-console-collecting button[action="drop"]').attr("disabled", "disabled");
			}else{
				$('#F-console-collecting button[action="drop"]').prop("disabled", false);
			}
			
			//判断是否可以合并
			mergable = 0;
			for(var i in param["package"]){
				if(
					i > 0 &&
					param["package"][i]['n'] == param["package"][0]['n'] &&
					param["package"][i]['k'] == param["package"][0]['k'] &&
					param["package"][i]['e'] == param["package"][0]['e']
				){
					mergable = i;
					break;
				}
			}
			if(mergable == 0){
				//panel_selector($("#F-console-package .selector[action='drop']")[0]); //将默认动作更改为丢弃
				$("#F-console-collecting button[action='merge']").attr("disabled", "disabled"); //禁止在中央执行合并操作
			}else{
				panel_selector($("#F-console-package .selector[action='merge']")[0]); //将默认动作更改为合并
				$("#F-console-package .item[iid='"+mergable+"']").addClass("selected");
				$("#F-console-package .item[iid='0']").addClass("selected");
				$("#F-console-collecting button[action='merge']").removeAttr("disabled"); //允许在中央执行合并操作
			}
			
			panel_block('package');
			$("#F-console-package .item .null").html("点击拾取");
			$("#F-console-package .item .null").click(collect_item);
		}else{
			//没有要拾取的物品
			$("#F-console-center .wrapper[content='collecting']").fadeOut(200);
			$("#F-console-panel .item .null").unbind("click");
			$("#F-console-package .item[iid='0']").slideUp(200);
		}
		UIvar["disable_drop"] = false; //拾取尸体装备时若有栏位空闲则不会触发中央拾取框，不执行此条指令则会导致下次拾取的时候丢弃按钮为灰色
	}

	$("#F-console-panel .item").unbind("click");
	$("#F-console-panel .item").click(function(e){
		item = $(this);
		select_item(item);
	});
	
}

function parse_item(div, item, type){
	if(item != undefined && item['n'] != ''){
		div.html('<div class="n">'+
			item['n']+
			'</div><div class="k">'+
			item['k']+
			'</div><div class="detial"><div>效果：'+
			item['e']+
			'</div><div>耐久：'+
			item['s']+
			'</div></div>');
	}else{
		div.html('<div class="null">无'+type+'</div>');
	}
	return;
}

function submit_item(e){
	action = UIvar['panel_action']['package'];
	items = [];
	$("#F-console-package .item.selected").each(function(e){
		items.push($(this).attr("iid"));
	});
	request(action, {iid : items});
	panel_selector_default("package");
}

function collect_item(e){
	request('collect');
	panel_selector_default("equipment");
	panel_selector_default("package");
}

function drop_collecting_item(e){
	panel_selector($("#F-console-package .selector[action='drop']")[0]);
	select_item($("#F-console-package .item[iid='0']"));
}

function select_item(item){
	iid = item.attr("iid");
	if(!$("#F-console-package .item[iid='"+iid+"'] .null").length){
		block = item.parent().attr("block");
		action = UIvar['panel_action'][block];
		switch(action){
			case 'merge':
			case 'compose':
				item.toggleClass("selected");
				break;
			
			default:
				request(action, { iid : iid});
				panel_selector_default("equipment");
				panel_selector_default("package");
				break;
		}
	}
}

function show_item_param(param){
	$("#F-console-center .wrapper[content='item_param']").fadeIn(200);
	
	result = "<form iid='"+param['id']+"'>";
	
	if(param['intro'] != undefined){
		result += '<div class="intro">'+param['intro']+'</div>';
	}
	
	var input;
	for(var i in param['input']){
		input = param['input'][i];
		result += '<div class="key">'+input['key']+'</div>';
		switch(input['type']){
			case 'text':
				result += '<div class="value">';
				result += '<input type="text" name="'+input['name']+'" />';
				result += '</div>';
				break;
			
			case 'radio':
				for(var j in input['value']){
					result += '<div class="value">';
					result += '<input type="radio" name="'+input['name']+'" value="'+input['value'][j]+'" />'+input['content'][j];
					result += '</div>';
				}
				break;
			
			default:
				
				break;
		}
		result += '<div class="clear"></div>';
	}
	
	result += '<div class="submit"><input type="submit" /><button class="back">返回</button></div>';
	result += '</form>';
	
	$("#F-console-item_param").html(result);
	
	$("#F-console-item_param form").submit(function(e){
		e.preventDefault();
		
		var result = {};
		
		$(this).find("input[type='text']").each(function(){
			result[$(this).attr("name")] = $(this).val();
		});
		
		$(this).find("input:radio:checked").each(function(){
			result[$(this).attr("name")] = $(this).val();
		});
		
		request('use', {iid: $(this).attr('iid'), param: result});
		//console.debug(result);
		
		$("#F-console-center .wrapper[content='item_param']").fadeOut(200);
	});
	
	$("#F-console-item_param .submit .back").click(function(e){
		e.preventDefault();
		$("#F-console-item_param form").unbind("submit");
		$("#F-console-center .wrapper[content='item_param']").fadeOut(200);
	});
}

function parse_battle_action(enemy){
	var action = enemy['action'];
	var name = "";
	var result = "";
	for(var i in action){
		switch(action[i]){
			case 'attack':
				name = "攻击";
				break;
			
			case 'escape':
				name = "逃跑";
				break;
			
			case 'strip':
				name = '';
				result += '<div class="text">你想从'+enemy['name']+'身上拿走什么？</div>';
				for(var j in enemy['item']){
					item = enemy['item'][j];
					switch(j){
						case 'money':
							result += '<button action="strip" target="money">'+item+UIconfig['currency']+'</button>';
							break;
						
						default:
							result += '<button action="strip" target="'+j+'">'+item['n']+' / '+item['k']+'<br>效：'+item['e']+' / 耐：'+item['s']+'</button>';
							break;
					}
				}
				break;
			
			case 'give':
				name = '';
				result += '<div class="text">你想给'+enemy['name']+'什么？</div>';
				for(var j in enemy['item']){
					item = enemy['item'][j];
					switch(j){
						case 'money':
							result += '<button action="give" target="money">'+item+UIconfig['currency']+'</button>';
							break;
						
						default:
							result += '<button action="give" target="'+j+'">'+item['n']+' / '+item['k']+'<br>效：'+item['e']+' / 耐：'+item['s']+'</button>';
							break;
					}
				}
				break;
			
			case 'leave':
				name = '离开';
				break;
			
			default:
				if(action[i].substr(0, 4) == "item"){
					result += '<button action="item" target="'+action[i].substr(4)+'">'+$("#F-console-package .list[block='package'] .item[iid='"+action[i].substr(4)+"'] .n").html()+'</button>';
				}
				name = "";
				break;
		}
		if(name != ''){
			result += '<button action="'+action[i]+'">'+name+'</button>';
		}
	}
	return result;
}

function show_wound_dressing(){
	$("#F-console-center .wrapper[content='wound_dressing']").fadeIn(400);
	$("#F-console-wound_dressing .buttons").empty();
	if(UIvar['wound_dressing'].length > 0){
		$("#F-console-wound_dressing .title").html("请选择要包扎的部位");
		var position = '';
		for(var wid in UIvar['wound_dressing']){
			switch(UIvar['wound_dressing'][wid]){
				case 'b':
					position = '胸部';
					break;
				
				case 'h':
					position = '头部';
					break;
				
				case 'a':
					position = '腕部';
					break;
				
				case 'f':
					position = '足部';
					break;
				
				default:
					position = '？？';
					break;
			}
			$("#F-console-wound_dressing .buttons").append('<button action="wound_dressing" target="'+UIvar['wound_dressing'][wid]+'">'+position+'</button>');
		}
		$("#F-console-wound_dressing .buttons button[action='wound_dressing']").click(function(){
			request('wound_dressing', {position : $(this).attr("target")});
			$("#F-console-center .wrapper[content='wound_dressing']").fadeOut(400);
		});
	}else{
		$("#F-console-wound_dressing .title").html("没有需要包扎的部位");
	}
	$("#F-console-wound_dressing .buttons").append('<button action="back">返回</button>');
	$("#F-console-wound_dressing .buttons button[action='back']").click(function(){
		$("#F-console-center .wrapper[content='wound_dressing']").fadeOut(400);
	});
}

function update_buff(buff){
	var result = '';
	var icon = '';
	var help = '';
	
	var poisoned = false;
	var injured = [];
	
	for(var i in buff){
		if(UIvar['buff_name'].hasOwnProperty(buff[i]['type'])){
			icon = UIvar['buff_name'][buff[i]['type']];
			if(UIvar['buff_help'].hasOwnProperty(buff[i]['type'])){
				help = UIvar['buff_help'][buff[i]['type']];
			}
		}else{
			icon = '神秘力量';
			help = '不明觉历的buff';
		}
		switch(buff[i]['type']){
			case 'poison':
				poisoned = true;
				break;
			
			case 'injured_body':
				injured.push('b');
				break;
			
			case 'injured_head':
				injured.push('h');
				break;
			
			case 'injured_arm':
				injured.push('a');
				break;
			
			case 'injured_foot':
				injured.push('f');
				break;
			
			default:
				break;
		}
		
		var time
		if(buff[i]['sec'] == -1){
			time = "";
		}else{
			time = buff[i]['sec'] + '"';
		}
		
		result +=
			'<div class="buff" time="'+buff[i]['sec']+'" type="'+buff[i]['type']+'">'+
				'<table class="icon"><tr><td>'+icon+'</td></tr></table>'+
				'<div class="time">'+time+'</div>'+
				(help == '' ? '' : '<div class="help">'+help+'</div>')+
			'</div>';
	}
	
	$("#F-console-buffinfo").html(result);
	
	//更改中毒时的血条颜色
	if(poisoned == true){
		$("#F-console-hp .indicator").addClass("poisoned");
	}else{
		$("#F-console-hp .indicator").removeClass("poisoned");
	}
	
	//显示或隐藏包扎选项
	if(injured.length > 0){
		$("#F-console-panel-wound_dressing").slideDown(200);
	}else{
		$("#F-console-panel-wound_dressing").slideUp(200);
	}
	UIvar['wound_dressing'] = injured;
}

function update_buff_time(){
	$("#F-console-buffinfo .buff").each(function(){
		var time = parseInt($(this).attr("time"));
		if(time != -1){
			time --;
			if(time <= 0){
				$(this).fadeOut(400, function(){
					$(this).remove();
					//时间降到0时向服务器更新玩家信息
					request('update');
				});
			}else{
				$(this).find(".time").html(time + '"');
				$(this).attr("time", time);
			}
		}
	});
}

function update_notice_time(){
	$("#F-console-notice .notice").each(function(){
		sec = parseInt($(this).attr("sec")) - 1;
		if(sec <= 0){
			$(this).slideUp(200, function(){
				$(this).remove();
			});
		}else{
			$(this).attr("sec", sec);
		}
	});
}

function respond(data){
	
	var respond_msec = (new Date()).valueOf();
	var show_performance = false;
	var feedback = [];
	
	for(var aid in data){
		
		action = data[aid]["action"];
		param = data[aid]["param"];
		
		switch(action){
			
			case 'battle':
				$("#F-console-battle .name").html(param["enemy"]["name"]);
				$("#F-console-battle .gender").html(param["enemy"]["gender"]);
				$("#F-console-battle .number").html(param["enemy"]["number"]);
				$("#F-console-battle .avatar").attr("src", param["enemy"]["avatar"]);
				$("#F-console-battle .status").html(param["enemy"]["status"]);
				$("#F-console-center .wrapper[content='battle']").fadeIn(200);
				$("#F-console-center .wrapper[content='battle']").unbind("click");
				
				if(param["end"]){
					$("#F-console-battle .action").fadeOut(200);
					$("#F-console-battle .back").fadeIn(200);
					$("#F-console-center .wrapper[content='battle']").click(function(){
						battle_action("back");
					});
				}else{
					$("#F-console-battle .action").fadeIn(200);
					$("#F-console-battle .back").fadeOut(200);
				}
				
				if(param["enemy"]["action"].length == 0){
					$("#F-console-battle .action").empty();
				}else{
					$("#F-console-battle .action").html(parse_battle_action(param["enemy"]));
				}
				
				$("#F-console-battle button").unbind("click");
				$("#F-console-battle button").click(function(e){
					battle_action($(this));
				});
				
				break;
			
			case 'radar':
				$("#F-console-map-table .map_block").not("td[mid=-1]").each(function(){
					$(this).attr("name", $(this).html());
					$(this).empty();
				});
				$("#F-console-map-table .map_block").addClass("radar");
				
				var map_blocks;
				for(var mid in param['result']){
					map_blocks = $("#F-console-map-table .map_block[mid="+mid+"]");
					map_blocks.eq(Math.floor(Math.random() * map_blocks.length)).html(param['result'][mid]);
					console.debug(mid);
				}
				
				$("#F-console-map-table .mask").show();
				break;
			
			case 'item_param':
				show_item_param(param);
				break;
			
			case 'item':
				update_item(param);
				break;
			
			case 'buff':
				update_buff(param['buff']);
				break;
			
			case 'pose':
				$("#F-console-panel .block[acceptor='pose'] .tactic").removeClass("selected");
				$("#F-console-panel .block[acceptor='pose'] .tactic[tid="+param['tid']+"]").addClass("selected");
				break;
			
			case 'tactic':
				$("#F-console-panel .block[acceptor='tactic'] .tactic").removeClass("selected");
				$("#F-console-panel .block[acceptor='tactic'] .tactic[tid="+param['tid']+"]").addClass("selected");
				break;
			
			case 'club':
				$("#F-console-club").html(param['name']);
				break;
				
			case 'team':
				$("#F-console-team").html(param['name']);
				$("#F-console-teaminfo .in_team .name").html(param['name']);
				if(param['joined']){
					$("#F-console-teaminfo .no_team").fadeOut(200, function(e){
						$("#F-console-teaminfo .in_team").fadeIn(200);
					});
				}else{
					$("#F-console-teaminfo .in_team").fadeOut(200, function(e){
						$("#F-console-teaminfo .no_team").fadeIn(200);
					});
				}
				break;
				
			case 'battle_data':
				$("#F-console-att").html(parseInt(parseFloat(param['att']) * 10) / 10);
				$("#F-console-def").html(parseInt(parseFloat(param['def']) * 10) / 10);
				break;
				
			case 'money':
				$("#F-console-money").html(param['money']);
				break;
				
			case 'name':
				$("#F-console-name").html(param['name']);
				break;
			
			case 'avatar':
				$("#F-console-avatar").attr("src", param['src']);
				$("#nav-cuser-name img").attr("src", param['src']);
				break;
				
			case 'number':
				$("#F-console-number").html(param['number']);
				break;
				
			case 'gender':
				$("#F-console-gender").html(param['gender']);
				break;
				
			case 'weather':
				$("#F-console-weather").html(param['name']);
				break;
				
			case 'location':
				$("#F-console-area").html(param['name']);
				if(param['shop']){
					$("#F-console-panel-shop").slideDown(200);
				}else{
					$("#F-console-panel-shop").slideUp(200);
				}
				break;
			
			case 'rage':
				$("#F-console-rage").html(param['rage']);
				break;
			
			case 'exp':
				if(param['target'] != undefined){
					UIconfig['texp'] = parseInt(param['target']);
				}
				if(param['current'] != undefined){
					UIconfig['cexp'] = parseInt(param['current']);
				}
				
				if(param['target'] == 0){
					var result = "100%";
				}else if(param['current'] == 0){
					var result = "0%";
				}else{
					var result = 100 * UIconfig['cexp'] / UIconfig['texp'];
					if(result > 100){
						result = "100%";
					}else{
						result = result + "%";
					}
				}
				
				$("#F-console-exp .indicator").width(result);
				$("#F-console-exp .label").html("经验 "+UIconfig['cexp']+" / "+UIconfig['texp']);
				
				if(param['level'] != undefined){
					$("#F-console-level").html("等级 "+param['level']);
				}
				
				break;
			
			case 'max_health':
				UIconfig['mhp'] = parseInt(param['mhp']);
				UIconfig['msp'] = parseInt(param['msp']);
				break;
			
			case 'health':
				if(param['hp'] != undefined){
					UIconfig['hp'] = parseInt(param['hp']);
				}
				if(param['sp'] != undefined){
					UIconfig['sp'] = parseInt(param['sp']);
				}
				update_health();
				break;
				
			case 'heal_speed':
				UIconfig['hpps'] = parseFloat(param['hpps']);
				UIconfig['spps'] = parseFloat(param['spps']);
				break;
			
			case 'area_info':
				$("#F-console-map .map_block[mid]").removeClass("forbidden");
				$("#F-console-map .map_block[mid]").removeClass("dangerous");
				for(var areaid in param['dangerous']){
					$("#F-console-map .map_block[mid='"+param['dangerous'][areaid]+"']").addClass("dangerous");
				}
				for(var areaid in param['forbidden']){
					$("#F-console-map .map_block[mid='"+param['forbidden'][areaid]+"']").addClass("forbidden");
				}
				break;
				
			case 'proficiency':
				for(var type in param['proficiency']){
					$("#F-console-playerinfo .proficiency div[type='"+type+"']").html(param['proficiency'][type]);
				}
				break;
			
			case 'shop':
				$("#F-console-shop .counter div").removeClass("selected");
				$("#F-console-shop .counter div[cid='"+param['kind']+"']").addClass("selected");
				$("#F-console-shop .goods").html(parse_goods(param['goods']));
				update_price();
				
				$("#F-console-shop .goods button[action='add']").click(function(e){
					input = $(this).parent().find("input");
					if(parseInt(input.val()) < parseInt(input.attr("max"))){
						input.val(parseInt(input.val()) + 1);
					}
					update_price();
				});
				
				$("#F-console-shop .goods button[action='cut']").click(function(e){
					input = $(this).parent().find("input");
					if(parseInt(input.val()) > 0){
						input.val(parseInt(input.val()) - 1);
					}
					update_price();
				});
				
				$("#F-console-shop .goods .item").mousewheel(function(e, delta){
					e.preventDefault();
					input = $(this).find("input");
					if(delta > 0){
						if(parseInt(input.val()) < parseInt(input.attr("max"))){
							input.val(parseInt(input.val()) + 1);
						}
					}else if(delta < 0){
						if(parseInt(input.val()) > 0){
							input.val(parseInt(input.val()) - 1);
						}
					}
					update_price();
				});
				
				break;
			
			case 'buff_name':
				UIvar['buff_name'] = param;
				break;
			
			case 'buff_help':
				UIvar['buff_help'] = param;
				break;
			
			case 'chat_msg':
				insert_chat_msg(param['time'], param['msg']);
				break;
			
			case 'notice':
				notice_msg(param['msg'], param['time']);
				break;
			
			case 'feedback':
				feedback.push({type:"feedback", msg:param['msg'], time:param['time']});
				break;
			
			case 'error':
				feedback.push({type:"error", msg:param['msg'], time:param['time']});
				break;
			
			case 'die':
				var dtime = new Date(param['time'] * 1000);
				$("#F-console-brief .die .time .h").html((dtime.getHours() + 100).toString().substr(1));
				$("#F-console-brief .die .time .m").html((dtime.getMinutes() + 100).toString().substr(1));
				$("#F-console-brief .die .time .s").html((dtime.getSeconds() + 100).toString().substr(1));
				var killers_html = "";
				for(var kindex in param['killer']){
					killers_html += '<div class="killer-avatar"><img src="'+param['avatar'][kindex]+'"></div>';
					killers_html += '<div class="killer-name">'+param['killer'][kindex]+'</div>';
				}
				$("#F-console-brief .die .killers").html(killers_html);
				$("#F-console-brief .die .reason").html(param["reason"]);
				
				show_brief('die');
				UIvar['alive'] = false;
				break;
			
			case 'end':
				show_brief('end');
				break;
			
			case 'brief':
				show_brief('brief', 0);
				$("#F-console-brief .brief").html(param['html']);
				$("#F-console-brief .brief").click(function(){
					$("#F-console-brief").fadeOut(400);
				});
				break;
			
			case 'init':
				init_gameUI();
				break;
			
			case 'game_settings':
				UIconfig['poison_damage'] = param['poison_damage'];
				UIconfig['poison_recover'] = param['poison_recover'];
				break;
			
			case 'currency':
				UIconfig['currency'] = param['name'];
				break;
			
			case 'need_login':
				alert("请先登录");
				break;
			
			case 'game_over':
				alert("游戏尚未开始");
				break;
			
			case 'need_join':
				init_join(param);
				break;
			
			case 'performance':
				show_performance = true;
				process_msec = parseInt(param['process_sec'] * 1000);
				break;
				
			default:
				debug("unexpected action: "+action);
				break;
		}
	
		debug(data[aid]);
	}
	
	if(feedback.length > 0){
		var current_time = Date.parse(new Date());
		$("#F-console-feedback .feedback").each(function(){
			if(current_time - parseInt($(this).attr("time")) >= 1500) {
				$(this).slideUp(200, function () {
					$(this).remove();
				})
			}
		});
		$("#F-console-feedback .error").each(function(){
			if(current_time - parseInt($(this).attr("time")) >= 1500) {
				$(this).slideUp(200, function () {
					$(this).remove();
				})
			}
		});
		
		for(var i in feedback){
			if(feedback[i]['type'] == 'feedback'){
				feedback_msg(feedback[i]['msg'], feedback[i]['time']);
			}else{
				error_msg(feedback[i]['msg'], feedback[i]['time']);
			}
		}
	}
	
	if(show_performance){
		var result1 = '';
		var result2 = '';
		if(param['process_sec'] != undefined){
			result1 += '<div class="key">网络延迟：</div><div class="value">'+(respond_msec - request_msec - process_msec)+'毫秒</div>';
			result1 += '<div class="key">处理时间：</div><div class="value">'+process_msec+'毫秒</div>';
			result2 += '<div class="key">渲染时间：</div><div class="value">'+((new Date()).valueOf() - respond_msec)+'毫秒</div>';
		}
		if(param['db_query_times']){
			result2 += '<div class="key">数据库操作量：</div><div class="value">'+param['db_query_times']+'次</div>';
		}
		$("#footer .performance .frame[fid='0']").html(result1);
		$("#footer .performance .frame[fid='1']").html(result2);
	}
}

function error_msg(msg, time){
	var current_time = Date.parse(new Date());
	$("#F-console-feedback").append('<div class="error new" time="'+current_time.toString()+'">'+msg+'</div>');
	$("#F-console-feedback .error.new").slideDown(200);
	$("#F-console-feedback .error.new").click(function(){
		$(this).slideUp(200, function(){
			$(this).remove();
		});
	})
	$("#F-console-feedback .error.new").removeClass("new");
}

function feedback_msg(msg, time){
	var current_time = Date.parse(new Date());
	$("#F-console-feedback").append('<div class="feedback new" time="'+current_time.toString()+'">'+msg+'</div>');
	$("#F-console-feedback .feedback.new").slideDown(200);
	$("#F-console-feedback .feedback.new").click(function(){
		$(this).slideUp(200, function(){
			$(this).remove();
		});
	})
	$("#F-console-feedback .feedback.new").removeClass("new");
}

function notice_msg(msg, time){
	$("#F-console-notice").prepend('<div class="notice new" sec="5">'+msg+'</div>');
	$("#F-console-notice .notice.new").slideDown(200);
	$("#F-console-notice .notice.new").click(function(){
		$(this).slideUp(200, function(){
			$(this).remove();
		});
	})
	$("#F-console-notice .notice.new").removeClass("new");
}

function init_gameUI(){
	comet_connect();
	switch_frame('game');
	UIvar = {'wound_dressing' : []};
	UIconfig = { mhp : 0 , msp : 0 , hp : 0 , sp : 0 , hpps : 0 , spps : 0 , cexp : 0 , texp : 0 , capacity : 0 };
	
	$("#nav-title").click(function(){
		$("#F-console-feedback .feedback").slideUp(200, function(){
			$(this).remove();
		});
		$("#F-console-feedback .error").slideUp(200, function(){
			$(this).remove();
		});
	});
	
	UIvar['shop_visible'] = false;
	UIvar['alive'] = true;
	UIvar['disable_drop'] = false;
	UIvar['buff_name'] = {};
	UIvar['buff_help'] = {};
	
	$("#F-console-shop .submit").click(function(e){
		cart = {};
		$("#F-console-shop .goods .controller input").each(function(){
			if($(this).val() > 0){
				cart[$(this).parent().parent().attr('iid')] = $(this).val();
				$(this).val(0);
				update_price();
			}
		});
		request('buy', {cart: cart});
	});
	
	$("#F-console-shop .back").click(function(e){
		$("#F-console-center .wrapper[content='shop']").fadeOut(400);
	});
	
	$("#F-console-shop .counter div").click(function(e){
		request('get_goods', {kind : $(this).attr("cid")});
	});
	
	//Panel
	panel_block("package");
	
	$("#F-console-panel .title[target]").click(function(e){
		panel_block($(e.target).attr("target"));
	});
	
	UIvar["panel_action"] = {};
	
	panel_selector_default("equipment");
	panel_selector_default("package");
	
	$("#F-console-panel .selector").click(function(e){
		panel_selector(e.target);
	});
	
	$("#F-console-panel .item_panel .submit").click(submit_item);
	
	$("#F-console-panel .tactic_panel .tactic").click(function(e){
		action = $(this).parent().attr("acceptor");
		tid = $(this).attr("tid");
		request(action, {tid : tid});
	});
	
	$("#F-console-panel-shop").click(function(e){
		request('get_goods', {kind : 0});
		$("#F-console-center .wrapper[content='shop']").fadeIn(400);
	});
	
	$("#F-console-panel-wound_dressing").click(function(e){
		show_wound_dressing();
	});
	
	//Collecting
	$("#F-console-collecting button[action='collect']").click(collect_item);
	$("#F-console-collecting button[action='drop']").click(drop_collecting_item);
	$("#F-console-collecting button[action='merge']").click(submit_item);
	
	//Team
	$("#F-console-teaminfo button").click(function(e){
		action = $(this).attr("action");
		switch(action){
			case 'create':
			case 'join':
				name = $("#F-console-teamname").val();
				pass = $("#F-console-teampass").val();
				request(action+'_team', {name: name, pass: pass});
				break;
			
			case 'leave':
				request('leave_team');
				break;
		}
	});
	
	//Chat
	UIvar['chat_visible'] = true;
	UIvar['chat_num'] = 0;
	
	$("#F-console-chat-dialog").css("width", 350);
	$("#F-console-chat-speak").click(function(){
		$("#F-console-chat-speak").fadeOut(400);
		$("#F-console-chat-form").fadeIn(400, function(){
			$("#F-console-chat-input").focus();
		});
		$("#F-console-chat-dialog").css("width", "");
		$("#F-console-chat-dialog").css("right", 300);
	});
	
	$("#F-console-chat-input").blur(function(){
		$("#F-console-chat-speak").fadeIn(400);
		$("#F-console-chat-form").fadeOut(400, function(){
			$("#F-console-chat-dialog").css("width", 350);
			$("#F-console-chat-dialog").css("right", "");
		});
	});
	
	$("#F-console-chat-form").submit(function(e){
		e.preventDefault();
		chat_content = $("#F-console-chat-input").val();
		$("#F-console-chat-input").val("");
		request("chat_send", { content : chat_content });
	});
	
	$("#F-console-chat-toggle-button").click(function(){
		if(UIvar['chat_visible'] == true){
			$("#F-console-chat-display").fadeOut();
			$("#F-console-chat-toggle-button").html("+");
			UIvar['chat_visible'] = false;
		}else{
			$("#F-console-chat-display").fadeIn();
			$("#F-console-chat-toggle-button").html("-");
			UIvar['chat_visible'] = true;
		}
	});
	
	//Map
	$("#F-console-map-table td").click(function(e){
		mid = $(e.target).attr("mid");
		if(mid != "-1"){
			request("move", { destination : mid });
		}
	});
	
	$("#F-console-map-table .mask").click(function(e){
		$("#F-console-map-table .mask").hide();
		$("#F-console-map-table .radar").removeClass("radar");
		$("#F-console-map-table .map_block").each(function(e){
			$(this).html($(this).attr("name"));
			$(this).removeAttr("name");
		});
	});
	
	setInterval("daemon()", 1000);
}

function init_join(param){
	enter_change_avatar(param["gender"], param["icon"]);
	$("#F-enter-form-icon-f").val(0);
	$("#F-enter-form-icon-m").val(0);
	
	$("#F-enter-form .icon-selector select").change(function(){
		gender = $("#F-enter-form input[name='gender']:checked").val();
		icon = $(this).children('option:selected').val();
		enter_change_avatar(gender, icon);
	});
	
	$("#F-enter-info input[type='radio']").change(function(){
		gender = $(this).val();
		icon = $("#F-enter-form .icon-selector select#F-enter-form-icon-"+gender).children('option:selected').val();
		enter_change_avatar(gender, icon);
		switch(gender){
			case "f":
				$("#F-enter-form-icon-f").show();
				$("#F-enter-form-icon-m").hide();
				break;
			
			case "m":
			default:
				$("#F-enter-form-icon-f").hide();
				$("#F-enter-form-icon-m").show();
				break;
		}
	});
	
	$("#F-enter-form input[value='"+param["gender"]+"']").attr("checked", true);
	$("#F-enter-form-icon-"+param["gender"]).show();
	$("#F-enter-form-icon-"+param["gender"]).val(param["icon"]);
	$("#F-enter-form-motto").attr("value", param["motto"]);
	$("#F-enter-form-killmsg").attr("value", param["killmsg"]);
	$("#F-enter-form-lastword").attr("value", param["lastword"]);
	
	switch_frame('enter');
	
	$("#F-enter-form").submit(enter_submit);
}

function enter_change_avatar(gender, icon){
	uri = 'img/' + gender + "_" + icon + ".gif";
	$("#F-enter-avatar").attr("src", uri);
}

function enter_submit(e){
	e.preventDefault();
	gender = $("#F-enter-form input[name='gender']:checked").val();
	icon = $("#F-enter-form .icon-selector select#F-enter-form-icon-"+gender).children('option:selected').val();
	request('enter_game', {
		icon : icon,
		gender : gender,
		motto : $("#F-enter-form-motto").val(),
		killmsg : $("#F-enter-form-killmsg").val(),
		lastword : $("#F-enter-form-lastword").val()
	});
}

function request(action, param){
	if(ajax_lock){
		return;
	}
	ajax_lock = true;
	ajax_plock = true;
	
	if(param == undefined){
		param = {};
	}
	
	request_msec = (new Date()).valueOf();
	
	$.post("command.php", { action : action , param : param }, function(data, status){
		ajax_lock = false;
		ajax_plock = false;
		if(!status){
			error_msg("网络传输错误");
		}else{
			respond(data);
		}
	}, "json")
	.error(function(e){
		debug("Unparsable Content: " + e.responseText);
		console.debug(e.responseText);
	});
}

function comet_connect(){
	if(is_iOS){
		$("div#comet").html("<iframe src='comet.php?method=long_polling' />");
	}else{
		$("div#comet").html("<iframe src='comet.php?method=streaming' />");
	}
}

function comet_respond(data){
	respond(data);
	debug(data);
}

$(document).ready(function(){
	frame = ['error', 'enter', 'game'];
	current_frame = '';
	ajax_lock = false;
	
	request('init');
});
