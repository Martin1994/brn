<?php

class command
{
	
	protected $player;
	
	public function __construct($cplayer)
	{
		$this->player = $cplayer;
	}

	public function get_need_join_data(){
		global $cuser;
		return array(
			'gender' => $cuser['gender'],
			'icon' => $cuser['icon'],
			'avatar' => $cuser['iconuri'],
			'motto' => $cuser['motto'],
			'killmsg' => $cuser['killmsg'],
			'lastword' => $cuser['lastword'],
			'avatar_dir' => $GLOBALS['avatar_dir']
			);
	}
	
	public function action_handler($action, $param)
	{
		global $a, $g;
		$cplayer = $this->player;

		//如果用户没有登录则弹出登录窗口
		if($cplayer === false && $action !== 'enter_game'){
			$a->action('need_join', $this->get_need_join_data());
			$a->flush();
			return;
		}
		
		switch($action){
			case 'enter_game':
				$g->enter_game();
				$cplayer = $this->player = $GLOBALS['cplayer'];
			case 'init':
				$a->action('init', false, false, true); //初始化动作拥有最高优先级
				$a->action('game_settings', array('poison_damage' => $GLOBALS['poison']['damage'], 'poison_recover' => $GLOBALS['poison']['recover']));
				$a->action('name', array('name' => $cplayer->name));
				$a->action('avatar', array('src' => $cplayer->icon));
				$a->action('number', array('number' => strval($cplayer->number).' 号'));
				$a->action('gender', array('gender' => $GLOBALS['genderinfo'][$cplayer->gender]));
				$a->action('max_health', array('mhp' => $cplayer->mhp, 'msp' => $cplayer->msp));
				$a->action('health', array('hp' => $cplayer->hp, 'sp' => $cplayer->sp));
				$hr = $cplayer->get_heal_rate();
				$a->action('heal_speed', array('hpps' => $hr['hp'], 'spps' => $hr['sp']));
				$a->action('pose', array('tid' => $cplayer->pose));
				$a->action('tactic', array('tid' => $cplayer->tactic));
				$a->action('club', array('name' => $GLOBALS['clubinfo'][$cplayer->club]));
				$a->action('team', $cplayer->get_team_info());
				$a->action('battle_data', array('att' => $cplayer->att, 'def' => $cplayer->def));
				$a->action('proficiency', array('proficiency' => $cplayer->proficiency));
				$a->action('money', array('money' => $cplayer->money));
				$a->action('area_info', $GLOBALS['g']->get_areainfo());
				$a->action('location', array('name' => $GLOBALS['map'][$cplayer->area], 'shop' => in_array(intval($cplayer->area), $GLOBALS['shopmap'], true)));
				$a->action('weather', array('name' => $GLOBALS['weatherinfo'][$GLOBALS['gameinfo']['weather']]));
				$a->action('item', array('equipment' => $cplayer->parse_equipment(), 'package' => $cplayer->parse_package(), 'capacity' => intval($cplayer->capacity)));
				$a->action('buff_name', $GLOBALS['buff_name']);
				$a->action('buff', array('buff' => $cplayer->parse_buff()));
				$a->action('exp', array('current' => $cplayer->exp, 'target' => $cplayer->upexp, 'level' => $cplayer->lvl));
				$a->action('currency', array('name' => $GLOBALS['currency']));
				
				if(isset($cplayer->action['battle'])){
					//战斗状态中
					$enemy = $GLOBALS['db']->select('players', '*', array('_id' => $cplayer->action['battle']['pid']));
					$enemy = new_player($enemy[0]);
					$a->action('battle', array(
						'enemy' => $cplayer->get_enemy_info($enemy),
						'end' => false
						));
				}
				
				if(false === $cplayer->is_alive()){
					//已死亡
					$cplayer->show_death_info();
				}
				
				break;
			
			case 'use':
				if(false === isset($param['iid'])){
					throw_error('请指定要使用的物品');
				}
				if(false === isset($param['param'])){
					$param['param'] = array();
				}
				$cplayer->item_apply($param['iid'], $param['param']);
				break;
			
			case 'drop':
				if(false === isset($param['iid'])){
					throw_error('请指定要丢弃的物品');
				}
				$cplayer->item_drop($param['iid']);
				break;
			
			case 'unload':
				if(false === isset($param['iid'])){
					throw_error('请指定要卸下的装备');
				}
				$cplayer->unload($param['iid']);
				break;
			
			case 'collect':
				$cplayer->collect();
				break;
			
			case 'compose':
				if(false === isset($param['iid']) or false === is_array($param['iid'])){
					$cplayer->error('请指定要合成的装备');
				}
				$cplayer->item_compose($param['iid']);
				break;
			
			case 'merge':
				if(false === isset($param['iid']) or false === is_array($param['iid'])){
					$cplayer->error('请指定要合并的装备');
				}
				$cplayer->item_merge($param['iid']);
				break;
			
			case 'switch':
				$cplayer->weapon_switch();
				break;
				
			case 'move':
				if(false === isset($param['destination'])){
					$cplayer->error('请指定目的地');
				}
				$cplayer->move($param['destination']);
				break;
			
			case 'search':
				$cplayer->search();
				break;
			
			case 'attack':
				$cplayer->attack();
				break;
			
			case 'escape':
				$cplayer->escape();
				break;
			
			case 'strip':
				if(false === isset($param['iid'])){
					$cplayer->error('请指定要拿走的物品');
				}
				$cplayer->strip($param['iid']);
				break;
			
			case 'give':
				if(false === isset($param['iid'])){
					$cplayer->error('请指定要给予的物品');
				}
				$cplayer->give($param['iid']);
				break;
			
			case 'leave':
				$cplayer->leave();
				break;
			
			case 'pose':
				$cplayer->pose(intval($param['tid']));
				break;
			
			case 'tactic':
				$cplayer->tactic(intval($param['tid']));
				break;
			
			case 'get_goods':
				if(false === isset($param['kind'])){
					$param['kind'] = 0;
				}
				$cplayer->ajax('shop', array(
					'goods' => $cplayer->get_goods($param['kind'], true),
					'kind' => $param['kind']
					));
				break;
			
			case 'buy':
				if(false === isset($param['cart']) || false === is_array($param['cart'])){
					$cplayer->error('请指定要购买的物品');
				}
				$cplayer->buy($param['cart']);
				break;
			
			case 'create_team':
				if(false === isset($param['name'])){
					$cplayer->error('请指定队伍名字');
				}
				if(false === isset($param['pass'])){
					$cplayer->error('请指定队伍密码');
				}
				$cplayer->team_create($param['name'], $param['pass']);
				break;
			
			case 'join_team':
				if(false === isset($param['name'])){
					$cplayer->error('请指定队伍名字');
				}
				if(false === isset($param['pass'])){
					$cplayer->error('请指定队伍密码');
				}
				$cplayer->team_join($param['name'], $param['pass']);
				break;
			
			case 'leave_team':
				$cplayer->team_leave();
				break;
			
			case 'chat_send':
				$cplayer->chat_send($param['content']);
				break;
			
			//用于数据更新
			case 'update':
				break;
			
			default:
				$cplayer->error('Unexcepted action: '.$action.' | param: '.json_encode($param));
				break;
		}
	}
	
}

?>