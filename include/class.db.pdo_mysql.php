<?php

include(ROOT_DIR.'/include/class.db.pdo.php');

class chlorodb_pdo_mysql extends chlorodb_pdo implements IChloroDB
{
	
	protected $db_type = 'mysql';
	
}