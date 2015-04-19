<?php

/**
 * Class combat_bra
 *
 * @property player_bra $attacker 先手发动攻击的玩家，在战斗引擎中并不一定总是攻击者（例如反击时）
 * @property player_bra $defender 后手发动攻击的玩家，在战斗引擎中并不一定总是防御者（例如反击时）
 */
class combat_bra extends combat
{
	public function __construct(player_bra $att, player_bra $def)
	{
		parent::__construct($att, $def);
		
		return;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return float
	 */
	public function attack(player $attacker, player $defender)
	{
		$damage = parent::attack($attacker, $defender);

		$rage = round(($attacker->lvl - $defender->lvl) / 3);
		if($rage < 1){
			$rage = 1;
		}

		$defender->rage += $rage;
		$defender->ajax('rage', array('rage' => $defender->rage));
		
		return $damage;
	}
	
	public function battle_start()
	{
		global $g;
		if(isset($this->defender->equipment['arb']['sk']['anti-'.$this->weapon_kind($this->attacker)])){
			$this->feedback($this->defender->name.'的'.$this->defender->equipment['n'].'展现出了惊人的抗性');
		}

		$this->attack_round($this->attacker, $this->defender);

		if ($g->determine(intval($this->get_counter_rate($this->attacker, $this->defender)))) {
			$this->feedback($this->defender->name . '发起反击');

			if (isset($this->attacker->equipment['arb']['sk']['anti-' . $this->weapon_kind($this->defender)])) {
				$this->feedback($this->attacker->name . '的' . $this->attacker->equipment['n'] . '展现出了惊人的抗性');
			}

			$this->attack_round($this->defender, $this->attacker);

			//反击致死
			if (false === $this->attacker->is_alive()) {
				if ((false === isset($this->defender->data['action']['battle']))
					&& (intval($this->defender->type) === GAME_PLAYER_USER)
				) {
					$this->defender->found_enemy($this->attacker);
				}
			}
		} else {
			$this->feedback($this->defender->name . '无法反击，逃跑了');
			$this->gain_experience($this->defender, $this->attacker);
		}
		$this->feedback('战斗结束');
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @param $ma_modulus
	 * @return float
	 */
	protected function calc_damage(player $attacker, player $defender, $ma_modulus)
	{
		$damage =
			  $this->modulus_proficiency($attacker, $defender)
			* $this->modulus_critical_hit($attacker, $defender)
			* $this->modulus_attack($attacker, $defender)
			/ $this->modulus_defend($attacker, $defender)
			* $ma_modulus;

		$weapon_kind = $this->weapon_kind($attacker);

		if($this->weapon_kind($attacker) === 'd'){
			$damage += $attacker->equipment['wep']['e'];
		}
		
		if(isset($defender->equipment['arb']['sk']['anti-'.$weapon_kind])){
			$damage *= $defender->equipment['arb']['sk']['anti-'.$weapon_kind];
		}
		
		return $damage;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return bool
	 */
	protected function get_counter_rate(player $attacker, player $defender)
	{
		if(false === $defender->is_alive()){
			return false;
		}
		
		global $base_counter_rate;
		
		$counter_rate = $base_counter_rate[$this->weapon_kind($attacker)];
		
		if(isset($modulus_counter_rate['weather'])){
			global $gameinfo;
			if(isset($modulus_counter_rate['weather'][intval($gameinfo['weather'])])){
				$counter_rate *= $modulus_counter_rate['weather'][intval($gameinfo['weather'])];
			}
		}
		
		if(isset($modulus_counter_rate['area'])){
			if(isset($modulus_counter_rate['area'][intval($attacker->data['area'])])){
				$counter_rate *= $modulus_counter_rate['area'][intval($attacker->data['area'])];
			}
		}
		
		if(isset($modulus_counter_rate['pose'])){
			if(isset($modulus_counter_rate['pose'][intval($attacker->data['pose'])])){
				$counter_rate *= $modulus_counter_rate['pose'][intval($attacker->data['pose'])];
			}
		}
		
		if(isset($modulus_counter_rate['tactic'])){
			if(isset($modulus_counter_rate['tactic'][intval($attacker->data['tactic'])])){
				$counter_rate *= $modulus_counter_rate['tactic'][intval($attacker->data['tactic'])];
			}
		}
		
		return $counter_rate;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return float|int
	 */
	protected function get_hitrate(player $attacker, player $defender)
	{
		if($this->weapon_kind($attacker) === 'c'){
			return 100;
		}else{
			global $gameinfo;
			
			$hitrate = parent::get_hitrate($attacker, $defender);
			
			if(intval($gameinfo['weather']) === 12){
				$hitrate += 20;
			}
			
			foreach($attacker->buff as $buff){
				if($buff['type'] === 'injured_head'){
					$hitrate -= 20;
				}
			}
			
			return $hitrate;
		}
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @param $position
	 */
	protected function injure(player $attacker, player $defender, $position)
	{
		switch($position){
			case 'b':
				$defender->buff('injured_body');
				$this->feedback($defender->name.'的胸部受伤了');
				break;
			
			case 'h':
				$defender->buff('injured_head');
				$this->feedback($defender->name.'的头部受伤了');
				break;
			
			case 'a':
				$defender->buff('injured_arm');
				$this->feedback($defender->name.'的腕部受伤了');
				break;
			
			case 'f':
				$defender->buff('injured_foot');
				$this->feedback($defender->name.'的足部受伤了');
				break;
		}
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return float
	 */
	protected function modulus_attack(player $attacker, player $defender)
	{
		global $modulus_attack;

		$att = parent::modulus_attack($attacker, $defender);

		//判断本次攻击是不是反击
		$counter = $attacker->_id == $this->attacker->_id;
		
		if($counter){
			if(isset($modulus_attack['pose'])){
				if(isset($modulus_attack['pose'][intval($attacker->data['pose'])])){
					$att /= $modulus_attack['pose'][intval($attacker->data['pose'])];
				}
			}
		}else{
			if(isset($modulus_attack['tactic'])){
				if(isset($modulus_attack['tactic'][intval($attacker->data['tactic'])])){
					$att /= $modulus_attack['tactic'][intval($attacker->data['tactic'])];
				}
			}
		}
		
		foreach($attacker->buff as $buff){
			if($buff['type'] === 'injured_arm'){
				//腕部受伤
				$att *= 0.8;
			}
		}
		
		return $att;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return float
	 */
	protected function modulus_defend(player $attacker, player $defender)
	{
		global $modulus_defend;
		
		$def = parent::modulus_defend($attacker, $defender);

		//判断本次攻击是不是反击
		$counter = $attacker->_id == $this->attacker->_id;
		
		if($counter){
			if(isset($modulus_defend['pose'])){
				if(isset($modulus_defend['pose'][intval($defender->data['pose'])])){
					$def /= $modulus_defend['pose'][intval($defender->data['pose'])];
				}
			}
		}else{
			if(isset($modulus_defend['tactic'])){
				if(isset($modulus_defend['tactic'][intval($defender->data['tactic'])])){
					$def /= $modulus_defend['tactic'][intval($defender->data['tactic'])];
				}
			}
		}
		
		foreach($defender->buff as $buff){
			if($buff['type'] === 'injured_body'){
				//胸部受伤
				$def *= 0.8;
			}
		}
		
		return $def;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return float|int
	 */
	protected function modulus_critical_hit(player $attacker, player $defender)
	{
		$modulus = 1;

		if($attacker->club == 9){
			if($this->is_critical_hit($attacker, $defender)){
				$this->feedback($attacker->name.'使出了必杀技！');
				$modulus *= 1.8;
			}
		}else{
			if($this->is_critical_hit($attacker, $defender)){
				$this->feedback($attacker->name.'会心一击！');
				$modulus *= 1.4;
			}
		}
		
		return $modulus;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 * @return bool
	 */
	protected function is_critical_hit(player $attacker, player $defender)
	{
		global $g;

		if($attacker->club == 9){
			if($attacker->rage >= 50 && $g->determine(30)){
				//TODO: 控制功能 使用技能？
				$attacker->data['rage'] -= 50;
				$attacker->ajax('rage', array('rage' => $attacker->rage));
				return true;
			}
		}else{
			if(intval($attacker->lvl) >= 3
				&& intval($attacker->proficiency[$this->weapon_kind($attacker)]) >= 20
				&& $attacker->rage >= 30
				&& $g->determine(30)){
				$attacker->data['rage'] -= 30;
				$attacker->ajax('rage', array('rage' => $attacker->rage));
				return true;
			}
		}
		return false;
	}

	/**
	 * @param player_bra $attacker
	 * @param player_bra $defender
	 */
	protected function make_noise(player $attacker, player $defender)
	{
		if(isset($attacker->data['equipment']['wep']['sk']['silent'])){
			return;
		}
		
		switch($this->weapon_kind($attacker)){
			case 'g':
				global $a, $map;
				$a->action('notice', array('msg' => $map[$attacker->area].'传来了枪声'), array('$exception' => array($attacker->uid, $defender->uid)));
				break;
			
			case 'd':
				global $a, $map;
				$a->action('notice', array('msg' => $map[$defender->area].'传来了爆炸声'), array('$exception' => array($attacker->uid, $defender->uid)));
				break;
		}
		return;
	}
}

?>