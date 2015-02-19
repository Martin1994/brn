(
  `gamenum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `gamestate` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0',
  `winmode` varchar(20) NOT NULL DEFAULT 'error',
  `winner` varchar(1000) NOT NULL DEFAULT '[]',
  `arealist` varchar(255) NOT NULL DEFAULT '[]',
  `forbiddenlist` varchar(255) NOT NULL DEFAULT '[]',
  `dangerouslist` varchar(255) NOT NULL DEFAULT '[]',
  `areatime` int(10) unsigned NOT NULL DEFAULT '0',
  `validnum` smallint(5) NOT NULL DEFAULT '0',
  `alivenum` smallint(5) NOT NULL DEFAULT '0',
  `deathnum` smallint(5) NOT NULL DEFAULT '0',
  `weather` smallint(5) NOT NULL DEFAULT '0',
  `round` smallint(5) NOT NULL DEFAULT '0',
  `hdamage` int(11) unsigned NOT NULL DEFAULT '0',
  `hplayer` varchar(15) NOT NULL DEFAULT '',
  `settings` varchar(15) NOT NULL DEFAULT 'default',
  
  PRIMARY KEY (`gamenum`)
)
