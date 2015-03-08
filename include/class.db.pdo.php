<?php

class chlorodb_pdo implements IChloroDB
{
	
	protected $query_time = 0;
	protected $table_prefix = '';
	protected $db_m;
	protected $db_s;
	protected $db_type = '';
	protected $json_flag = 0;
	
	public function __construct($user, $pass, $name, $host_m, $host_s = false, $persistent = false)
	{
		$connection_param = array();
		
		if($persistent){
			$connection_param[PDO::ATTR_PERSISTENT] = true;
		}
		
		try{
			$this->db_m = new PDO($this->db_type.':host='.$host_m.';dbname='.$name.';charset=utf8', $user, $pass, $connection_param);
		}catch(PDOException $e){
			throw_error($e->getMessage());
		}
		
		$this->db_m->exec('set names utf8');
		
		if(false === $host_s){
			$this->db_s = $this->db_m;
		}else{
			try{
				$this->db_s = new PDO($this->db_type.':host='.$host_s.';dbname='.$name.';charset=utf8', $user, $pass, $connection_param);
			}catch(PDOException $e){
				throw_error($e->getMessage());
			}
			mysql_set_charset('utf8', $this->db_s);
			$this->db_s->exec('set names utf8');
		}
		
		if(defined('JSON_UNESCAPED_UNICODE')){
			$this->json_flag += JSON_UNESCAPED_UNICODE;
		}
		
		return;
	}
	
	public function set_table_prefix($table_prefix)
	{
		$this->table_prefix = $table_prefix;
	}
	
	public function select($table, $column, $where = false, $limit = 0, $order = false)
	{
		$db = $this->db_s;
		
		$params = array();
		
		$column = $this->parse_column($params, $column);
		$table = $this->parse_table($params, $table);
		
		$qry = 'SELECT '.$column.' FROM '.$table;
		
		if(false != $where){
			$where = $this->parse_where($params, $where);
			$qry .= ' WHERE '.$where;
		}
		
		if(false !== $order){
			$order = $this->parse_order($params, $order);
			$qry .= ' ORDER BY '.$order;
		}
		
		if(false != $limit){
			$limit = $this->parse_limit($params, $limit);
			$qry .= ' LIMIT '.$limit;
		}
		
		$qry .= ';';
		return $this->query($qry, $params, $db);
	}
	
	public function update($table, array $data, $where = false, $limit = true)
	{
		$db = $this->db_m;
		
		$params = array();
		
		$table = $this->parse_table($params, $table);
		$data = $this->parse_data($params, $data);
		
		$qry = 'UPDATE '.$table.' SET'.$data;
		
		if(false != $where){
			$where = $this->parse_where($params, $where);
			$qry .= ' WHERE '.$where;
		}
		
		if(true === $limit){
			$limit = $this->parse_limit($params, 1);
			$qry .= ' LIMIT '.$limit;
		}
		
		$qry .= ';';
		return $this->query($qry, $params, $db);
	}
	
