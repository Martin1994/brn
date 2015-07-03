<?php

/**
 * Class combat_thbr
 *
 * @property player_thbr $attacker 先手发动攻击的玩家，在战斗引擎中并不一定总是攻击者（例如反击时）
 * @property player_thbr $defender 后手发动攻击的玩家，在战斗引擎中并不一定总是防御者（例如反击时）
 */
class combat_thbr extends combat_bra
{
	
	public function __construct(player_thbr $att, player_thbr $def)
	{
		parent::__construct($att, $def);
	}

	public function weapon_kind(player $attacker)
	{
		if($attacker->equipment['wep']['k'] == 'SW'){
			return 'sc';
		}else{
			return parent::weapon_kind($attacker);
		}
	}

	public function battle_start()
	{
		global $g;

		$attacker = $this->attacker;
		$defender = $this->defender;

		$has_countered = false;

		do {
			if (isset($defender->equipment['arb']['sk']['anti-' . $this->weapon_kind($attacker)])) {
				$this->feedback($defender->name . '的' . $defender->equipment['n'] . '展现出了惊人的抗性');
			}

			$this->attack_round($attacker, $defender);

			//判定反击
			//默认不允许反击反击
			$counter = !$has_countered;

			foreach($defender->buff as $key => &$buff){
				switch($buff['type']){
					case 'komeiji_suit':
						if($buff['param']['quantity'] >= 5){
							//古明地套装五件效果
							$counter = true;
						}
						break;
				}
			}

			$counter = $counter && $g->determine(intval($this->get_counter_rate($attacker, $defender)));

			if ($counter) {
				$this->feedback($defender->name . '发起反击');
				$has_countered = true;
				$temp = $attacker;
				$attacker = $defender;
				$defender = $temp;
			} else {
				$this->feedback($defender->name . '无法反击，逃跑了');
				$this->gain_experience($defender, $attacker);
			}
		}while($counter);

		//反击致死
		if (false === $this->attacker->is_alive()) {
			if ((false === isset($this->defender->data['action']['battle']))
				&& (intval($this->defender->type) === GAME_PLAYER_USER)
			) {
				$this->defender->found_enemy($this->attacker);
			}
		}

		$this->feedback('战斗结束');
	}
	
