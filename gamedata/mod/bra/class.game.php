<?php

class game_bra extends game
{
	public function game_end($type = 'timeup', $winner = array(), $mode = 'team')
	{
		$winner = parent::game_end($type, $winner, $mode);
		
		return;
	}
	
	public function game_forbid_area()
	{
		$return = parent::game_forbid_area();
		
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
				$player->sacrifice(); //TODO: 死因
			}
		}
		unset($players_dying);
		
		//NPC换区
		$this->moving_NPC($all, $safe);
		
		return $return;
	}
	
	public function moving_NPC($areas, $targets)
	{
		global $db;
		
		if(sizeof($targets) === 0){
			return;
		}
		
		$npcs = $db->select('players', array('_id', 'skill'), array('type' => GAME_PLAYER_NPC, 'area' => array('$in' => $areas), 'hp' => array('$gt' => 0)));
		foreach($npcs as $ndata){
			if(false === in_array('Peg', $ndata['skill'])){
				$target = $targets[array_rand($targets)];
				$db->update('players', array('area' => $target), array('_id' => $ndata['_id']));
			}
		}
		
		return;
	}
	
	protected function npc_area(&$player)
	{
		if(false === isset($player['pls'])){
			$player['pls'] = 99;
		}
		switch(intval($player['pls'])){
			case 99:
				global $map;
				$forbidden = $this->gameinfo['forbiddenlist'];
				$safe = array();
				for($i = 0; $i < sizeof($map); $i ++){
					if(false === in_array($i, $forbidden)){
						$safe[] = $i;
					}
				}
				return $safe[array_rand($safe)];
				
			default:
				return intval($player['pls']);
		}
		return false;
	}
	
	protected function new_player()
	{
		$player = parent::new_player();
		$player['exp'] = $GLOBALS['gameinfo']['round'] * 75; //晚进游戏的补偿
		return $player;
	}
	
	public function player_data_preprocess(&$data)
	{
		parent::player_data_preprocess($data);
		
		//怒气精度调整
		$data['rage'] /= $GLOBALS['health_accuracy'];
	}
	
	public function player_data_postprocess(&$data)
	{
		parent::player_data_postprocess($data);
		
		//怒气精度调整
		$data['rage'] = round($data['rage'] * $GLOBALS['health_accuracy']);
	}
	
	protected function blank_player()
	{
		$data = parent::blank_player();
		//增加怒气栏位
		$data['rage'] = 0;
		return $data;
	}
	
	protected function np_generate_combat_index($skills)
	{
		$index = parent::np_generate_combat_index($skills);
		
		foreach($skills as $skill){
			switch($skill){
				case 'Pro_P':
					$index['p'] += 20;
					break;
				
				case 'Pro_K':
					$index['k'] += 20;
					break;
				
				case 'Pro_G':
					$index['g'] += 20;
					break;
				
				case 'Pro_C':
					$index['c'] += 20;
					break;
				
				case 'Pro_D':
					$index['d'] += 20;
					break;
			}
		}
		
		return $index;
	}
	
	public function enter_game()
	{
		parent::enter_game();
		$GLOBALS['a']->action('brief', array('html' => $this->generate_welcome_message()));
	}
	
	protected function generate_welcome_message()
	{
		return
			'<div class="welcome"><div class="text">
				你看到了一行剧情<br><br>
				你也许还看到了一幅CG<br><br>
				随便点哪儿继续
			</div></div>';
	}
	
	public function insert_news($type, $args = array())
	{
		$content = '';
		switch($type){
			case 'damage':
				$damage = $args['damage'];
				$attacker = $args['attacker'];
				$defender = $args['defender'];
				
				if($damage > 1000){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>燃烧自己的生命得到了不可思议的力量！！！！！ 「<span class="damage">'.$damage.'</span>」 点的伤害值，没天理啊…<span class="username">'.$defender.'</span>死-定-了！！！！！</span>';
				}else if($damage > 750){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>受到天神的加护，打出惊天动地的一击 – <span class="username">'.$defender.'</span>被打掉<span class="damage">'.$damage.'</span>点生命值！！！！！</span>';
				}else if($damage > 600){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>手中的武器闪耀出七彩光芒！！！！<span class="username">'.$defender.'</span>招架不住，生命减少<span class="damage">'.$damage.'</span>点！！！！</span>';
				}else if($damage > 500){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>使出呼风唤雨的力量！！！！可怜的<span class="username">'.$defender.'</span>受到了<span class="damage">'.$damage.'</span>点的伤害！！！！</span>';
				}else if($damage > 400){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>使出浑身解数奋力一击！！！受到了<span class="damage">'.$damage.'</span>点伤害！！！<span class="username">'.$defender.'</span>还活着吗？</span>';
				}else if($damage > 300){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>发出会心一击！！！<span class="username">'.$defender.'</span>损失了<span class="damage">'.$damage.'</span>点生命！！！</span>';
				}else if($damage > 250){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>简直不是人！！<span class="username">'.$defender.'</span>瞬间被打了<span class="damage">'.$damage.'</span>点伤害！！</span>';
				}else if($damage > 200){
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>拿了什么神兵！？<span class="username">'.$defender.'</span>被打了<span class="damage">'.$damage.'</span>滴血！！</span>';
				}else{
					$content = '<span class="damage-news"><span class="username">'.$attacker.'</span>对<span class="username">'.$defender.'</span>做出了<span class="damage">'.$damage.'</span>点的攻击！ 一定是有练过！</span>';
				}
				break;
			
			case 'flounder':
				$content = '<span class="system"><span class="username">'.$args['caster'].'</span> 招来一阵怪风，把地上的尸体都吹走了！</span>';
				break;
			
			default:
				parent::insert_news($type, $args);
				return;
				break;
		}
		
		global $db;
		$db->insert('news', array('time' => time(), 'content' => $content));
		
		$this->update_news_cache();
	}
	
}

?>