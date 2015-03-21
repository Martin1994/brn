<?php

class combat
{
	
	protected $attacker;
	protected $defender;
	protected $kind;
	protected $fist;
	
	public function __construct(player $att, player $def)
	{
		$this->attacker = $att;
		$this->defender = $def;
		$this->kind = ($this->attacker->equipment['wep']['k'] == '') ? 'p' :strtolower(substr($this->attacker->equipment['wep']['k'] , 1, 1));
		$this->fist = $this->attacker->equipment['wep']['n'] == '';
	}
	
	/**
	 * 返回最终攻击输出
	 */
	public function attack()
	{
		$attacker = $this->attacker;
		$defender = $this->defender;
		$total_damage = 0;

		$multiple_attack = $this->get_multiple();
		
		$multistage_attack = $this->get_multistage();
		
		$hitted = false;
		$buffed = false;
		$hurt = false;
		$total_damage = 0;
		
		foreach($multiple_attack as $m1_modulus){
			$hitrate = intval($this->get_hitrate());
			if($GLOBALS['g']->determine($hitrate)){
				$hitted = true;
				foreach($multistage_attack as $m2_modulus){
					$multistage_total_damage = 0;
					$damage = $this->calc_damage($m1_modulus * $m2_modulus);
					$multistage_total_damage += $damage;
					$damage = round($damage * 10) / 10;
					$weapon = $this->fist ? $GLOBALS['fist'] : $attacker->equipment['wep']['n'];
					
					$this->feedback($attacker->name.'使用 '.$weapon.' 攻击（'.$GLOBALS['weapon_types'][$this->kind].'）'.$defender->name.'，造成了'.$damage.'点伤害');
					
					//淬毒
					if((false === $buffed) || (false === isset($attacker->equipment['wep']['sk']['single-buff']))){
						$this->buff();
						$buffed = true;
					}
					
					//磨甲 / 受伤
					if(false === $hurt || false === isset($attacker->equipment['wep']['sk']['single-hurt'])){
						$this->hurt();
						$hurt = true;
					}
					
				}
				
				$total_damage += $this->damage($multistage_total_damage);
				
				if(false === $defender->is_alive()){
					$this->attacker->update_enemy_info($this->defender, false);
				}
			}else{
				$this->feedback($attacker->name.'没有击中'.$defender->name);
			}
		}
		if(!$this->fist){
			//损耗
			$this->weapon_consume($hitted);
			$attacker->ajax('item', array('equipment' => $attacker->parse_equipment()));
			
			//声音
			$this->make_noise();
		}
		
		//熟练
		$this->gain_proficiency();
		
		//经验
		$this->gain_experience();
		
		if($this->attacker->type == GAME_PLAYER_USER && ($total_damage > $GLOBALS['g']->gameinfo['hdamage'])){
			$GLOBALS['g']->renew_top_player($this->attacker, $total_damage);
		}
		
		return $total_damage;
	}
	
	protected function get_multiple()
	{
		//处理多重攻击，反复计算击中
		if(isset($this->attacker->equipment['wep']['sk']['multiple'])){
			if(is_array($this->attacker->equipment['wep']['sk']['multiple'])){
				$multiple_attack = $this->attacker->equipment['wep']['sk']['multiple'];
			}else{
				$multiple_attack = array();
				$this->attacker->equipment['wep']['sk']['multiple'] = intval($this->attacker->equipment['wep']['sk']['multiple']);
				for($i = 0; $i < $this->attacker->equipment['wep']['sk']['multiple']; $i ++){
					$multiple_attack[] = 1 / $this->attacker->equipment['wep']['sk']['multiple'];
				}
			}
		}else{
			$multiple_attack = array(1);
		}
		
		return $multiple_attack;
	}
	
	protected function get_multistage()
	{
		//处理多段攻击，只计算一次击中
		if(isset($this->attacker->equipment['wep']['sk']['multistage'])){
			if(is_array($this->attacker->equipment['wep']['sk']['multistage'])){
				$multistage_attack = $this->attacker->equipment['wep']['sk']['multistage'];
			}else{
				$multistage_attack = array();
				$this->attacker->equipment['wep']['sk']['multistage'] = intval($this->attacker->equipment['wep']['sk']['multistage']);
				for($i = 0; $i < $this->attacker->equipment['wep']['sk']['multistage']; $i ++){
					$multistage_attack[] = 1 / $this->attacker->equipment['wep']['sk']['multistage'];
				}
			}
		}else{
			$multistage_attack = array(1);
		}
		
		return $multistage_attack;
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
	
	protected function damage($effect)
	{
		return $this->defender->damage($effect , array('pid' => $this->attacker->_id, 'weapon' => $this->attacker->equipment['wep']['n'], 'type' => 'weapon_'.$this->kind));
	}
	
	protected function hurt()
	{
		global $hurt_position;
		
		$threshold = $this->get_hurt_rate();
		
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
			
			$avalible_position = $this->fist ? $hurt_position['fist'] : $hurt_position[$this->kind];
			if(in_array($position, $avalible_position)){
				if(isset($this->defender->data['equipment']['ar'.$position]['n']) && $this->defender->data['equipment']['ar'.$position]['n'] != ''){
					$this->attrit($position);
				}else{
					$this->injure($position);
				}
			}
		}
	}
	
	protected function injure($position)
	{
		$this->defender->damage(25, array('pid' => $this->attacker->_id, 'type' => 'injure'));
	}
	
