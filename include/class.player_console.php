<?php

class player_console
{
	
	protected $db;
	protected $players;
	
	public function __construct($db)
	{
		$this->db = $db;
		$this->players = array();
		return;
	}
	
	public function get_player($condition = false, $limit = 1)
	{
		/* DEPRECATED */
		$db = $this->db;
		
		$players = $db->select('players', '*', $condition, $limit);
		
		if($players === false){
			return false;
		}
		
		$this->add_player($players[0]);
		
		return $players;
	}
	
	public function &add_player(array &$data)
	{
		$pid = $data['_id'];
		if(isset($this->players[strval($pid)])){
			$data = &$this->players[strval($pid)];
		}else{
			player_data_preprocess($data);
			
			$this->players[strval($pid)] = &$data;
		}
		
		return $data;
	}
	
	public function __destruct()
	{
		$db = $this->db;
		
		foreach($this->players as $pid => &$player){
			player_data_postprocess($player);
			
			$db->update('players', $player, array('_id' => $player['_id']));
		}
		return;
	}
	
}

?>