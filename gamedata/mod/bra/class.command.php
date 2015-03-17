<?php

class command_bra extends command
{
	
	public function action_handler($action, $param)
	{
		switch($action){
			case 'init':
				if($this->player) {
					$GLOBALS['a']->action('rage', array('rage' => $this->player->rage));
				}
				parent::action_handler($action, $param);
				break;
			
			case 'wound_dressing':
				if(false === isset($param['position'])){
					$this->player->error('请指定受伤部位');
				}
				$this->player->wound_dressing($param['position']);
				break;
			
			default:
				parent::action_handler($action, $param);
				break;
		}
	}
	
}

?>