<?php

$base_move_speed = 9;

$base_sight = 60;

$backup_move_modulus = 0.8;

$explode_move_modulus = 0.9;

$base_aware_time = 5;

$base_attack_range = array(
	'p' => 1,
	'k' => 1.5,
	'g' => 30,
	'c' => 20,
	'd' => 10
);

$base_preload = array(
	'p' => 1.25,
	'k' => 1.25,
	'g' => 0,
	'c' => 1,
	'd' => 2.5
);

$base_overload = array(
	'p' => 0.25,
	'k' => 0.25,
	'g' => 1,
	'c' => 0.5,
	'd' => 0
);

$base_ammo_reload = 4;

class combat_status_timeline
{
	public $combat;
	public $player;
	public $in_battle;
	public $aware_time;
	public $preload_time;
	public $overload_time;
	public $stun_time;
	
	public function __get($name)
	{
		if ($name === 'aware'){
			return $this->combat->time >= $this->aware_time;
		} else if ($name === 'stun'){
			return $this->combat->time < $this->stun_time;
		} else if ($name === 'preload'){
			return $this->combat->time < $this->preload_time;
		} else if ($name === 'overload'){
			return $this->combat->time < $this->overload_time;
		} else {
			throw 'Unexected property: '.$name;
		}
	}
}

class combat_timeline extends combat
{
	public $time;
	public $distance;
	
	public function __construct(player $att, player $def)
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
	
	public function set_distance($distance)
	{
		$this->distance = $distance;
	}
	
	public function battle_start()
	{
		global $g;
		
		$this->time = 0;
		
		$player = array(0 => $this->attacker, 1 => $this->defender);
		$status = array(0 => $this->create_status($player[0], $player[1]), 1 => $this->create_status($player[1], $player[0]));
		$status[1]->aware_time = $this->get_aware_time($player[0], $player[1]);
		
		while ($this->time < 10 && (
			$status[0]->in_battle && $this->distance <= $this->get_sight($player[0], $player[1]) ||
			$status[1]->in_battle && $this->distance <= $this->get_sight($player[1], $player[0]))) {
			
			if ($status[0]->aware_time == $this->time) {
				$this->feedback($player[0]->name.'发现了'.$player[1]->name);
				$status[0]->preload_time = $this->time + $this->get_preload($status[0]->player, $status[1]->player);
			}
			
			if ($status[1]->aware_time == $this->time) {
				$this->feedback($player[1]->name.'发现了'.$player[0]->name);
				$status[1]->preload_time = $this->time + $this->get_preload($status[1]->player, $status[0]->player);
			}
			// Actions TODO: general reaction time
			if ($this->allow_attack($status[0], $status[1])) {
				$this->attack_round_timeline($status[0], $status[1]);
					
				if (!$player[1]->is_alive())
					break;
			}
			if ($this->allow_attack($status[1], $status[0])) {
				$this->attack_round_timeline($status[1], $status[0]);
				
				if (!$player[0]->is_alive())
					break;
			}
			
			$relative_speed = 0;
			$duration = 0;
			
            // Move speed
			$relative_speed += $this->get_relative_speed($status[0], $status[1]);
			$relative_speed += $this->get_relative_speed($status[1], $status[0]);
			
			// Duration
			$durations = $this->get_duration_candidates($status[0], $status[1], $relative_speed);
			
			foreach($durations as $candidate) {
				if ($candidate > 0 && ($candidate < $duration || $duration == 0)) {
					$duration = $candidate;
				}
			}
			
			if ($duration == 0) {
				$this->feedback('BUG: 0 duration');
				$this->feedback(var_export($durations, true));
			}
			
			$this->time += $duration;
			$this->distance += $relative_speed * $duration;
			if ($this->distance < 0) {
				$this->distance = 0;
			}
		}
		
		$this->feedback('战斗结束');
	}
	
