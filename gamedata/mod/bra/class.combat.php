<?php

class combat_bra extends combat
{
	public $counter = false;
	
	public function __construct(player $att, player $def)
	{
		parent::__construct($att, $def);
		
		if(isset($this->defender->equipment['arb']['sk']['anti-'.$this->kind])){
			$this->feedback($this->defender->name.'的'.$this->defender->equipment['n'].'展现出了惊人的抗性');
		}
		
		return;
	}
	
	public function attack_without_counter()
	{
		$damage = parent::attack();
		
		$attacker = $this->attacker;
		$defender = $this->defender;
		
		$rage = round(($attacker->lvl - $defender->lvl) / 3);
		if($rage < 1){
			$rage = 1;
		}
		
		$defender->rage += $rage;
		$defender->ajax('rage', array('rage' => $defender->rage));
		
		return $damage;
	}
	
	public function attack()
	{
		$total_damage = $this->attack_without_counter(false);
		
		$counter = $this->counter;
		
		$attacker = $this->attacker;
		$defender = $this->defender;
		
		if(false === $counter){
			$c_combat = new_combat($this->defender, $this->attacker);
			$c_combat->counter = true;
			if(determine(intval($this->get_counter_rate()))){
				$this->feedback($this->defender->name.'发起反击');
				$c_damage = $c_combat->attack();
				
				$GLOBALS['g']->record_battle_damage($c_damage, $this->defender, $this->attacker);
				
				//反击致死
				if(false === $attacker->is_alive()){
					if((false === isset($defender->data['action']['battle'])) && (intval($defender->type) === GAME_PLAYER_USER)){
						$defender->found_enemy($attacker);
					}
				}
			}else{
				$this->feedback($this->defender->name.'无法反击，逃跑了');
				$c_combat->gain_experience();
			}
			$this->feedback('战斗结束');
		}
		
		return $total_damage;
	}
	
	protected function damage($effect)
	{
		$damage = parent::damage($effect);
		
		return $damage;
	}
	
	public function gain_experience()
	{
		return parent::gain_experience();
	}
	
	protected function calc_damage($ma_modulus)
	{
		$damage = $this->modulus_proficiency() * $this->modulus_critical_hit() * $this->modulus_attack() / $this->modulus_defend() * $ma_modulus;
		if($this->kind === 'd'){
			$damage += $this->attacker->equipment['wep']['e'];
		}
		
		if(isset($this->defender->equipment['arb']['sk']['anti-'.$this->kind])){
			$damage *= $this->defender->equipment['arb']['sk']['anti-'.$this->kind];
		}
		
		return $damage;
	}
	
