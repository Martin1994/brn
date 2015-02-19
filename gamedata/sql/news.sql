(
  _id mediumint unsigned NOT NULL auto_increment,
  `time` bigint unsigned NOT NULL default '0',
  content varchar(255) NOT null default '',
  
  PRIMARY KEY  (_id)
)