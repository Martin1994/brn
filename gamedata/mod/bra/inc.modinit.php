<?php

$GAME_CLASS = 'game_bra';
$COMMAND_CLASS = 'command_bra';
$PLAYER_CLASS = 'player_bra';
$COMBAT_CLASS = 'combat_bra';
$ITEM_CLASS = 'item_bra';

include(get_mod_path('bra').'/class.player.php');
include(get_mod_path('bra').'/class.item.php');
include(get_mod_path('bra').'/class.combat.php');
include(get_mod_path('bra').'/class.game.php');
include(get_mod_path('bra').'/class.command.php');

include(ROOT_DIR.'/gamedata/settings.bra.php');

?>