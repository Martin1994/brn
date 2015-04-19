<?php

/**
 * Class player_bra
 *
 * @property float rage 怒气值
 */
class player_bra extends player
{
	
	public function move($destination)
	{
		global $g, $map, $weatherinfo;

		if(intval($this->area) !== $destination && intval($g->gameinfo['weather']) === 11){
			//龙卷风
			$areainfo = $g->get_areainfo();
			do{
				$destination = $g->random(0, sizeof($weatherinfo) - 1);
			}while(in_array($destination, $areainfo['forbidden']));
		}
		$area = $this->area;
		
		parent::move($destination);
		
		if(intval($g->gameinfo['weather']) === 11){
			$this->notice('一阵龙卷风把你刮到了'.$map[$destination]);
		}else if(intval($area) !== intval($destination) && intval($g->gameinfo['weather']) === 13){
			$damage = $g->random(1, 4);
			$this->damage($damage);
			$this->notice('冰雹砸中了你，造成了'.$damage.'点伤害');
		}
	}
	
	//合成增加熟练度
	public function item_compose($iids)
	{
		$success = parent::item_compose($iids);
		
		if($success){
			$this->data['proficiency']['d'] ++;
		}
		
		return $success;
	}
	
	//下毒效果增益
	public function get_poison_power($kind, $item_e)
	{
		global $poison;
		switch(substr($kind, 0, 1)){
			case 'W':
				return $poison['Wturn'];
				break;
			
			//烹饪社持续时间翻倍
			case 'H':
				return intval($poison['Hlast'] * $item_e * (in_array('Glutton', $this->skill) ? 1.5 : 1));
				break;
			
			default:
				return -1;
				break;
		}
	}
	
	public function wound_dressing($position)
	{
		switch($position){
			case 'b':
				$pname = '胸部';
				$buff_type = 'injured_body';
				break;
			
			case 'h':
				$pname = '头部';
				$buff_type = 'injured_head';
				break;
			
			case 'a':
				$pname = '腕部';
				$buff_type = 'injured_arm';
				break;
			
			case 'f':
				$pname = '足部';
				$buff_type = 'injured_foot';
				break;
			
			default:
				$this->error('请指定正确的包扎部位');
				return;
		}
		
		foreach($this->buff as $bid => $buff){
			if($buff['type'] === $buff_type){
				$consumption = $this->get_consumption('wound_dressing');
				$this->check_health($consumption, 'wound_dressing');
				
				$this->remove_buff($bid);
				
				break; //改为continue可一次包扎全部同类伤口
			}
		}
		
		$this->feedback($pname.'包扎完成了');
	}
	
	public function calculate_battle_info($ajax = true)
	{
		parent::calculate_battle_info(false);
		
		if($this->data['equipment']['art']['n'] != ''){
			if(isset($this->data['equipment']['art']['sk']['att-buff'])){
				$this->att += $this->data['equipment']['art']['sk']['att-buff'];
			}
		}
		
		if($this->data['equipment']['art']['n'] != ''){
			if(isset($this->data['equipment']['art']['sk']['def-buff'])){
				$this->def += $this->data['equipment']['art']['sk']['def-buff'];
			}
		}
		
		if($this->data['equipment']['art']['n'] != ''){
			if(isset($this->data['equipment']['art']['sk']['att-debuff'])){
				$this->att -= $this->data['equipment']['art']['sk']['att-buff'];
			}
		}
		
		if($this->data['equipment']['art']['n'] != ''){
			if(isset($this->data['equipment']['art']['sk']['def-debuff'])){
				$this->def -= $this->data['equipment']['art']['sk']['def-buff'];
			}
		}
		
		$this->ajax('battle_data', array('att' => $this->att, 'def' => $this->def));
	}
	
	protected function get_discover_threshold($mode)
	{
		global $gameinfo;
		
		if(($gameinfo['gamestate'] & GAME_STATE_COMBO) == GAME_STATE_COMBO){
			if(intval($this->pose) === 3){
				return parent::get_discover_threshold($mode);
			}else{
				return 100;
			}
		}else{
			return parent::get_discover_threshold($mode);
		}
	}
	
	protected function levelup($extra_text = '')
	{
		global $g;

		$extra_hp = $g->random(8, 10);
		$extra_att = $g->random(2, 4);
		$extra_def = $g->random(3, 5);
		
		$this->data['baseatt'] += $extra_att;
		$this->data['basedef'] += $extra_def;
		$this->calculate_battle_info();
		
		$this->data['mhp'] += $extra_hp;
		$this->heal('hp', $extra_hp);
		$this->heal('sp', 50);
		
		$this->ajax('max_health', array('mhp' => $this->mhp, 'msp' => $this->msp));
		
		$extra_text .= '，生命提升了'.$extra_hp;
		$extra_text .= '，攻击提升了'.$extra_att;
		$extra_text .= '，防御提升了'.$extra_def;
		
		parent::levelup($extra_text);
	}

