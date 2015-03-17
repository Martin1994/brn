<?php

function new_game()
{
	return new $GLOBALS['GAME_CLASS']();
}

function new_command($cplayer)
{
	return new $GLOBALS['COMMAND_CLASS']($cplayer);
}

/* @return player */
function new_player(&$data)
{
	return new $GLOBALS['PLAYER_CLASS']($data);
}

/* @return item */
function new_item(player $player, array &$data, $id)
{
	return new $GLOBALS['ITEM_CLASS']($player, $data, $id); //TODO: id塞在data里，于player_console处理
}

function new_combat(player $attacker, player $defender)
{
	return new $GLOBALS['COMBAT_CLASS']($attacker, $defender);
}

function game_forbid_area()
{
	return $GLOBALS['g']->game_forbid_area();
}

function game_start()
{
	return $GLOBALS['g']->game_start();
}

function game_end()
{
	return $GLOBALS['g']->game_end();
}

function get_next_areatime()
{
	return $GLOBALS['g']->get_next_areatime();
}

function get_areainfo()
{
	return $GLOBALS['g']->get_areainfo();
}

function current_player()
{
	return $GLOBALS['g']->current_player();
}

function enter_game()
{
	return $GLOBALS['g']->enter_game();
}

function &get_player($condition)
{
	return $GLOBALS['g']->get_player($condition);
}

function player_data_preprocess(&$data){
	return $GLOBALS['g']->player_data_preprocess($data);
}

function player_data_postprocess(&$data){
	return $GLOBALS['g']->player_data_postprocess($data);
}

function determine($threshold, $max = false)
{
	global $g;
	if($max === false){
		return $g->determine($threshold);
	}else{
		return $g->determine($threshold, $max);
	}
}

function random($min = false, $max = false)
{
	global $g;
	if($min === false){
		return $g->random();
	}else if($max === false){
		return $g->random($min);
	}else{
		return $g->random($min, $max);
	}
}

?>