	protected function get_counter_rate()
	{
		if(false === $this->defender->is_alive()){
			return false;
		}
		
		global $base_counter_rate;
		
		$counter_rate = $base_counter_rate[$this->kind];
		
		if(isset($modulus_counter_rate['weather'])){
			global $gameinfo;
			if(isset($modulus_counter_rate['weather'][intval($gameinfo['weather'])])){
				$counter_rate *= $modulus_counter_rate['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_counter_rate['area'])){
			if(isset($modulus_counter_rate['area'][intval($this->attacker->data['area'])])){
				$counter_rate *= $modulus_counter_rate['area'][intval($this->attacker->data['area'])];
			}
		}
		
		if(isset($modulus_counter_rate['pose'])){
			if(isset($modulus_counter_rate['pose'][intval($this->attacker->data['pose'])])){
				$counter_rate *= $modulus_counter_rate['pose'][intval($this->attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_counter_rate['tactic'])){
			if(isset($modulus_counter_rate['tactic'][intval($this->attacker->data['tactic'])])){
				$counter_rate *= $modulus_counter_rate['tactic'][intval($this->attacker->data['tactic'])];
			}
		}
		
		return $counter_rate;
	}
	
	protected function get_hitrate()
	{
		if($this->kind === 'c'){
			return 100;
		}else{
			global $gameinfo;
			
			$hitrate = parent::get_hitrate();
			
			if(intval($gameinfo['weather']) === 12){
				$hitrate += 20;
			}
			
			foreach($this->attacker->buff as $buff){
				if($buff['type'] === 'injured_head'){
					$hitrate -= 20;
				}
			}
			
			return $hitrate;
		}
	}
	
	protected function injure($position)
	{
		switch($position){
			case 'b':
				$this->defender->buff('injured_body');
				$this->feedback($this->defender->name.'的胸部受伤了');
				break;
			
			case 'h':
				$this->defender->buff('injured_head');
				$this->feedback($this->defender->name.'的头部受伤了');
				break;
			
			case 'a':
				$this->defender->buff('injured_arm');
				$this->feedback($this->defender->name.'的腕部受伤了');
				break;
			
			case 'f':
				$this->defender->buff('injured_foot');
				$this->feedback($this->defender->name.'的足部受伤了');
				break;
		}
	}
	
	protected function modulus_attack()
	{
		global $modulus_attack;
		
		$att = parent::modulus_attack();
		
		if($this->counter){
			if(isset($modulus_attack['pose'])){
				if(isset($modulus_attack['pose'][intval($this->attacker->data['pose'])])){
					$att /= $modulus_attack['pose'][intval($this->attacker->data['pose'])];
				}
			}
		}else{
			if(isset($modulus_attack['tactic'])){
				if(isset($modulus_attack['tactic'][intval($this->attacker->data['tactic'])])){
					$att /= $modulus_attack['tactic'][intval($this->attacker->data['tactic'])];
				}
			}
		}
		
		foreach($this->attacker->buff as $buff){
			if($buff['type'] === 'injured_arm'){
				$att *= 0.8;
			}
		}
		
		return $att;
	}
	
	protected function modulus_defend()
	{
		global $modulus_defend;
		
		$def = parent::modulus_defend();
		
		if($this->counter){
			if(isset($modulus_defend['pose'])){
				if(isset($modulus_defend['pose'][intval($this->defender->data['pose'])])){
					$def /= $modulus_defend['pose'][intval($this->defender->data['pose'])];
				}
			}
		}else{
			if(isset($modulus_defend['tactic'])){
				if(isset($modulus_defend['tactic'][intval($this->defender->data['tactic'])])){
					$def /= $modulus_defend['tactic'][intval($this->defender->data['tactic'])];
				}
			}
		}
		
		foreach($this->defender->buff as $buff){
			if($buff['type'] === 'injured_body'){
				$def *= 0.8;
			}
		}
		
		return $def;
	}
	
	protected function modulus_critical_hit()
	{
		$modulus = 1;
		
		$attacker = $this->attacker;
		if($attacker->club == 9){
			if($this->is_critical_hit()){
				$this->feedback($attacker->name.'使出了必杀技！');
				$modulus *= 1.8;
			}
		}else{
			if($this->is_critical_hit()){
				$this->feedback($attacker->name.'会心一击！');
				$modulus *= 1.4;
			}
		}
		
		return $modulus;
	}
	
	protected function is_critical_hit()
	{
		$attacker = $this->attacker;
		if($attacker->club == 9){
			if($attacker->rage >= 50 && determine(30)){
				//TODO: 控制功能 使用技能？
				$attacker->data['rage'] -= 50;
				$attacker->ajax('rage', array('rage' => $attacker->rage));
				return true;
			}
		}else{
			if(intval($attacker->lvl) >= 3 && intval($attacker->proficiency[$this->kind]) >= 20 && $attacker->rage >= 30 && determine(30)){
				$attacker->data['rage'] -= 30;
				$attacker->ajax('rage', array('rage' => $attacker->rage));
				return true;
			}
		}
		return false;
	}
	
	protected function make_noise()
	{
		if(isset($this->attacker->data['equipment']['wep']['sk']['silent'])){
			return;
		}
		
		switch($this->kind){
			case 'g':
				global $a, $map;
				$a->action('notice', array('msg' => $map[$this->attacker->area].'传来了枪声'), array('$exception' => array($this->attacker->uid, $this->defender->uid)));
				break;
			
			case 'd':
				global $a, $map;
				$a->action('notice', array('msg' => $map[$this->attacker->area].'传来了爆炸声'), array('$exception' => array($this->attacker->uid, $this->defender->uid)));
				break;
		}
		return;
	}
}

?>