	/**
	 * @param player_bra $enemy
	 */
	public function found_enemy($enemy)
	{
		global $g;

		if(false === $enemy->is_alive()){
			parent::found_enemy($enemy);
		}else if(strval($enemy->teamID) !== '-1' && $enemy->teamID === $this->teamID){
			parent::found_enemy($enemy);
		}else if($g->determine($this->get_emptive_rate($enemy))){
			parent::found_enemy($enemy);
		}else{
			$this->feedback($enemy->name.'突然向你袭来');
			$combat = new_combat($enemy, $this);
			$combat->battle_start();
			
			$this->update_enemy_info($enemy, $enemy->is_alive());
		}
	}

	/**
	 * 获得先发攻击的概率
	 * @param player $enemy
	 * @return int
	 */
	protected function get_emptive_rate(player $enemy)
	{
		global $modulus_emptive, $base_emptive;
		
		$emptive = $base_emptive;
		
		if(isset($modulus_emptive['weather'])){
			global $gameinfo;
			if(isset($modulus_emptive['weather'][intval($gameinfo['weather'])])){
				$emptive *= $modulus_emptive['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_emptive['area'])){
			if(isset($modulus_emptive['area'][intval($this->data['area'])])){
				$emptive *= $modulus_emptive['area'][intval($this->data['area'])];
			}
		}
		
		if(isset($modulus_emptive['pose'])){
			if(isset($modulus_emptive['pose'][intval($this->data['pose'])])){
				$emptive *= $modulus_emptive['pose'][intval($this->data['pose'])];
			}
		}
		
		if(isset($modulus_emptive['tactic'])){
			if(isset($modulus_emptive['tactic'][intval($this->data['tactic'])])){
				$emptive *= $modulus_emptive['tactic'][intval($this->data['tactic'])];
			}
		}
		
		return $emptive;
	}
	
	protected function get_base_heal_rate()
	{
		$heal_rate = parent::get_base_heal_rate();
		
		if(intval($this->pose) === 5){
			$heal_rate['hp'] *= 2;
		}
		
		foreach($this->buff as $buff){
			if($buff['type'] === 'injured_body'){
				$heal_rate['hp'] /= 2;
			}
		}
		
		return $heal_rate;
	}
	
	protected function get_consumption($action)
	{
		$consumption = parent::get_consumption($action);
		
		switch($action){
			case 'move':
				$injured = false;
				foreach($this->buff as $buff){
					if($buff['type'] === 'injured_foot'){
						$consumption['sp'] += 5;
					}
				}
				
				if(in_array('Pheidippides', $this->skill) && !$injured){
					$consumption['sp'] -= 3;
				}
				break;
			
			case 'search':
				$injured = false;
				foreach($this->buff as $buff){
					if($buff['type'] === 'injured_arm'){
						$consumption['sp'] += 5;
					}
				}
				
				if(in_array('Detector', $this->skill) && !$injured){
					$consumption['sp'] -= 3;
				}
				break;
		}
		
		return $consumption;
	}
	
	protected function buff_handler(&$buff, &$param)
	{
		switch($buff['type']){
			
			default:
				parent::buff_handler($buff, $param);
				break;
		}
	}
	
	public function get_basic_info($show_fog = false, $show_hp = false)
	{
		global $gameinfo, $fog_avatar;
		switch($show_fog || !$this->is_alive() ? -1 : intval($gameinfo['weather'])){
			case 8:
			case 9:
				$name = '？？？？';
				$avatar = $fog_avatar;
				$number = '？';
				$gender = '？';
				$status = '<span class="fog">？？？？</span>';
				break;
			
			default:
				return parent::get_basic_info($show_fog, $show_hp);
				break;
		}
		return array(
			'name' => $name,
			'avatar' => $avatar,
			'number' => $number,
			'gender' => $gender,
			'status' => $status
			);
	}
	
	protected function found_item($item)
	{
		global $g;
		
		if($item['k'] === 'TO'){
			//中陷阱
			$pid = isset($item['sk']['owner']) ? $item['sk']['owner'] : false;
			$damage = $this->calculate_trap_damage($item);
			$damage = $this->damage($damage, array('pid' => $pid, 'weapon' => $item['n'], 'type' => 'trap'));
			global $healthinfo;
			$this->feedback('糟糕，你中了'.$item['n'].'，失去了'.$damage.'点'.$healthinfo['hp']);

			//添加中陷阱公告
			if(isset($item['sk']['owner'])){
				$victimizer = new_player($g->get_player_by_id($item['sk']['owner']));
				$g->insert_news('trap', array('victim' => $this->name, 'victimizer' => $victimizer->name, 'item' => $item['n'], 'damage' => $damage));
			}
		}else{
			parent::found_item($item);
		}
	}
	
	public function chat_send($msg)
	{
		global $a;
		//添加地区信息
		$content = '<span class="area">【'.$GLOBALS['map'][$this->area].'】</span>';
		$content .= '<span class="username">'.$this->name.':</span>';
		$content .= '<span class="chatmsg">'.$msg.'</span>';
		$a->action('chat_msg', array('msg' => $content, 'time' => time()), true);
	}
}

?>