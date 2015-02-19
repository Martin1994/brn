(
  _id mediumint unsigned NOT NULL auto_increment,
  itm varchar(30) NOT NULL default '',
  itmk varchar(5) not null default '',
  itme mediumint unsigned NOT NULL default '0',
  itms mediumint not null default '0',
  itmsk varchar(255) not null default '',
  area tinyint unsigned not null default '0',
  
  PRIMARY KEY  (_id),
  INDEX AREA (area)
)