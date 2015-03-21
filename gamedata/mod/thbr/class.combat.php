<?php

class combat_thbr extends combat_bra
{
	
	protected $extra_attack = false;
	
	public function __construct(player $att, player $def)
	{
		parent::__construct($att, $def);
		if($this->attacker->equipment['wep']['k'] == 'SW'){
			$this->feedback($this->attacker->name.' 发动了 '.$this->attacker->equipment['wep']['n']);
			$this->kind = 'sc';
		}
	}
	
	public function attack_without_counter($extra = false)
	{
		$this->extra_attack = $extra;
		$this->attacker->data['proficiency']['sc'] = floor(($this->attacker->data['proficiency']['d'] + max($this->attacker->data['proficiency']['p'], $this->attacker->data['proficiency']['k'], $this->attacker->data['proficiency']['g'], $this->attacker->data['proficiency']['c'], $this->attacker->data['proficiency']['d'])) / 2);
		
		$damage = 0;
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//冴月麟套四件效果
				case 'rin_suit':
					if($buff['param']['quantity'] >= 4){
						if($GLOBALS['g']->determine(30)){
							$this->feedback($this->attacker->name.' 发动了 风符「共振激励」 造成 '.$this->defender->name.' 当前生命值 25% 的伤害');
							$damage += $this->damage($this->defender->hp * 0.25);
						}
					}
				
				default:
					break;
			}
		}
		
		$damage += parent::attack_without_counter();
		
		if(!$extra && $this->defender->is_alive()){
			foreach($this->attacker->equipment['wep']['sk'] as $key => $value){
				switch($key){
					case 'lunar-incense':
						if($damage > 0){
							//仙香玉兔效果
							$this->defender->buff('lunar_incense', 1800, array('killer' => $this->attacker->_id)); //半小时后死亡
							$this->feedback($this->defender->name.'被 秘薬「仙香玉兎」 击中了，出现了幻觉，意志力正在慢慢被消耗');
						}
						break;
					
					default:
						break;
				}
			}
			
			foreach($this->defender->data['buff'] as $key => &$buff){
				switch($buff['type']){
					//打断回复
					case 'recover_hp':
					case 'recover_sp':
						if($damage <= 0){
							continue;
						}
						if($buff['param']['interrupt']){
							$this->defender->remove_buff($key);
							$this->feedback($this->defender->name.' 的回复增益被打断了');
						}
						break;
					
					default:
						break;
				}
			}
			
			foreach($this->attacker->data['buff'] as $key => &$buff){
				switch($buff['type']){
					//大小无寿
					case 'ageless_dream':
						if($damage <= 0){
							continue;
						}
						$this->attacker->remove_buff($key);
						$this->feedback('霊符「无寿の夢」 的效果消失了');
						break;
					
					case 'ageless_land':
						if($damage <= 0){
							continue;
						}
						$this->attacker->remove_buff($key);
						$this->feedback('寿命「无寿国への約束手形」 的效果消失了');
						break;
					
					//梦想天生
					case 'fantasy_nature':
						if($damage <= 0){
							continue;
						}
						$this->attacker->feedback('为 「夢想天生」 的发动积聚了能量！');
						$buff['param']['hits'] ++;
						if($buff['param']['hits'] >= 7){
							$this->attacker->feedback('「夢想天生」发动！');
							$this->attacker->remove_buff($key);
							
							//生成武器
							$weapon = array(
								'n' => '「夢想天生」',
								'k' => $this->attacker->equipment['wep']['k'],
								'e' => 1000,
								's' => 2,
								'sk' => array('accurate' => true)
								);
							
							$damage += $this->bonus_attack($weapon);
							
						}
						break;
					
					//灵梦套两件效果
					case 'reimu_suit':
						if($buff['param']['quantity'] >= 2){
							if($GLOBALS['g']->determine(25)){
								//生成武器
								$weapon = array(
									'n' => '珠符「明珠暗投」',
									'k' => $this->attacker->equipment['wep']['k'],
									'e' => 40,
									's' => 2,
									'sk' => array()
									);
								$this->feedback($this->attacker->name.' 发动了 珠符「明珠暗投」');
								$damage += $this->bonus_attack($weapon);
							}
						}
						break;
					
					//文套五件效果
					case 'aya_suit':
						if($damage <= 0){
							continue;
						}
						if($buff['param']['quantity'] >= 5){
							$GLOBALS['g']->insert_news('aya_ridicule', array('attacker' => $this->attacker->name, 'defender' => $this->defender->name));
							$this->defender->buff('ridicule', 60);
							$this->feedback($this->attacker->name.' 写下了新闻嘲讽 '.$this->defender->name);
						}
						break;
					
					//爱丽丝套两件效果 爱丽丝套五件效果
					case 'alice_suit':
						if($buff['param']['quantity'] >= 5){
							//生成武器
							$weapon = array(
								'n' => '爱丽丝·上海人形',
								'k' => $this->attacker->equipment['wep']['k'],
								'e' => 25,
								's' => 2,
								'sk' => array('multistage' => array(0.5))
								);
							$this->feedback($this->attacker->name.' 释放了「上海人形」');
							$damage += $this->bonus_attack($weapon);
							//待机状态，身代效果
							$this->attacker->buff('shield', 10, array('effect' => 100));
							$this->attacker->notice('「上海人形」进入了待机状态');
						}else if($buff['param']['quantity'] >= 2){
							if($GLOBALS['g']->determine(50)){
								//生成武器
								$weapon = array(
									'n' => '爱丽丝·上海人形',
									'k' => $this->attacker->equipment['wep']['k'],
									'e' => 25,
									's' => 2,
									'sk' => array('multistage' => array(0.5))
									);
								$this->feedback($this->attacker->name.' 释放了「上海人形」');
								$damage += $this->bonus_attack($weapon);
							}
						}
						break;
					
					default:
						break;
				}
			}
		}
		
		foreach($this->defender->data['buff'] as $key => &$buff){
			
		}
		
		return $damage;
	}
	
	protected function bonus_attack($weapon){
		//换上武器
		$current_weapon = $this->attacker->equipment['wep'];
		$this->attacker->data['equipment']['wep'] = $weapon;
		$this->attacker->calculate_battle_info(false);
		
		//攻击
		$bonus_attack = new_combat($this->attacker, $this->defender);
		$damage = $bonus_attack->attack_without_counter(true);
		
		//换回来
		$this->attacker->data['equipment']['wep'] = $current_weapon;
		$this->attacker->calculate_battle_info(false);
		
		return $damage;
	}
	
	protected function buff()
	{
		$attacker = $this->attacker;
		$defender = $this->defender;
		
		//淬毒
		if(isset($attacker->equipment['wep']['sk']['poison'])){
			global $poison;
			//持续时间与武器攻击力正相关
			$defender->buff('poison',
				min(intval($poison['Wlast'] * intval($attacker->equipment['wep']['e']) * (isset($this->skill['Glutton']) ? 2 : 1)), $poison['Wlast_min']));
			if($attacker->data['equipment']['wep']['sk']['poison'] !== 0){
				$attacker->data['equipment']['wep']['sk']['poison'] --;
				if($attacker->equipment['wep']['sk']['poison'] <= 0){
					unset($attacker->data['equipment']['wep']['sk']['poison']);
					$attacker->feedback($attacker->equipment['wep']['n'].'的不再有淬毒效果了');
				}
			}
			$attacker->feedback($defender->name.'中毒了');
		}
	}
	
	protected function damage($effect)
	{
		foreach($this->defender->buff as &$buff){
			switch($buff['type']){
				case 'konpaku_suit':
					//魂魄套两件效果 魂魄套五件效果
					if($buff['param']['quantity'] >= 2){
						if($GLOBALS['g']->determine(($buff['param']['quantity'] >= 5) ? 10 : 5)){
							$this->feedback($this->defender->name.' 发动了「反射下界斩」，对 '.$this->attacker->name.' 反弹了 '.(intval($effect * 10) / 10).' 点伤害');
							$this->attacker->damage($effect , array('pid' => $this->defender->_id, 'weapon' => $this->attacker->equipment['wep']['n'].'（反射下界斩）', 'type' => 'weapon_'.$this->type));
							
							//处理死亡
							if(false === $this->attacker->is_alive()){
								$this->defender->update_enemy_info($this->attacer, false);
							}
							return 0;
						}						
					}
					break;
				
				default:
					break;
			}
		}
		
		if(in_array('Thron', $this->defender->skill)){
			//荆棘光环
			$this->attacker->damage($effect / 10 , array('pid' => $this->defender->_id, 'type' => 'thron'));
			$this->feedback($this->defender->name.' 对 '.$this->attacker->name.' 造成了 '.(intval($effect) / 10).' 点荆棘伤害');
			//处理死亡
			if(false === $this->attacker->is_alive()){
				$this->defender->update_enemy_info($this->attacer, false);
			}
		}
		
		//刚毅 偏转高额伤害
		if(in_array('Immortal', $this->defender->skill)){
			if($effect > 100){
				$effect = 100;
			}
		}
		
		//铁壁 抵消固额伤害
		if(in_array('Rampart', $this->defender->skill)){
			$effect -= 100;
			if($effect <= 0){
				$effect = 0;
			}
		}
		
		$damage = parent::damage($effect);
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				case 'scarlet_suit':
					//斯卡雷特套四件效果
					if($buff['param']['quantity'] >= 4){
						$heal = $this->attacker->heal('hp', $damage * 0.1);
						$this->attacker->feedback('偷取了 '.(intval($heal * 10) / 10).' 点生命');						
					}
					break;
				
				default:
					break;
			}
		}
		
		//浓雾
		if($GLOBALS['g']->gameinfo['weather'] == 11){
			$heal = $this->attacker->heal('hp', $damage * 0.1);
		}
		
		return $damage;
	}
	
	protected function get_multiple()
	{
		$multiple = parent::get_multiple();
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				case 'ultrashort_EEG':
					$sub_multiple = $multiple;
					foreach($sub_multiple as &$attack_coefficient){
						$attack_coefficient *= 0.4;
					}
					$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
					
					$this->feedback($this->attacker->name.' 发动了 短視「超短脳波」 ，'.$this->defender->name.' 受到了来自幻影的攻击');
					break;
				
				//铃仙套五件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 5){
						$sub_multiple = $multiple;
						foreach($sub_multiple as &$attack_coefficient){
							$attack_coefficient *= 0.25;
						}
						$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
						$this->feedback($this->defender->name.' 受到了来自 '.$this->attacker->name.' 幻影的攻击');
					}
					break;
				
				//琪露诺套两件效果
				case 'cirno_suit':
					if($buff['param']['quantity'] >= 2 && $this->kind == 'c'){
						$sub_multiple = $multiple;
						foreach($sub_multiple as &$attack_coefficient){
							$attack_coefficient *= 0.05;
						}
						$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
						$this->feedback($this->attacker->equipment['wep']['n'].' 裹着一层冰， '.$this->defender->name.' 受到了冰渣的攻击');
					}
					break;
				
				//魂魄套五件效果
				case 'konpaku_suit':
					if($buff['param']['quantity'] >= 5){
						if($this->attacker->equipment['wep']['n'] == '魂魄对剑「白楼观」'){
							$sub_multiple = array();
							for($i = 0; $i < sizeof($multiple) / 2; $i ++){
								$sub_multiple[] = ($multiple[$i * 2] + $multiple[$i * 2 + 1]) * 0.25;
							}
							$multiple = array_merge($multiple, $sub_multiple);
						}
					}
					break;
				
				//魔理沙套四件效果
				case 'marisa_suit':
					if($buff['param']['quantity'] >= 4){
						if(false !== strpos($this->attacker->equipment['wep']['n'], '八卦炉') && $this->kind != 'c'){
							$sub_multiple = $multiple;
							foreach($sub_multiple as &$attack_coefficient){
								$attack_coefficient *= 0.5;
							}
							$multiple = array_merge($multiple, $sub_multiple);
						}
					}
					break;
				
				//十六夜套四件效果
				case 'sakuya_suit':
					if($buff['param']['quantity'] >= 4){
						if($this->is_critical_hit()){
							$this->feedback($this->attacker->name.' 发动了 時符「プライベートスクウェア」');
							$sub_multiple = $multiple;
							foreach($sub_multiple as &$attack_coefficient){
								$attack_coefficient *= 0.6;
							}
							$multiple = array_merge($sub_multiple, $sub_multiple, $sub_multiple, $sub_multiple);
						}
					}
					break;
					
				//斯卡雷特套血月效果
				case 'scarlet_suit':
					if($GLOBALS['g']->gameinfo['weather'] == 13){
						$ori_multiple = $multiple;
						while($GLOBALS['g']->determine(100 - 100 / sqrt($buff['param']['quantity']))){
							$this->feedback($this->attacker->name.' 连击！');
							$multiple = array_merge($multiple, $ori_multiple);
						}
					}
					break;
				
				default:
					break;
			}
		}
		
		return $multiple;
	}
	
	protected function get_hitrate()
	{
		if(isset($this->attacker->equipment['wep']['sk']['accurate'])){
			return 100;
		}
		
		if(isset($this->attacker->equipment['wep']['sk']['range-missile'])){
			switch($this->defender->equipment['wep']['k']){
				case 'WC':
				case 'WG':
				case 'WD':
					return 100;
					break;
					
				default:
					return 0;
					break;
			}
		}
		
		$hitrate = parent::get_hitrate();
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//铃仙套三件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 3){
						if($this->kind == 'g'){
							$hitrate *= 1.3;
						}
					}
					break;
				
				//斯卡雷特套血月效果
				case 'scarlet_suit':
					if($GLOBALS['g']->gameinfo['weather'] == 13){
						$hitrate *= sqrt($buff['param']['quantity']);
					}
					break;
				
				default:
					break;
			}
		}
		
		foreach($this->defender->buff as &$buff){
			switch($buff['type']){
				case 'infrared_moon':
					if($this->kind == 'g'){
						$this->feedback($this->defender->name.' 发动了 長視「赤月下」 ，'.$this->attacker->name.' 的枪械攻击被无效化了');
						return 0;
					}else if($this->kind == 'c'){
						$this->feedback($this->defender->name.' 发动了 長視「赤月下」 ，'.$this->attacker->name.' 的投掷攻击被无效化了');
						return 0;
					}else if($this->kind == 'd'){
						$this->feedback($this->defender->name.' 发动了 長視「赤月下」 ，'.$this->attacker->name.' 的爆炸攻击被无效化了');
						return 0;
					}else if($this->kind == 'p' && $GLOBALS['g']->gameinfo['weather'] == 10){
						$this->feedback($this->defender->name.' 发动了 長視「赤月下」 ，'.$this->attacker->name.' 的钝击攻击被无效化了');
						return 0;
					}else if($this->kind == 'k' && $GLOBALS['g']->gameinfo['weather'] == 10){
						$this->feedback($this->defender->name.' 发动了 長視「赤月下」 ，'.$this->attacker->name.' 的斩切攻击被无效化了');
						return 0;
					}
					break;
				
				//铃仙套两件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 2){
						$hitrate *= 0.9;
					}
				
				//文套三件效果
				case 'aya_suit':
					if($buff['param']['quantity'] >= 3){
						$hitrate *= 0.85;
					}
					break;
				
				default:
					break;
			}
		}
		
		return $hitrate;
	}
	
	protected function get_counter_rate()
	{
		$rate = parent::get_counter_rate();
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//冴月麟套两件效果
				case 'rin_suit':
					if($buff['param']['quantity'] >= 2){
						$rate *= 0.5;
					}
				
				default:
					break;
			}
		}
		
		foreach($this->defender->buff as &$buff){
			switch($buff['type']){
				//铃仙套两件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 2){
						$rate *= 1.1;
					}
				
				default:
					break;
			}
		}
		
		return $rate;
	}
	
	protected function get_hurt_rate()
	{
		$rate = parent::get_hurt_rate();
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//文套三件效果
				case 'aya_suit':
					if($buff['param']['quantity'] >= 3){
						$rate *= 1.5;
					}
				
				default:
					break;
			}
		}
		
		return $rate;
	}
	
	protected function calc_damage($ma_modulus)
	{
		//花昙
		if($this->kind == 'sc' && $GLOBALS['g']->gameinfo['weather'] == 10){
			return 0;
		}
		
		//盾牌效果
		if(isset($this->defender->equipment['ara']['sk']['shield'])){
			if($GLOBALS['g']->determine($this->defender->equipment['ara']['sk']['shield'])){
				$this->feedback($this->defender->name.' 的 '.$this->defender->equipment['ara']['n'].' 发动格挡，没有造成伤害');
				return 0;
			}
		}
		
		foreach($this->defender->buff as &$buff){
			switch($buff['type']){
				//灵梦套五件效果
				case 'reimu_suit':
					if($buff['param']['quantity'] >= 5){
						if($GLOBALS['g']->determine(25)){
							$this->feedback($this->defender->name.' 使用了 阴阳玉 抵消了本次伤害');
							return 0;
						}
					}
					break;
				
				default:
					break;
			}
		}
		
		$damage = parent::calc_damage($ma_modulus);
		$modulus = 1;
		
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//魂魄套四件效果
				case 'konpaku_suit':
					if($buff['param']['quantity'] >= 4 && $this->kind == 'k'){
						$modulus *= 1.35;
					}
					break;
					
				//魔理沙套三件效果
				case 'konpaku_suit':
					if($buff['param']['quantity'] >= 3 && $this->kind == 'p'){
						$modulus *= 1.3;
					}
					break;
					
				//爱丽丝套四件效果
				case 'alice_suit':
					if($buff['param']['quantity'] >= 4 && $this->kind == 'd'){
						$modulus *= 1.4;
					}
					break;
					
				//上白泽套三件效果
				case 'keine_suit':
					if($buff['param']['quantity'] >= 3 && $this->kind == 'c'){
						$modulus *= 1.3;
					}
					break;
				
				default:
					break;
			}
		}
		
		//浮动伤害
		$damage *= $modulus * $this->calc_damage_float();
		
		return $damage;
	}
	
	protected function calc_damage_float()
	{
		return pow(2, $GLOBALS['g']->random(-10000, 10000) / 10000);
	}
	
	protected function modulus_critical_hit()
	{
		foreach($this->attacker->buff as &$buff){
			switch($buff['type']){
				//十六夜套四件效果
				case 'sakuya_suit':
					if($buff['param']['quantity'] >= 4){
						return 1;
					}
					break;
				
				//幽幽子套五件效果
				case 'yuyuko_suit':
					if($buff['param']['quantity'] >= 5){
						$modulus = 1;
						
						if($this->is_critical_hit()){
							if($this->attacker->club == 9){
								$this->feedback($this->attacker->name.' 使出了必杀技！');
								$modulus *= 1.8;
							}else{
								$this->feedback($this->attacker->name.' 会心一击！');
								$modulus *= 1.4;
							}
							$this->feedback($this->attacker->name.' 发动 寿命「无寿国への約束手形」');
							$this->defender->buff('ageless_land', 7, array('source' => $this->attacker->_id));
						}else{
							$this->feedback($this->attacker->name.' 发动 霊符「无寿の夢」');
							$this->defender->buff('ageless_dream', 7, array('source' => $this->attacker->_id));
						}
						
						return $modulus;
					}
					break;
				
				default:
					break;
			}
		}
		
		return parent::modulus_critical_hit();
	}
	
	protected function gain_proficiency()
	{
		if(!$this->extra_attack){ //额外攻击不增加熟练（如灵梦套的阴阳玉）
			if($this->kind == 'sc'){
				$this->attacker->data['proficiency']['d'] += 1;
				$this->attacker->ajax('proficiency', array('proficiency' => $this->attacker->proficiency));
			}else{
				parent::gain_proficiency();
			}
		}
	}
	
	protected function attrit($position)
	{
		parent::attrit($position);
		
		//钻石星辰
		if($GLOBALS['g']->gameinfo['weather'] == 12){
			$this->defender->damage(125, array('type' => 'diamond'));
		}
	}
	
	protected function injure($position)
	{
		parent::injure($position);
		
		//钻石星辰
		if($GLOBALS['g']->gameinfo['weather'] == 12){
			$this->defender->damage(175, array('type' => 'diamond'));
		}
	}
	
}

?>