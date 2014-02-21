<?php

define('ROOT_DIR', dirname(__FILE__));
define('CONNECTION_MODE', 'normal');

include(ROOT_DIR.'/include/func.template.php');
include(ROOT_DIR.'/include/inc.init.php');

$starttime = $g->gameinfo['starttime'];
$gamenum = $g->gameinfo['gamenum'];
$round = $g->gameinfo['round'];
$gamestate = $g->gameinfo['gamestate'];
$hdamage = $g->gameinfo['hdamage'];
$hplayer = $g->gameinfo['hplayer'];
$winner = $g->gameinfo['winner'];
$winmode = $g->gameinfo['winmode'];

$index_page = true;

render_page('Index');

?>