<?php


class combat
{

	/**
	 * @var player 先手发动攻击的玩家，在战斗引擎中并不一定总是攻击者（例如反击时）
	 */
	protected $attacker;
	/**
	 * @var player 后手发动攻击的玩家，在战斗引擎中并不一定总是防御者（例如反击时）
	 */
	protected $defender;
	/**
	 * @var array 战斗中各个玩家造成的总伤害
	 */
	protected $damages = array();
	/**
	 * @var array 战斗中各个玩家受到的总伤害
	 */
	protected $hurts = array();

	/**
	 * 构造函数，指定先后手玩家
	 * @param player $att
	 * @param player $def
	 */
	public function __construct(player $att, player $def)
	{
		$this->attacker = $att;
		$this->defender = $def;
		$this->damages[$this->attacker->_id] = 0;
		$this->damages[$this->defender->_id] = 0;
		$this->hurts[$this->attacker->_id] = 0;
		$this->hurts[$this->defender->_id] = 0;
	}

	/**
	 * 判断某个玩家是否正在使用拳头攻击
	 * @param player $player
	 * @return bool
	 */
	protected function is_fist(player $player)
	{
		return $player->equipment['wep']['n'] == '';
	}

	/**
	 * 获取某个玩家的武器种类（wep的k属性）
	 * @param player $player
	 * @return string
	 */
	protected function weapon_kind(player $player)
	{
		return ($player->equipment['wep']['k'] == '') ? 'p' :strtolower(substr($player->equipment['wep']['k'] , 1, 1));
	}

	/**
	 * 开始战斗
	 */
	public function battle_start()
	{
		$this->attack_round($this->attacker, $this->defender);
	}

	/**
	 * 进行一轮攻击，并记录攻击结果
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function attack_round(player $attacker, player $defender)
	{
		global $g;
		$damage = $this->attack($attacker, $defender);
		$g->record_battle_damage($damage, $attacker, $defender);
	}

	/**
	 * 获取战斗记录
	 * @return array
	 */
	public function get_battle_result()
	{
		$result = array();

		foreach($this->damages as $pid => $damage)
		{
			if(!isset($result[$pid])){
				$result[$pid] = array();
			}
			$result[$pid]['damage'] = $damage;
		}

		foreach($this->hurts as $pid => $damage)
		{
			if(!isset($result[$pid])){
				$result[$pid] = array();
			}
			$result[$pid]['hurt'] = $damage;
		}

		return $result;
	}

	/**
	 * 攻击
	 * @param player $attacker
	 * @param player $defender
	 * @return float
	 */
	protected function attack(player $attacker, player $defender)
	{
		global $g;

		$multiple_attack = $this->get_multiple($attacker, $defender);
		
		$multistage_attack = $this->get_multistage($attacker, $defender);
		
		$hitted = false;
		$buffed = false;
		$hurt = false;
		$total_damage = 0;
		
		foreach($multiple_attack as $m1_modulus){
			$hitrate = intval($this->get_hitrate($attacker, $defender));
			if($g->determine($hitrate)){
				$hitted = true;
				$multistage_total_damage = 0;
				foreach($multistage_attack as $m2_modulus){
					$damage = $this->calc_damage($attacker, $defender, $m1_modulus * $m2_modulus);
					$multistage_total_damage += $damage;
					$damage = round($damage * 10) / 10;
					$weapon = $this->is_fist($attacker) ? $GLOBALS['fist'] : $attacker->equipment['wep']['n'];

					$this->feedback($attacker->name.'使用 '.$weapon.' 攻击（'.$GLOBALS['weapon_types'][$this->weapon_kind($attacker)].'）'.$defender->name.'，造成了'.$damage.'点伤害');

					//淬毒
					if((false === $buffed) || (false === isset($attacker->equipment['wep']['sk']['single-buff']))){
						$this->buff($attacker, $defender);
						$buffed = true;
					}

					//磨甲 / 受伤
					if(false === $hurt || false === isset($attacker->equipment['wep']['sk']['single-hurt'])){
						$this->hurt($attacker, $defender);
						$hurt = true;
					}

				}

				$total_damage += $this->damage($attacker, $defender, $multistage_total_damage);

				if(false === $defender->is_alive()){
					$attacker->update_enemy_info($defender, false);
				}
			}else{
				$this->feedback($attacker->name.'没有击中'.$defender->name);
			}
		}
		if(!$this->is_fist($attacker)){
			//损耗
			$this->weapon_consume($attacker, $defender, $hitted);
			$attacker->ajax('item', array('equipment' => $attacker->parse_equipment()));
			
			//声音
			$this->make_noise($attacker, $defender);
		}
		
		//熟练
		$this->gain_proficiency($attacker, $defender);
		
		//经验
		$this->gain_experience($attacker, $defender);
		
		if($attacker->type == GAME_PLAYER_USER && ($total_damage > $GLOBALS['g']->gameinfo['hdamage'])){
			$GLOBALS['g']->renew_top_player($attacker, $total_damage);
		}
		
		return $total_damage;
	}