	protected function attrit($position)
	{
		$this->defender->item_consume('ar'.$position);
		$this->defender->notice($this->defender->data['equipment']['ar'.$position]['n'].'的耐久度下降了');
		$this->defender->ajax('item', array('equipment' => $this->defender->parse_equipment()));
	}
	
	protected function get_hurt_rate()
	{
		global $hurt_rate;
		return $this->fist ? $hurt_rate['fist'] : $hurt_rate[$this->kind];
	}
	
	protected function gain_proficiency()
	{
		$this->attacker->data['proficiency'][$this->kind] += 1;
		$this->attacker->ajax('proficiency', array('proficiency' => $this->attacker->proficiency));
	}
	
	protected function gain_experience()
	{
		$exp_gain = round(($this->defender->lvl - $this->attacker->lvl) / 3);
		$exp_gain = ($exp_gain < 1) ? 1 : $exp_gain;
		$this->attacker->experience($exp_gain);
	}
	
	protected function weapon_consume($hitted)
	{
		switch($this->kind){
			case 'p':
			case 'k':
				if($hitted){
					$this->attacker->item_consume('wep', 0);
				}
				break;
			
			case 'c':
			case 'd':
			case 'g':
				$this->attacker->item_consume('wep', 0);
				break;
		}
		return;
	}
	
	protected function make_noise()
	{
		return;
	}
	
	protected function get_hitrate()
	{
		global $base_hit_rate, $extra_hit_rate;
		
		$hitrate = $base_hit_rate[$this->kind];
		$hitrate += $extra_hit_rate[$this->kind] * $this->attacker->proficiency[$this->kind];
		
		if(isset($modulus_hitrate['weather'])){
			global $gameinfo;
			if(isset($modulus_hitrate['weather'][intval($gameinfo['weather'])])){
				$hitrate *= $modulus_hitrate['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_hitrate['area'])){
			if(isset($modulus_hitrate['area'][intval($this->attacker->data['area'])])){
				$hitrate *= $modulus_hitrate['area'][intval($this->attacker->data['area'])];
			}
		}
		
		if(isset($modulus_hitrate['pose'])){
			if(isset($modulus_hitrate['pose'][intval($this->attacker->data['pose'])])){
				$hitrate *= $modulus_hitrate['pose'][intval($this->attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_hitrate['tactic'])){
			if(isset($modulus_hitrate['tactic'][intval($this->attacker->data['tactic'])])){
				$hitrate *= $modulus_hitrate['tactic'][intval($this->attacker->data['tactic'])];
			}
		}
		
		return $hitrate;
	}
	
	protected function calc_damage($ma_modulus)
	{
		$att = $this->modulus_attack() * $ma_modulus;
		$def = $this->modulus_defend();
		$att = ($att < 10) ? 10 : $att;
		$att = ($def < 10) ? 10 : $att;
		$damage = ($att - $def) * log($def, $att) * $this->modulus_proficiency() * $this->modulus_critical_hit();
		return $damage;
	}
	
	protected function modulus_attack()
	{
		global $modulus_attack;
		
		$att = $this->attacker->att;
		
		if(isset($modulus_attack['weather'])){
			global $gameinfo;
			if(isset($modulus_attack['weather'][intval($gameinfo['weather'])])){
				$att *= $modulus_attack['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_attack['area'])){
			if(isset($modulus_attack['area'][intval($this->attacker->data['area'])])){
				$att *= $modulus_attack['area'][intval($this->attacker->data['area'])];
			}
		}
		
		if(isset($modulus_attack['pose'])){
			if(isset($modulus_attack['pose'][intval($this->attacker->data['pose'])])){
				$att *= $modulus_attack['pose'][intval($this->attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_attack['tactic'])){
			if(isset($modulus_attack['tactic'][intval($this->attacker->data['tactic'])])){
				$att *= $modulus_attack['tactic'][intval($this->attacker->data['tactic'])];
			}
		}
		
		return $att;
	}
	
	protected function modulus_defend()
	{
		global $modulus_defend;
		
		$def = $this->defender->def;
		
		if(isset($modulus_defend['weather'])){
			global $gameinfo;
			if(isset($modulus_defend['weather'][intval($gameinfo['weather'])])){
				$def *= $modulus_defend['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_defend['area'])){
			if(isset($modulus_defend['area'][intval($this->defender->data['area'])])){
				$def *= $modulus_defend['area'][intval($this->defender->data['area'])];
			}
		}
		
		if(isset($modulus_defend['pose'])){
			if(isset($modulus_defend['pose'][intval($this->defender->data['pose'])])){
				$def *= $modulus_defend['pose'][intval($this->defender->data['pose'])];
			}
		}
		
		if(isset($modulus_defend['tactic'])){
			if(isset($modulus_defend['tactic'][intval($this->defender->data['tactic'])])){
				$def *= $modulus_defend['tactic'][intval($this->defender->data['tactic'])];
			}
		}
		
		return $def;
	}
	
	protected function modulus_proficiency()
	{
		global $proficiency_modulus, $proficiency_intercept;
		$kind = $this->kind;
		return $this->attacker->proficiency[$kind] * $proficiency_modulus[$kind] + $proficiency_intercept[$kind];
	}
	
	protected function modulus_critical_hit()
	{
		return 1;
	}
	
	protected function feedback($msg)
	{
		$this->attacker->feedback($msg);
		$this->defender->feedback($msg);
	}
	
}

?>