<?php

/* @return game */
function new_game()
{
	return new $GLOBALS['GAME_CLASS']();
}

/* @return command */
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
	return new $GLOBALS['ITEM_CLASS']($player, $data, $id);
}

/* @return combat */
function new_combat(player $attacker, player $defender)
{
	return new $GLOBALS['COMBAT_CLASS']($attacker, $defender);
}

?>