	/**
	 * 处理多重攻击，反复计算击中
	 * @param player $attacker
	 * @param player $defender
	 * @return array
	 */
	protected function get_multiple(player $attacker, player $defender)
	{
		if(isset($attacker->equipment['wep']['sk']['multiple'])){
			if(is_array($attacker->equipment['wep']['sk']['multiple'])){
				$multiple_attack = $attacker->equipment['wep']['sk']['multiple'];
			}else{
				$multiple_attack = array();
				$attacker->equipment['wep']['sk']['multiple'] = intval($attacker->equipment['wep']['sk']['multiple']);
				for($i = 0; $i < $attacker->equipment['wep']['sk']['multiple']; $i ++){
					$multiple_attack[] = 1 / $attacker->equipment['wep']['sk']['multiple'];
				}
			}
		}else{
			$multiple_attack = array(1);
		}
		
		return $multiple_attack;
	}


	/**
	 * 处理多段攻击，只计算一次击中
	 * @param player $attacker
	 * @param player $defender
	 * @return array
	 */
	protected function get_multistage(player $attacker, player $defender)
	{
		if(isset($attacker->equipment['wep']['sk']['multistage'])){
			if(is_array($attacker->equipment['wep']['sk']['multistage'])){
				$multistage_attack = $attacker->equipment['wep']['sk']['multistage'];
			}else{
				$multistage_attack = array();
				$attacker->equipment['wep']['sk']['multistage'] = intval($attacker->equipment['wep']['sk']['multistage']);
				for($i = 0; $i < $attacker->equipment['wep']['sk']['multistage']; $i ++){
					$multistage_attack[] = 1 / $attacker->equipment['wep']['sk']['multistage'];
				}
			}
		}else{
			$multistage_attack = array(1);
		}
		
		return $multistage_attack;
	}

	/**
	 * 造成特殊效果（中毒等）
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function buff(player $attacker, player $defender)
	{
		//淬毒
		if(isset($attacker->equipment['wep']['sk']['poison'])){
			global $poison;
			//持续时间与武器攻击力正相关
			$defender->buff('poison',
				min(intval($poison['Wlast'] * intval($attacker->equipment['wep']['e'])), $poison['Wlast_min']));
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

	/**
	 * 造成伤害并写入战斗记录
	 * @param player $attacker
	 * @param player $defender
	 * @param $effect
	 * @return float
	 */
	protected function damage(player $attacker, player $defender, $effect)
	{
		$kind = $this->weapon_kind($attacker);
		$damage = $defender->damage($effect , array('pid' => $attacker->_id, 'weapon' => $attacker->equipment['wep']['n'], 'type' => 'weapon_'.$kind));

		if(!isset($this->damages[$attacker->_id])){
			$this->damages[$attacker->_id] = 0;
		}

		$this->damages[$attacker->_id] += $damage;
		if(!isset($this->hurts[$defender->_id])){
			$this->hurts[$defender->_id] = 0;
		}
		$this->hurts[$defender->_id] += $damage;

		return $damage;
	}

