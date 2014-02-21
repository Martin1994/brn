(
  `_id` mediumint(8) unsigned NOT NULL auto_increment,
  `gamenum` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `type` char(15) NOT NULL default '',
  `time` int(10) not null default '0',
  `winner_info` varchar(1000) not null default '[]',
  `winners` text not null,
  `news` text not null,
  
  PRIMARY KEY  (_id)
)