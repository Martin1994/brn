(
  _id mediumint unsigned NOT NULL auto_increment,
  name varchar(32) NOT NULL default '',
  pass varchar(32) NOT null default '',
  
  PRIMARY KEY  (_id),
  UNIQUE KEY `name` (`name`)
)