	protected function attack_round_timeline($status_att, $status_def)
	{
		$this->attack_round($status_att->player, $status_def->player);
			
		$status_att->overload_time = $this->time + $this->get_overload($status_att->player, $status_def->player);
		$status_att->preload_time = $status_att->overload_time + $this->get_preload($status_att->player, $status_def->player);
		
		if (!$status_def->aware) {
			$status_def->aware_time = $this->time + 0.5;
		}
	}
	
	protected function create_status(player $player, player $enemy)
	{
		$status = new combat_status_timeline();
		
		$status->combat = $this;
		$status->player = $player;
		$status->in_battle = true;
		$status->preload_time = $this->get_preload($player, $enemy);
		$status->aware_time = 0;
		$status->overload_time = 0;
		$status->stun_time = 0;
		
		return $status;
	}
	
	protected function get_aware_time(player $player_1, player $player_2)
	{
		// TODO: Poission
		global $g, $base_aware_time;
		return -log(1 - $g->random(0, 65535) / 65536) * $base_aware_time;
	}
	
	protected function get_relative_speed($status_1, $status_2)
	{
		global $backup_move_modulus;
		
		if (!$status_1->stun && !$status_1->overload && $status_1->aware) {
			if ($status_1->in_battle) {
				return -$this->get_speed($status_1->player, $status_2->player);
			} else if (false) {
				return $this->get_speed($status_1->player, $status_2->player) * $backup_speed_modulus;
			} else {
				return $this->get_speed($status_1->player, $status_2->player);
			}
		}
		
		return 0;
	}
	
	protected function get_duration_candidates($status_1, $status_2, $relative_speed)
	{
		$duartions = array();
		
		$durations[] = $status_1->overload_time - $this->time;
		$durations[] = $status_2->overload_time - $this->time;
		$durations[] = $status_1->stun_time - $this->time;
		$durations[] = $status_2->stun_time - $this->time;
		$durations[] = $status_1->preload_time - $this->time;
		$durations[] = $status_2->preload_time - $this->time;
		$durations[] = $status_1->aware_time - $this->time;
		$durations[] = $status_2->aware_time - $this->time;
		if ($relative_speed != 0)
		{
			$durations[] = ($this->get_sight($status_1->player, $status_2->player) - $this->distance) / $relative_speed;
			$durations[] = ($this->get_sight($status_2->player, $status_1->player) - $this->distance) / $relative_speed;
			$durations[] = ($this->get_range($status_1->player, $status_2->player) - $this->distance) / $relative_speed;
			$durations[] = ($this->get_range($status_2->player, $status_1->player) - $this->distance) / $relative_speed;
		}
		
		return $durations;
	}
	
	protected function get_sight(player $attacker, player $defneder)
	{
		global $base_sight;
		return $base_sight;
	}
	
	protected function get_range(player $attacker, player $defender)
	{
		global $base_attack_range;
		return $base_attack_range[$this->weapon_kind($attacker)];
	}
	
	protected function get_preload(player $attacker, player $defneder)
	{
		global $base_preload;
		return $base_preload[$this->weapon_kind($attacker)];
	}
	
	protected function get_overload(player $attacker, player $defneder)
	{
		global $base_overload;
		return $base_overload[$this->weapon_kind($attacker)];
	}
	
	protected function get_speed(player $attacker, player $defender)
	{
		global $base_move_speed;
		return $base_move_speed;
	}
	
	protected function allow_attack($status_att, $status_def)
	{
		return
			$status_att->in_battle &&
			!$status_att->preload &&
			!$status_att->overload &&
			!$status_att->stun &&
			$status_att->aware &&
			$this->get_range($status_att->player, $status_def->player) >= $this->distance;
	}
	
	protected function feedback($message)
	{
		$t = intval($this->time * 100) / 100;
		$d = intval($this->distance * 100) / 100;
		parent::feedback("[${t}s ${d}m] $message");
	}
	
	/******************* BRA codes ********************/

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