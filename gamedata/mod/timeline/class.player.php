<?php

class player_timeline extends player
{
	public function attack()
	{
		global $g;

		if(false === $this->is_alive()){
			$this->error('你已经死了', false);
			return;
		}
		
		if(false === isset($this->action['battle'])){
			$this->error('目前尚未碰到敌人', false);
			return;
		}
		
		$enemy_data = $g->get_player_by_id($this->action['battle']['pid']);
		$enemy = new_player($enemy_data);
		
		if(false === $enemy->is_alive()){
			$this->update_enemy_info($enemy, true);
			$this->error('对方已阵亡', false);
			return;
		}
		
		$combat = new_combat($this, $enemy);
		
		$combat->set_distance(50);
		
		$combat->battle_start();
		
		$this->ajax('battle', array(
			'enemy' => $this->get_enemy_info($enemy, true),
			'end' => $enemy->is_alive()
			));
		
		if($enemy->is_alive()){
			unset($this->data['action']['battle']);
		}
	}
}

?>