<?php

class game_thbr extends game_bra
{
	
	protected function generate_welcome_message()
	{
		$message = '<div class="welcome">'.
			'<div class="text">'.
			'	你看到了一行剧情<br><br>'.
			'	你也许还看到了一幅CG<br><br>'.
			'	随便点哪儿继续'.
			'</div>'.
		'</div>';
		return $message;
	}
	
	protected function generate_forbidden_sequence()
	{
		$arealist = parent::generate_forbidden_sequence();
		
		//香霖堂不会一禁
		for($i = 0; $i <= $GLOBALS['round_area']; $i++){
			if($arealist[$i] == 14){
				$temp = $arealist[$i];
				$arealist[$i] = $arealist[$GLOBALS['round_area'] + 1];
				$arealist[$GLOBALS['round_area'] + 1] = $temp;
				break;
			}
		}
		
		return $arealist;
	}
	
	protected function new_npc(&$player)
	{
		return array_merge(parent::new_npc($player), array(
			'icon' => 'img/thbr/n_'.$player['icon'].'.png'
			));
	}
	
	protected function np_generate_icon(&$user, $gender)
	{
		global $param, $icon_num;
		
		if(false === isset($param['icon'])){
			$param['icon'] = $user['icon'];
			return $user['iconuri'];
		}
		
		$icon = $param['icon'];
		
		if($icon === 'customed'){
			return 'img/upload/'.md5($user['username']).'.img';
		}else{
			if($icon > $icon_num[$gender]){
				throw_error("头像设置错误");
			}
			
			if($icon == 0){
				$icon = mt_rand(1, $icon_num[$gender]);
			}
		
			return 'img/thbr/'.$gender.'_'.$icon.'.png';
		}
	}
	
	protected function np_generate_club(&$user)
	{
		return (isset($GLOBALS['param']['club']) && $GLOBALS['param']['club'] > 0 && $GLOBALS['param']['club'] < sizeof($GLOBALS['clubinfo'])) ? $GLOBALS['param']['club'] : $GLOBALS['g']->random(1, sizeof($GLOBALS['clubinfo']) - 1);
	}
	
	public function game_forbid_area()
	{
		$return = game::game_forbid_area(); //不调用BRA的禁区（BRA实现了禁区死亡），直接调用BRN的禁区，然后重新实现禁区死亡
		
		global $db, $map;
		$forbidden = $this->gameinfo['forbiddenlist'];
		$safe = array();
		$all = array();
		for($i = 0; $i < sizeof($map); $i ++){
			if(false === in_array($i, $forbidden)){
				$safe[] = $i;
			}
			$all[] = $i;
		}
		
		//禁区死亡
		$players_dying = $db->select('players', '*', array('type' => GAME_PLAYER_USER, 'area' => array('$in' => $forbidden), 'hp' => array('$gt' => 0), 'tactic' => array('$ne' => 3)));
		if(is_array($players_dying)){
			foreach($players_dying as $pdata){
				$player = new_player($pdata);
				foreach($player->buff as &$buff){
					switch($buff['type']){
						//八云紫套三件效果
						case 'yukari_suit':
							if($buff['param']['quantity'] >= 3){
								$player->notice('八云紫的力量让你躲避了禁区死亡');
								$player->data['area'] = $safe[array_rand($safe)];
								$player->ajax('location', array('name' => $GLOBALS['map'][$player->data['area']], 'shop' => in_array(intval($player->data['area']), $GLOBALS['shopmap'], true)));
								continue 2; //自动躲避禁区
							}
							
						//毛玉套三件效果
						case 'kedama_suit':
							if($buff['param']['quantity'] >= 3){
								$player->notice('毛玉的力量让你躲避了禁区死亡');
								$player->data['area'] = $safe[array_rand($safe)];
								$player->ajax('location', array('name' => $GLOBALS['map'][$player->data['area']], 'shop' => in_array(intval($player->data['area']), $GLOBALS['shopmap'], true)));
								continue 2; //自动躲避禁区
							}
						
						default:
							break;
					}
				}
				$player->sacrifice(array('type' => 'forbid'));
			}
		}
		unset($players_dying);
		
		//NPC换区
		$this->moving_NPC($all, $safe);
		
		//飞空毛玉
		if($this->gameinfo['round'] == 1){
			$db->delete('players', array('name' => '飞空毛玉', 'type' => GAME_PLAYER_NPC));
		}
		
		return $return;
	}
	
	public function insert_news($type, $args = array())
	{
		
		$content = '';
		switch($type){
			case 'aya_ridicule':
				$attacker = '<span class="username">'.$args['attacker'].'</span>';
				$defender = '<span class="username">'.$args['defender'].'</span>';
				switch($GLOBALS['g']->random(0,4)){
					case 0:
						$rhetoric = '<span class="username">'.$defender.'</span>惨遭重创';
						break;
					
					case 1:
						$rhetoric = '<span class="username">'.$defender.'</span>被<span class="username">'.$attacker.'</span>打得灰头土脸';
						break;
					
					case 2:
						$rhetoric = '<span class="username">'.$defender.'</span>受到了<span class="username">'.$attacker.'</span>的打击，吓得落荒而逃';
						break;
					
					case 3:
						$rhetoric = '<span class="username">'.$attacker.'</span>在遭遇战中玩弄<span class="username">'.$defender.'</span>于鼓掌之间';
						break;
					
					default:
						$rhetoric = '由于屡战屡败，<span class="username">'.$defender.'</span>对<span class="username">'.$attacker.'</span>产生了心理阴影';
						break;
				}
				$content = '文文·新闻：'.$rhetoric;
				break;
			
			case 'horai':
				$content = '<span class="username">'.$args['name'].'</span>体内的蓬莱之药发出了光芒，<span class="username">'.$args['name'].'</span>复活了';
				break;

			case 'kill':
				$args['type'] = isset($args['type']) ? $args['type'] : 'default';
				switch($args['type']){
					case 'weapon_sc':
						$content = '<span class="username">'.$args['killer'].'</span>使用<span class="weapon">'.$args['weapon'].'</span>将<span class="username">'.$args['deceased'].'</span>击杀';
						break;

					case 'diamond':
						$content = '<span class="username">'.$args['deceased'].'</span>被钻石星辰撕裂伤口，失血过多死亡';
						break;

					default:
						return parent::insert_news($type, $args);
				}
				break;
			default:
				$content =  parent::insert_news($type, $args);
				if($type != 'damage'){
					$GLOBALS['a']->action('chat_msg', array('msg' => $content, 'time' => time()), true);
				}
				return $content;
				break;
		}
		
		global $db;
		$db->insert('news', array('time' => time(), 'content' => $content));
		
		if($type != 'damage'){
			$GLOBALS['a']->action('chat_msg', array('msg' => $content, 'time' => time()), true);
		}
		
		$this->update_news_cache();
		
		return $content;
	}
	
	public function &summon_npc($nid){
		include (get_mod_path(MOD_NAME).'/init/npc.php');
		
		if(!isset($npcinfo[$nid])){
			return throw_error('NPC #'.$this->data['sk']['id'].' doesn\'t exist');
		}
		
		$npc = $npcinfo[$nid];
		
		$sub = isset($npc['sub']) ? $npc['sub'] : array(array());
		unset($npc['sub']);
		
		$npc = array_merge($npc, $sub[array_rand($sub)]);
		$npc['number'] = 1;
		$npc = $this->new_npc($npc);
		
		$GLOBALS['db']->insert('players', $npc);
		
		return $npc;
	}
}

?>