	/**
	 * 处理部位损耗（致伤或护甲损耗）
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function hurt(player $attacker, player $defender)
	{
		global $hurt_position;
		
		$threshold = $this->get_hurt_rate($attacker, $defender);
		
		if($GLOBALS['g']->determine($threshold)){
			$position = $GLOBALS['g']->random(0, 3);
			switch($position){
				case 0:
					$position = 'b';
					break;
				
				case 1:
					$position = 'h';
					break;
				
				case 2:
					$position = 'a';
					break;
				
				case 3:
					$position = 'f';
					break;
			}
			
			$avalible_position = $this->is_fist($attacker) ? $hurt_position['fist'] : $hurt_position[$this->weapon_kind($attacker)];
			if(in_array($position, $avalible_position)){
				if(isset($defender->data['equipment']['ar'.$position]['n']) && $defender->data['equipment']['ar'.$position]['n'] != ''){
					$this->attrit($attacker, $defender, $position);
				}else{
					$this->injure($attacker, $defender, $position);
				}
			}
		}
	}

	/**
	 * 造成受伤（击中没有防具的部位有几率遭成受伤）
	 * @param player $attacker
	 * @param player $defender
	 * @param $position
	 */
	protected function injure(player $attacker, player $defender, $position)
	{
		$defender->damage(25, array('pid' => $attacker->_id, 'type' => 'injure'));
	}

	/**
	 * 造成护甲损耗（击中有防具的部位有几率遭成损耗）
	 * @param player $attacker
	 * @param player $defender
	 * @param $position
	 */
	protected function attrit(player $attacker, player $defender, $position)
	{
		$defender->item_consume('ar'.$position);
		$defender->notice($defender->data['equipment']['ar'.$position]['n'].'的耐久度下降了');
		$defender->ajax('item', array('equipment' => $defender->parse_equipment()));
	}

	/**
	 * 获取致伤率
	 * @param player $attacker
	 * @param player $defender
	 * @return float
	 */
	protected function get_hurt_rate(player $attacker, player $defender)
	{
		global $hurt_rate;
		return $this->is_fist($attacker) ? $hurt_rate['fist'] : $hurt_rate[$this->weapon_kind($attacker)];
	}

	/**
	 * 提升熟练度
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function gain_proficiency(player $attacker, player $defender)
	{
		$attacker->data['proficiency'][$this->weapon_kind($attacker)] += 1;
		$attacker->ajax('proficiency', array('proficiency' => $attacker->proficiency));
	}

	/**
	 * 提升经验值
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function gain_experience(player $attacker, player $defender)
	{
		$exp_gain = round(($defender->lvl - $attacker->lvl) / 3);
		$exp_gain = ($exp_gain < 1) ? 1 : $exp_gain;
		$attacker->experience($exp_gain);
	}

	/**
	 * 消耗武器耐久
	 * @param player $attacker
	 * @param player $defender
	 * @param $hitted
	 */
	protected function weapon_consume(player $attacker, player $defender, $hitted)
	{
		switch($this->weapon_kind($attacker)){
			case 'p':
			case 'k':
				//只有命中才消耗耐久
				if($hitted){
					$attacker->item_consume('wep', 0);
				}
				break;
			
			case 'c':
			case 'd':
			case 'g':
				//只要攻击就消耗耐久
				$attacker->item_consume('wep', 0);
				break;
		}
		return;
	}

	/**
	 * 发出声响（枪声、爆炸声）
	 * @param player $attacker
	 * @param player $defender
	 */
	protected function make_noise(player $attacker, player $defender)
	{
		return;
	}

