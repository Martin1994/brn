(
  _id smallint unsigned NOT NULL auto_increment,
  kind tinyint unsigned NOT NULL default '0',
  num smallint unsigned NOT NULL default '0',
  price smallint unsigned NOT NULL default '0',
  area tinyint unsigned NOT NULL default '0',
  itm char(30) NOT NULL default '',
  itmk char(5) NOT NULL default '',
  itme smallint unsigned NOT NULL default '0',
  itms char(5) NOT NULL default '0',
  itmsk char(255) NOT NULL default '{}',

  PRIMARY KEY  (_id),
  INDEX KIND (kind, area)
)