	public function attack(player $attacker, player $defender, $extra = false)
	{
		global $g;

		if($attacker->equipment['wep']['k'] == 'SW'){
			$this->feedback($attacker->name.' 发动了 '.$attacker->equipment['wep']['n']);
		}

		$attacker->data['proficiency']['sc'] = floor(($attacker->proficiency['d'] + max($attacker->proficiency['p'], $attacker->proficiency['k'], $attacker->proficiency['g'], $attacker->proficiency['c'], $attacker->proficiency['d'])) / 2);
		
		$damage = 0;
		
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				//冴月麟套四件效果
				case 'rin_suit':
					if($buff['param']['quantity'] >= 4){
						if($g->determine(30)){
							$this->feedback($attacker->name.' 发动了 风符「共振激励」 造成 '.$defender->name.' 当前生命值 25% 的伤害');
							$damage += $this->damage($attacker, $defender, $defender->hp * 0.25);
						}
					}
					break;
				
				default:
					break;
			}
		}
		
		$damage += parent::attack($attacker, $defender);
		
		if(!$extra && $defender->is_alive()){
			foreach($attacker->equipment['wep']['sk'] as $key => $value){
				switch($key){
					case 'lunar-incense':
						if($damage > 0){
							//仙香玉兔效果
							$defender->buff('lunar_incense', 1800, array('killer' => $attacker->_id)); //半小时后死亡
							$this->feedback($defender->name.'被 秘薬「仙香玉兎」 击中了，出现了幻觉，意志力正在慢慢被消耗');
						}
						break;
					
					default:
						break;
				}
			}
			
			foreach($defender->data['buff'] as $key => &$buff){
				switch($buff['type']){
					//打断回复
					case 'recover_hp':
					case 'recover_sp':
						if($damage <= 0){
							continue;
						}
						if($buff['param']['interrupt']){
							$defender->remove_buff($key);
							$this->feedback($defender->name.' 的回复增益被打断了');
						}
						break;
					
					default:
						break;
				}
			}
			
			foreach($attacker->data['buff'] as $key => &$buff){
				switch($buff['type']){
					//大小无寿
					case 'ageless_dream':
						if($damage <= 0){
							continue;
						}
						$attacker->remove_buff($key);
						$this->feedback('霊符「无寿の夢」 的效果消失了');
						break;
					
					case 'ageless_land':
						if($damage <= 0){
							continue;
						}
						$attacker->remove_buff($key);
						$this->feedback('寿命「无寿国への約束手形」 的效果消失了');
						break;
					
					//梦想天生
					case 'fantasy_nature':
						if($damage <= 0){
							continue;
						}
						$attacker->feedback('为 「夢想天生」 的发动积聚了能量！');
						$buff['param']['hits'] ++;
						if($buff['param']['hits'] >= 7){
							$attacker->feedback('「夢想天生」发动！');
							$attacker->remove_buff($key);
							
							//生成武器
							$weapon = array(
								'n' => '「夢想天生」',
								'k' => $attacker->equipment['wep']['k'],
								'e' => 1000,
								's' => 2,
								'sk' => array('accurate' => true)
								);
							
							$damage += $this->bonus_attack($attacker, $defender, $weapon);
							
						}
						break;
					
					//灵梦套两件效果
					case 'reimu_suit':
						if($buff['param']['quantity'] >= 2){
							if($g->determine(25)){
								//生成武器
								$weapon = array(
									'n' => '珠符「明珠暗投」',
									'k' => $attacker->equipment['wep']['k'],
									'e' => 40,
									's' => 2,
									'sk' => array()
									);
								$this->feedback($attacker->name.' 发动了 珠符「明珠暗投」');
								$damage += $this->bonus_attack($attacker, $defender, $weapon);
							}
						}
						break;
					
					//文套五件效果
					case 'aya_suit':
						if($damage <= 0){
							continue;
						}
						if($buff['param']['quantity'] >= 5){
							$g->insert_news('aya_ridicule', array('attacker' => $attacker->name, 'defender' => $defender->name));
							$defender->buff('ridicule', 60);
							$this->feedback($attacker->name.' 写下了新闻嘲讽 '.$defender->name);
						}
						break;
					
					//爱丽丝套两件效果 爱丽丝套五件效果
					case 'alice_suit':
						if($buff['param']['quantity'] >= 5){
							//生成武器
							$weapon = array(
								'n' => '爱丽丝·上海人形',
								'k' => $attacker->equipment['wep']['k'],
								'e' => 25,
								's' => 2,
								'sk' => array('multistage' => array(0.5))
								);
							$this->feedback($attacker->name.' 释放了「上海人形」');
							$damage += $this->bonus_attack($attacker, $defender, $weapon);
							//待机状态，身代效果
							$attacker->buff('shield', 10, array('amount' => 100, 'effect' => 0.9));
							$attacker->notice('「上海人形」进入了待机状态');
						}else if($buff['param']['quantity'] >= 2){
							if($g->determine(50)){
								//生成武器
								$weapon = array(
									'n' => '爱丽丝·上海人形',
									'k' => $attacker->equipment['wep']['k'],
									'e' => 25,
									's' => 2,
									'sk' => array('multistage' => array(0.5))
									);
								$this->feedback($attacker->name.' 释放了「上海人形」');
								$damage += $this->bonus_attack($attacker, $defender, $weapon);
							}
						}
						break;
					
					default:
						break;
				}
			}
		}
		
		foreach($defender->data['buff'] as $key => &$buff){
			
		}
		
		return $damage;
	}
	
	protected function bonus_attack(player $attacker, player $defender, $weapon){
		//将武器标记成 extra-attack
		$attacker->data['equipment']['wep']['sk']['extra-attack'] = true;

		//换上武器
		$current_weapon = $attacker->equipment['wep'];
		$attacker->data['equipment']['wep'] = $weapon;
		$attacker->calculate_battle_info(false);
		
		//攻击
		$damage = $this->attack($attacker, $defender, true);

		//撤下武器的 extra-attack 标签
		unset($attacker->data['equipment']['wep']['sk']['extra-attack']);

		//换回来
		$attacker->data['equipment']['wep'] = $current_weapon;
		$attacker->calculate_battle_info(false);
		
		return $damage;
	}
	
	protected function buff(player $attacker, player $defender)
	{
		//淬毒
		if(isset($attacker->equipment['wep']['sk']['poison'])){
			global $poison;
			//持续时间与武器攻击力正相关
			$defender->buff('poison',
				min(intval($poison['Wlast'] * intval($attacker->equipment['wep']['e']) * (isset($attacker->skill['Glutton']) ? 2 : 1)), $poison['Wlast_min']));
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
	
	protected function damage(player $attacker, player $defender, $effect)
	{
		global $g;

		foreach($defender->buff as &$buff){
			switch($buff['type']){
				case 'konpaku_suit':
					//魂魄套两件效果 魂魄套五件效果
					if($buff['param']['quantity'] >= 2){
						if($g->determine(($buff['param']['quantity'] >= 5) ? 10 : 5)){
							$this->feedback($defender->name.' 发动了「反射下界斩」，对 '.$attacker->name.' 反弹了 '.(intval($effect * 10) / 10).' 点伤害');
							$attacker->damage($effect , array('pid' => $defender->_id, 'weapon' => $attacker->equipment['wep']['n'].'（反射下界斩）', 'type' => 'weapon_'.$this->weapon_kind($attacker)));
							
							//处理死亡
							if(false === $attacker->is_alive()){
								$defender->update_enemy_info($attacker, false);
							}
							return 0;
						}						
					}
					break;
				
				default:
					break;
			}
		}
		
		if(in_array('Thron', $defender->skill)){
			//荆棘光环
			$attacker->damage($effect / 10 , array('pid' => $defender->_id, 'type' => 'thron'));
			$this->feedback($defender->name.' 对 '.$attacker->name.' 造成了 '.(intval($effect) / 10).' 点荆棘伤害');
			//处理死亡
			if(false === $attacker->is_alive()){
				$defender->update_enemy_info($attacker, false);
			}
		}
		
		//刚毅 偏转高额伤害
		if(in_array('Immortal', $defender->skill)){
			if($effect > 100){
				$effect = 100;
			}
		}
		
		//铁壁 抵消固额伤害
		if(in_array('Rampart', $defender->skill)){
			$effect -= 100;
			if($effect <= 0){
				$effect = 0;
			}
		}
		
		$damage = parent::damage($attacker, $defender, $effect);
		
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				case 'scarlet_suit':
					//斯卡雷特套四件效果
					if($buff['param']['quantity'] >= 4){
						$heal = $attacker->heal('hp', $damage * 0.1);
						$attacker->feedback('偷取了 '.(intval($heal * 10) / 10).' 点生命');
					}
					break;
				
				default:
					break;
			}
		}
		
		//浓雾 吸血效果
		if($g->gameinfo['weather'] == 11){
			$attacker->heal('hp', $damage * 0.1);
		}
		
		return $damage;
	}
	
	protected function get_multiple(player $attacker, player $defender)
	{
		global $g;

		$multiple = parent::get_multiple($attacker, $defender);
		
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				case 'ultrashort_EEG':
					$sub_multiple = $multiple;
					foreach($sub_multiple as &$attack_coefficient){
						$attack_coefficient *= 0.4;
					}
					$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
					
					$this->feedback($attacker->name.' 发动了 短視「超短脳波」 ，'.$defender->name.' 受到了来自幻影的攻击');
					break;
				
				//铃仙套五件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 5){
						$sub_multiple = $multiple;
						foreach($sub_multiple as &$attack_coefficient){
							$attack_coefficient *= 0.25;
						}
						$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
						$this->feedback($defender->name.' 受到了来自 '.$attacker->name.' 幻影的攻击');
					}
					break;
				
				//琪露诺套两件效果
				case 'cirno_suit':
					if($buff['param']['quantity'] >= 2 && $this->weapon_kind($attacker) == 'c'){
						$sub_multiple = $multiple;
						foreach($sub_multiple as &$attack_coefficient){
							$attack_coefficient *= 0.05;
						}
						$multiple = array_merge($multiple, $sub_multiple, $sub_multiple);
						$this->feedback($attacker->equipment['wep']['n'].' 裹着一层冰， '.$defender->name.' 受到了冰渣的攻击');
					}
					break;
				
				//魔理沙套四件效果
				case 'marisa_suit':
					if($buff['param']['quantity'] >= 4){
						if(false !== strpos($attacker->equipment['wep']['n'], '八卦炉') && $this->weapon_kind($attacker) != 'c'){
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
						if($this->is_critical_hit($attacker, $defender)){
							$this->feedback($attacker->name.' 发动了 時符「プライベートスクウェア」');
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
					if($g->gameinfo['weather'] == 13){
						$ori_multiple = $multiple;
						while($g->determine(100 - 100 / sqrt($buff['param']['quantity']))){
							$this->feedback($attacker->name.' 连击！');
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

	protected function get_multistage(player $attacker, player $defender)
	{

		global $g;

		$multiple = parent::get_multistage($attacker, $defender);

		foreach ($attacker->buff as &$buff) {
			switch ($buff['type']) {
				//魂魄套五件效果
				case 'konpaku_suit':
					if ($buff['param']['quantity'] >= 5) {
						if ($attacker->equipment['wep']['n'] == '魂魄对剑「白楼观」') {
							$sub_multiple = array();
							for ($i = 0; $i < sizeof($multiple) / 2; $i++) {
								$sub_multiple[] = ($multiple[$i * 2] + $multiple[$i * 2 + 1]) * 0.25;
							}
							$multiple = array_merge($multiple, $sub_multiple);
						}
					}
					break;
			}
		}

		return $multiple;
	}
	
	protected function get_hitrate(player $attacker, player $defender)
	{
		if(isset($attacker->equipment['wep']['sk']['accurate'])){
			return 100;
		}
		
		if(isset($attacker->equipment['wep']['sk']['range-missile'])){
			switch($defender->equipment['wep']['k']){
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
		
		$hitrate = parent::get_hitrate($attacker, $defender);

		$kind = $this->weapon_kind($attacker);
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				//铃仙套三件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 3){
						if($kind == 'g'){
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

				//古明地三件效果
				case 'komeiji_suit':
					if($buff['param']['quantity'] >= 3){
						$hitrate *= 1.1;
					}
					break;
				
				default:
					break;
			}
		}
		
		foreach($defender->buff as &$buff){
			switch($buff['type']){
				case 'infrared_moon':
					$kind = $this->weapon_kind($attacker);
					if($kind == 'g'){
						$this->feedback($defender->name.' 发动了 長視「赤月下」 ，'.$attacker->name.' 的枪械攻击被无效化了');
						return 0;
					}else if($kind == 'c'){
						$this->feedback($defender->name.' 发动了 長視「赤月下」 ，'.$attacker->name.' 的投掷攻击被无效化了');
						return 0;
					}else if($kind == 'd'){
						$this->feedback($defender->name.' 发动了 長視「赤月下」 ，'.$attacker->name.' 的爆炸攻击被无效化了');
						return 0;
					}else if($kind == 'p' && $GLOBALS['g']->gameinfo['weather'] == 10){
						$this->feedback($defender->name.' 发动了 長視「赤月下」 ，'.$attacker->name.' 的钝击攻击被无效化了');
						return 0;
					}else if($kind == 'k' && $GLOBALS['g']->gameinfo['weather'] == 10){
						$this->feedback($defender->name.' 发动了 長視「赤月下」 ，'.$attacker->name.' 的斩切攻击被无效化了');
						return 0;
					}
					break;
				
				//铃仙套两件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 2){
						$hitrate *= 0.9;
					}
					break;
				
				//文套三件效果
				case 'aya_suit':
					if($buff['param']['quantity'] >= 3){
						$hitrate *= 0.85;
					}
					break;

				//古明地三件效果
				case 'komeiji_suit':
					if($buff['param']['quantity'] >= 3){
						$hitrate *= 0.9;
					}
					break;
				
				default:
					break;
			}
		}
		
		return $hitrate;
	}
	
	protected function get_counter_rate(player $attacker, player $defender)
	{
		$rate = parent::get_counter_rate($attacker, $defender);
		
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				//冴月麟套两件效果
				case 'rin_suit':
					if($buff['param']['quantity'] >= 2){
						$rate *= 0.5;
					}
					break;
				
				default:
					break;
			}
		}
		
		foreach($defender->buff as &$buff){
			switch($buff['type']){
				//铃仙套两件效果
				case 'reisen_suit':
					if($buff['param']['quantity'] >= 2){
						$rate *= 1.1;
					}
					break;
				
				default:
					break;
			}
		}

		if(isset($defender->equipment['wep']['sk']['be-countered-buff'])){
			$rate *= $defender->equipment['wep']['sk']['be-countered-buff'];
		} //TODO: 帮助文件添加饰品的说明
		
		return $rate;
	}
	
	protected function get_hurt_rate(player $attacker, player $defender)
	{
		$rate = parent::get_hurt_rate($attacker, $defender);
		
		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				//文套三件效果
				case 'aya_suit':
					if($buff['param']['quantity'] >= 3){
						$rate *= 1.5;
					}
					break;
				
				default:
					break;
			}
		}
		
		return $rate;
	}

	/**
	 * @param player_thbr $attacker
	 * @param player_thbr $defender
	 * @param $ma_modulus
	 * @return float|int
	 */
	protected function calc_damage(player $attacker, player $defender, $ma_modulus)
	{
		global $g;

		//花昙
		if($this->weapon_kind($attacker) == 'sc' && $GLOBALS['g']->gameinfo['weather'] == 10){
			return 0;
		}
		
		//盾牌效果
		if(isset($defender->equipment['ara']['sk']['shield'])){
			if($g->determine($defender->equipment['ara']['sk']['shield'])){
				$this->feedback($defender->name.' 的 '.$defender->equipment['ara']['n'].' 发动格挡');
				return 0;
			}
		}
		
		foreach($defender->buff as &$buff){
			switch($buff['type']){
				//灵梦套五件效果
				case 'reimu_suit':
					if($buff['param']['quantity'] >= 5){
						if($g->determine(25)){
							$this->feedback($defender->name.' 使用了 阴阳玉 抵消了本次伤害');
							return 0;
						}
					}
					break;
				
				default:
					break;
			}
		}
		
		$damage = parent::calc_damage($attacker, $defender, $ma_modulus);
		$modulus = 1;

		$kind = $this->weapon_kind($attacker);

		foreach($attacker->buff as &$buff){
			switch($buff['type']){
				//魂魄套四件效果
				case 'konpaku_suit':
					if($buff['param']['quantity'] >= 4 && $kind == 'k'){
						$modulus *= 1.35;
					}
					break;
					
				//魔理沙套三件效果
				case 'marisa_suit':
					if($buff['param']['quantity'] >= 3 && $kind == 'p'){
						$modulus *= 1.3;
					}
					break;
					
				//爱丽丝套四件效果
				case 'alice_suit':
					if($buff['param']['quantity'] >= 4 && $kind == 'd'){
						$modulus *= 1.4;
					}
					break;
					
				//上白泽套三件效果
				case 'keine_suit':
					if($buff['param']['quantity'] >= 3 && $kind == 'c'){
						$modulus *= 1.3;
					}
					break;
				
				default:
					break;
			}
		}
		
		//浮动伤害
		$damage *= $modulus * $this->calc_damage_float($attacker, $defender);
		
		return $damage;
	}
	
	protected function calc_damage_float(player $attacker, player $defender)
	{
		global $g;
		$stdev = 0.2;
		switch($this->weapon_kind($attacker)){
			case 'k':
				$stdev = 0.8;
				break;

			case 'g':
				$stdev = 0.1;
				break;

			case 'd':
				$stdev = 0;
				break;

			case 'sc':
				$stdev = 0;
				break;
		}

		return $g->nb_random(1, $stdev, true);
	}
	
	protected function modulus_critical_hit(player $attacker, player $defender)
	{
		foreach($attacker->buff as &$buff){
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
						
						if($this->is_critical_hit($attacker, $defender)){
							if($attacker->club == 9){
								$this->feedback($attacker->name.' 使出了必杀技！');
								$modulus *= 1.8;
							}else{
								$this->feedback($attacker->name.' 会心一击！');
								$modulus *= 1.4;
							}
							$this->feedback($attacker->name.' 发动 寿命「无寿国への約束手形」');
							$defender->buff('ageless_land', 7, array('source' => $attacker->_id));
						}else{
							$this->feedback($attacker->name.' 发动 霊符「无寿の夢」');
							$defender->buff('ageless_dream', 7, array('source' => $attacker->_id));
						}
						
						return $modulus;
					}
					break;
				
				default:
					break;
			}
		}
		
		return parent::modulus_critical_hit($attacker, $defender);
	}
	
	protected function gain_proficiency(player $attacker, player $defender)
	{
		$extra = isset($attacker->equipment['wep']['sk']['extra-attack']);
		$kind = $this->weapon_kind($attacker);
		if(!$extra){ //额外攻击不增加熟练（如灵梦套的阴阳玉）
			if($kind == 'sc'){
				$attacker->data['proficiency']['d'] += 1;
				$attacker->ajax('proficiency', array('proficiency' => $attacker->proficiency));
			}else{
				parent::gain_proficiency($attacker, $defender);
			}
		}
	}
	
	protected function attrit(player $attacker, player $defender, $position)
	{
		parent::attrit($attacker, $defender, $position);
		
		//钻石星辰
		if($GLOBALS['g']->gameinfo['weather'] == 12){
			$damage = $defender->damage(125, array('type' => 'diamond'));

			$this->feedback($defender->name . '的伤口被钻石星辰撕裂了，造成了' . $damage . '点伤害');

			if(!isset($this->hurts[$defender->_id])){
				$this->hurts[$defender->_id] = 0;
			}
			$this->hurts[$defender->_id] += $damage;
		}
	}
	
	protected function injure(player $attacker, player $defender, $position)
	{
		parent::injure($attacker, $defender, $position);
		
		//钻石星辰
		if($GLOBALS['g']->gameinfo['weather'] == 12){
			$damage = $defender->damage(175, array('type' => 'diamond'));

			$this->feedback($defender->name . '的伤口被钻石星辰撕裂了，造成了' . $damage . '点伤害');

			if(!isset($this->hurts[$defender->_id])){
				$this->hurts[$defender->_id] = 0;
			}
			$this->hurts[$defender->_id] += $damage;
		}
	}

	/**
	 * 消耗武器耐久，增加sc判断
	 * @param player $attacker
	 * @param player $defender
	 * @param $hitted
	 */
	protected function weapon_consume(player $attacker, player $defender, $hitted)
	{
		switch($this->weapon_kind($attacker)){
			case 'sc':
				//只要攻击就消耗耐久
				$attacker->item_consume('wep', 0);
				break;

			default:
				parent::weapon_consume($attacker, $defender, $hitted);
				break;
		}
		return;
	}
	
}

?>