	/**
	 * 获取命中率
	 * @param player $attacker
	 * @param player $defender
	 * @return float
	 *
	 */
	protected function get_hitrate(player $attacker, player $defender)
	{
		global $base_hit_rate, $extra_hit_rate;

		$weapon_kind = $this->weapon_kind($attacker);
		$hitrate = $base_hit_rate[$weapon_kind];
		$hitrate += $extra_hit_rate[$weapon_kind] * $attacker->proficiency[$weapon_kind];
		
		if(isset($modulus_hitrate['weather'])){
			global $gameinfo;
			if(isset($modulus_hitrate['weather'][intval($gameinfo['weather'])])){
				$hitrate *= $modulus_hitrate['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_hitrate['area'])){
			if(isset($modulus_hitrate['area'][intval($attacker->data['area'])])){
				$hitrate *= $modulus_hitrate['area'][intval($attacker->data['area'])];
			}
		}
		
		if(isset($modulus_hitrate['pose'])){
			if(isset($modulus_hitrate['pose'][intval($attacker->data['pose'])])){
				$hitrate *= $modulus_hitrate['pose'][intval($attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_hitrate['tactic'])){
			if(isset($modulus_hitrate['tactic'][intval($attacker->data['tactic'])])){
				$hitrate *= $modulus_hitrate['tactic'][intval($attacker->data['tactic'])];
			}
		}
		
		return $hitrate;
	}

	/**
	 * 计算攻击造成的伤害
	 * 攻击增益、护盾抵消等与战斗相关的伤害效果在此计算
	 * @param player $attacker
	 * @param player $defender
	 * @param $ma_modulus
	 * @return float
	 */
	protected function calc_damage(player $attacker, player $defender, $ma_modulus)
	{
		$att = $this->modulus_attack($attacker, $defender) * $ma_modulus;
		$def = $this->modulus_defend($attacker, $defender);
		$att = ($att < 10) ? 10 : $att;
		$att = ($def < 10) ? 10 : $att;
		$damage = ($att - $def) * log($def, $att) * $this->modulus_proficiency($attacker, $defender) * $this->modulus_critical_hit($attacker, $defender);
		return $damage;
	}

	/**
	 * 计算攻击系数
	 * @param player $attacker
	 * @param player $defender
	 * @return float
	 */
	protected function modulus_attack(player $attacker, player $defender)
	{
		global $modulus_attack;
		
		$att = $attacker->att;
		
		if(isset($modulus_attack['weather'])){
			global $gameinfo;
			if(isset($modulus_attack['weather'][intval($gameinfo['weather'])])){
				$att *= $modulus_attack['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_attack['area'])){
			if(isset($modulus_attack['area'][intval($attacker->data['area'])])){
				$att *= $modulus_attack['area'][intval($attacker->data['area'])];
			}
		}
		
		if(isset($modulus_attack['pose'])){
			if(isset($modulus_attack['pose'][intval($attacker->data['pose'])])){
				$att *= $modulus_attack['pose'][intval($attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_attack['tactic'])){
			if(isset($modulus_attack['tactic'][intval($attacker->data['tactic'])])){
				$att *= $modulus_attack['tactic'][intval($attacker->data['tactic'])];
			}
		}
		
		return $att;
	}

	/**
	 * 计算防御系数
	 * @param player $attacker
	 * @param player $defender
	 * @return float
	 */
	protected function modulus_defend(player $attacker, player $defender)
	{
		global $modulus_defend;
		
		$def = $defender->def;
		
		if(isset($modulus_defend['weather'])){
			global $gameinfo;
			if(isset($modulus_defend['weather'][intval($gameinfo['weather'])])){
				$def *= $modulus_defend['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_defend['area'])){
			if(isset($modulus_defend['area'][intval($defender->data['area'])])){
				$def *= $modulus_defend['area'][intval($defender->data['area'])];
			}
		}
		
		if(isset($modulus_defend['pose'])){
			if(isset($modulus_defend['pose'][intval($defender->data['pose'])])){
				$def *= $modulus_defend['pose'][intval($defender->data['pose'])];
			}
		}
		
		if(isset($modulus_defend['tactic'])){
			if(isset($modulus_defend['tactic'][intval($defender->data['tactic'])])){
				$def *= $modulus_defend['tactic'][intval($defender->data['tactic'])];
			}
		}
		
		return $def;
	}

	/**
	 * 计算熟练度造成的伤害系数
	 * @param player $attacker
	 * @param player $defender
	 * @return mixed
	 */
	protected function modulus_proficiency(player $attacker, player $defender)
	{
		global $proficiency_modulus, $proficiency_intercept;
		$kind = $this->weapon_kind($attacker);
		return $attacker->proficiency[$kind] * $proficiency_modulus[$kind] + $proficiency_intercept[$kind];
	}

	/**
	 * 计算爆击造成的伤害系数
	 * @param player $attacker
	 * @param player $defender
	 * @return int
	 */
	protected function modulus_critical_hit(player $attacker, player $defender)
	{
		return 1;
	}

	/**
	 * 对战斗中的玩家发送feedback消息
	 * @param $msg
	 */
	protected function feedback($msg)
	{
		$this->attacker->feedback($msg);
		$this->defender->feedback($msg);
	}
	
}

?>