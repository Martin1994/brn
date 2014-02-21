<?php

$GAME_CLASS = 'game_thbr';
$COMMAND_CLASS = 'command_thbr';
$PLAYER_CLASS = 'player_thbr';
$COMBAT_CLASS = 'combat_thbr';
$ITEM_CLASS = 'item_thbr';

include(get_mod_path('bra').'/class.player.php');
include(get_mod_path('bra').'/class.item.php');
include(get_mod_path('bra').'/class.combat.php');
include(get_mod_path('bra').'/class.game.php');
include(get_mod_path('bra').'/class.command.php');

include(get_mod_path('thbr').'/class.player.php');
include(get_mod_path('thbr').'/class.item.php');
include(get_mod_path('thbr').'/class.combat.php');
include(get_mod_path('thbr').'/class.game.php');
include(get_mod_path('thbr').'/class.command.php');

include(ROOT_DIR.'/gamedata/settings.bra.php');
include(ROOT_DIR.'/gamedata/settings.thbr.php');

?>