	public function batch_update($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return true;
		}
		if($matrix){
			$db = $this->db_m;
			
			$params = array();
			
			$table = $this->parse_table($params, $table);
			
			if(false === is_array(reset($data))){
				return throw_error('MySQL Class Param Error: when updating mulitiple rows, each elements of data should be an array.');
			}
			
			$column = '(';
			foreach(reset($data) as $key => $value){
				$column .= '`'.$key.'`,';
			}
			$column = rtrim($column, ',').')';
			$width = sizeof(reset($data));
			
			$qry = 'INSERT INTO '.$table.' '.$column.' VALUES ';
			
			foreach($data as $row){
				if(false === is_array($row)){
					return throw_error('MySQL Class Param Error: when inserting mulitiple rows, each elements of data should be an array.');
				}
				if(sizeof($row) !== $width){
					return throw_error('MySQL Class Param Error: elements of martix multiple inserting should be the same size.');
				}
				$values = '(';
				foreach($row as $value){
					if($key === '_id'){
						$values .= $value.',';
					}else if(true === is_array($value)){
						$params[] = json_encode($value, $this->json_flag);
					}else{
						$params[] = $value;
					}
					$values .= '?,';
				}
				$values = rtrim($values, ',').')';
				$qry .= $values.',';
			}
			
			$qry = rtrim($qry, ',');
			
			$qry .= ' ON DUPLICATE KEY UPDATE ';
			foreach(reset($data) as $key => $value){
				if($key !== '_id'){
					$qry .= '`'.$key.'`=VALUES(`'.$key.'`),';
				}
			}
			
			return $this->query(rtrim($qry, ',').';', $params, $db);
		}else{
			foreach($data as $value){
				$this->insert($table, $value);
			}
			return;
		}
	}
	
	public function insert($table, array $data)
	{
		$db = $this->db_m;
		
		$params = array();
		
		
		$table = $this->parse_table($params, $table);
		$data = $this->parse_data($params, $data);
		$qry = 'INSERT INTO '.$table.' SET'.$data.';';
		return $this->query($qry, $params, $db);
	}
	
	public function batch_insert($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return true;
		}
		if($matrix){
			$db = $this->db_m;
			
			$params = array();
			
			$table = $this->parse_table($params, $table);
			
			if(false === is_array($data[0])){
				return throw_error('MySQL Class Param Error: when inserting mulitiple rows, each elements of data should be an array.');
			}
			
			$column = '(';
			foreach($data[0] as $key => $value){
				$column .= '`'.$key.'`,';
			}
			$column = rtrim($column, ',').')';
			$width = sizeof($data[0]);
			
			$qry = 'INSERT INTO '.$table.' '.$column.' VALUES ';
			
			foreach($data as $row){
				if(false === is_array($row)){
					return throw_error('MySQL Class Param Error: when inserting mulitiple rows, each elements of data should be an array.');
				}
				if(sizeof($row) !== $width){
					return throw_error('MySQL Class Param Error: elements of martix multiple inserting should be the same size.');
				}
				$values = '(';
				foreach($row as $value){
					if(true === is_array($value)){
						$params[] = json_encode($value, $this->json_flag);
					}else{
						$params[] = $value;
					}
					$values .= '?,';
				}
				$values = rtrim($values, ',').')';
				$qry .= $values.',';
			}
			
			return $this->query(rtrim($qry, ',').';', $params, $db);
		}else{
			foreach($data as $value){
				$this->insert($table, $value);
			}
			return;
		}
	}
	
	public function delete($table, $where = false, $limit = true)
	{
		$db = $this->db_m;
		
		$params = array();
		
		
		$table = $this->parse_table($params, $table);
		
		$qry = 'DELETE FROM '.$table;
		
		if(false != $where){
			$where = $this->parse_where($params, $where);
			$qry .= ' WHERE '.$where;
		}
		
		if(true === $limit){
			$limit = $this->parse_limit($params, 1);
			$qry .= ' LIMIT '.$limit;
		}
		
		$qry .= ';';
		return $this->query($qry, $params, $db);
	}
	
	public function create_table($table, $column)
	{
		if(true === is_array($table)){
			foreach($table as $subtable){
				$this->create_table(strval($subtable), $column);
			}
			return;
		}
		
		$db = $this->db_m;
		
		$params = array();
		
		
		$table = $this->parse_table($params, $table);
		
		$qry = 'DROP TABLE IF EXISTS '.$table.';';
		$this->query($qry, $params, $db);
		
		$column = $this->parse_column($params, $column, true);
		
		$qry = 'CREATE TABLE '.$table.' '.$column.'ENGINE=MyISAM CHARACTER SET utf8 DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT COLLATE utf8_general_ci;';
		return $this->query($qry, $params, $db);
	}
	
	public function count($table, $where = false)
	{
		$db = $this->db_s;
		
		$params = array();
		
		$qry = 'SELECT COUNT(*) FROM '.$this->parse_table($params, $table);
		
		if($where){
			$qry .= ' WHERE '.$this->parse_where($params, $where);
		}
		
		$qry .= ';';
		
		$result = $this->query($qry, $params, $db);
		
		return intval($result[0]['COUNT(*)']);
	}
	
	public function get_query_time()
	{
		return $this->query_time;
	}
	
	protected function query($query_string, &$params = array(), $db = false)
	{
		$this->query_time += 1;
		
		if(false === $db){
			$db = $this->db_m;
		}
		
		//echo $query_string, '<br />';
		
		try{
			$statement = $db->prepare($query_string);
			$exec = $statement->execute($params);
			if($exec === false){
				return throw_error('PDO query error | Query string: '.$query_string.' | Query params: '.var_export($params, true));
			}
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		}catch(PDOException $e){
			throw_error($e->getMessage().' | Query string: '.$query_string);
		}
		
		if(strtoupper(substr($query_string, 0, 6)) != 'SELECT'){
			return true;
		}
		
		if(sizeof($result) == 0){
			return false;
		}
		$data = $result;
		foreach($data as &$record){
			foreach($record as $key => $value){
				if( (substr($value, 0, 1) === '{' && substr($value, -1, 1) === '}') || (substr($value, 0, 1) === '[' && substr($value, -1, 1) === ']') ){
					$record[$key] = json_decode($value, true);
				}
			}
		}
		return $data;
	}
	
	protected function parse_column(&$params, $column, $increate = false)
	{
		if($increate){
			/*if(false === is_array($column)){
				return throw_error('MySQL Class Param Error: Invaild column information when creating table');
			}
			
			$result = '(';
			foreach($column as $value){
				$result .= $value['name'].' '.$value['type'];
			}*/
			return $column;
		}else{
			if(false === is_array($column)){
				return preg_replace("[^\w\*]", '', $column);
			}
			
			$result = '';
			foreach($column as $value){
				$result .= preg_replace("[\W]", '', $value).',';
			}
			return substr($result, 0, strlen($result)-1);
		}
	}
	
	protected function parse_table(&$params, $table)
	{
		return '`'.preg_replace("[\W]", '', $this->table_prefix.$table).'`';
	}
	
	protected function parse_data(&$params, $data)
	{
		if(false === is_array($data)){
			throw_error('MySQL Class Param Error: Data must be an array.');
			return;
		}
		$result = '';
		foreach($data as $key => $value){
			$result .= ' `'.$key.'` = ?';
			if(true === is_array($value)){
				$params[] = json_encode($value, $this->json_flag);
			}else{
				$params[] = $value;
			}
			$result .= ',';
		}
		return substr($result, 0, strlen($result)-1);
	}
	
	protected function parse_text($text){
		$text = str_replace("\\", "\\\\", $text); //SQL will escape single \
		$text = str_replace('"', "\\\"", $text); //SQL query use ""
		return $text;
	}
	
	protected function parse_where(&$params, $where)
	{
		if(false === is_array($where)){
			throw_error('MySQL Class Param Error: Condition must be an array.');
			return;
		}
		
		/*
		if(isset($where['_id'])){
			$where['_id'] = intval($where['_id']);
		}
		*/
		
		$where = $this->convert_id($where);
		
		$result = '';
		foreach($where as $key => $value){
			switch(strtolower($key)){
				case '$or':
					if(false === is_array($value)){
						throw_error('MySQL Class Param Error: Subcondition must be an array.');
					}
					
					$result .= '(';
					foreach($value as $subvalue){
						$result .= '('.$this->parse_where($subvalue).') OR ';
					}
					$result = substr($result, 0, strlen($result) - 4).')';
					break;
				
				case '$and':
					if(false === is_array($value)){
						throw_error('MySQL Class Param Error: Subcondition must be an array.');
					}
					
					$result .= '(';
					foreach($value as $subvalue){
						$result .= '('.$this->parse_where($subvalue).') AND ';
					}
					$result = substr($result, 0, strlen($result) - 5).')';
					break;
				
				default:
					if(false === is_array($value)){
						$result .= '`'.preg_replace("[\W]", '', $key).'`';
						$result .= ' = ?';
						$params[] = $value;
					}else{
						foreach($value as $subkey => $subvalue){
							$result .= '`'.preg_replace("[\W]", '', $key).'`';
							switch(strtolower($subkey)){
								case '$lt':
									$result .= ' < ?';
									$params[] = $subvalue;
									break;
								
								case '$gt':
									$result .= ' > ?';
									$params[] = $subvalue;
									break;
								
								case '$lte':
									$result .= ' <= ?';
									$params[] = $subvalue;
									break;
								
								case '$gte':
									$result .= ' >= ?';
									$params[] = $subvalue;
									break;
								
								case '$ne':
									$result .= ' != ?';
									$params[] = $subvalue;
									break;
								
								case '$in':
									if(sizeof($subvalue) == 0){
										$result .= ' = -1';
										break;
									}
									$result .= ' IN (';
									if(!is_array($subvalue)){
										throw_error('MySQL Class Param Error: Param of "IN" must be an array');
									}
									foreach($subvalue as $subsubvalue){
										$result .= '?,';
										$params[] = $subsubvalue;
									}
									$result = substr($result, 0, strlen($result) - 1).')';
									break;
								
								default:
									throw_error('MySQL Class Param Error: Unknown comparision symbol: '.$subkey);
									break;
							}
							
							$result .= ' AND ';
						}
						$result = substr($result, 0, strlen($result) - 5);
					}
					break;
			}
			$result .= ' AND ';
		}
		return substr($result, 0, strlen($result) - 5);
	}
	
	protected function parse_order(&$params, $order)
	{
		if(false === is_array($order)){
			throw_error('MySQL Class Param Error: Order must be an array.');
			return;
		}
		
		$result = '';
		foreach($order as $key => $value){
			switch($value){
				case 1:
					$sc = 'ASC';
					break;
				
				case -1:
					$sc = 'DESC';
					break;
				
				default:
					$sc = 'ASC';
					break;
			}
			$result .= '`'.preg_replace("[\W]", '', $key).'` '.$sc.',';
		}
		return substr($result, 0, strlen($result)-1);
	}
	
	protected function parse_limit(&$params, $limit)
	{
		if(false === is_array($limit)){
			return preg_replace("[^0-9]", '', $limit);
		}
		
		$size = sizeof($limit);
		if($size !== 2){
			throw_error('MySQL Class Param Error: There must be exactly 2 elements in the array of limit param.');
			return;
		}
		
		return preg_replace("[^0-9]", '', $limit[0].','.$limit[1]);
	}
	
	protected function convert_id($array, $is_id = false){
		foreach($array as $key => $value){
			if($is_id || $key === '_id'){
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, true);
				}else{
					$array[$key] = intval($value);
				}
			}else{
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, false);
				}
			}
		}
		
		return $array;
	}
	
}