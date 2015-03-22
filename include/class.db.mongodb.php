<?php

class chlorodb_mongodb implements IChloroDB
{
	
	protected $query_time = 0;
	protected $table_prefix = '';
	protected $db_m;
	protected $db_s;
	
	public function __construct($user, $pass, $name, $host_m, $host_s = false, $persistent = false)
	{
		$m = new MongoClient('mongodb://'.$user.':'.$pass.'@'.$host_m);
		$this->db_m = $m->selectDB($name);
		
		if(false === $host_s){
			$this->db_s = $this->db_m;
		}else{
			$m = new MongoClient('mongodb://'.$user.':'.$pass.'@'.$host_s);
			$this->db_s = $m->selectDB($name);
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
		
		$table = $this->parse_table($table);
		$column = $this->parse_column($column);
		$where = $this->parse_where($where);
		
		$cursor = $db->selectCollection($table)->find($where, $column);
		$this->query_time ++;
		
		if(false !== $order){
			$cursor = $cursor->sort($order);
		}
		
		if(false != $limit){
			$limit = $this->parse_limit($limit);
			$cursor = $cursor->limit($limit[1])->skip($limit[0]);
		}
		
		$result = array();
		foreach($cursor as $key => $value){
			if(isset($value['_id'])){
				$value['_id'] = strval($value['_id']);
			}
			$result[] = $value;
		}
		if(sizeof($result) === 0){
			$result = false;
		}
		
		return $result;
	}
	
	public function update($table, array $data, $where = false, $limit = true)
	{
		$db = $this->db_m;
		$table = $this->parse_table($table);
		$where = $this->parse_where($where);
		if(isset($data['_id'])){
			unset($data['_id']);
		}
		
		$result = $db->selectCollection($table)->update($where, array('$set' => $data), array('multi' => !$limit));
		$this->query_time ++;
		
		return $result;
	}
	
	public function batch_update($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return false;
		}
		$db = $this->db_m;
		$table = $this->parse_table($table);
		$ids = array();
		foreach($data as &$row){
			$row['_id'] = new MongoId($row['_id']);
			array_push($ids, $row['_id']);
		}
		$collection = $db->selectCollection($table);
		$collection->remove(array('_id' => array('$in' => $ids)));
		$result = $collection->batchInsert($data);
		$this->query_time ++;
		return $result;
	}
	
	public function insert($table, array $data)
	{
		$db = $this->db_m;
		$table = $this->parse_table($table);
		$result = $db->selectCollection($table)->insert($data);
		$this->query_time ++;
		return $result;
	}
	
	public function batch_insert($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return false;
		}
		$db = $this->db_m;
		$table = $this->parse_table($table);
		$result = $db->selectCollection($table)->batchInsert($data);
		$this->query_time ++;
		return $result;
	}
	
	public function delete($table, $where = false, $limit = true)
	{
		$db = $this->db_m;
		$table = $this->parse_table($table);
		$where = $this->parse_where($where);
		
		$result = $db->selectCollection($table)->remove($where, array('justOne' => ($limit == true)));
		$this->query_time ++;
		return $result;
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
		$table = $this->parse_table($table);
		
		$db->dropCollection($table);
		$this->query_time ++;
		return $db->createCollection($table);
	}
	
	public function count($table, $where = false)
	{
		$db = $this->db_s;
		if($where){
			return $db->selectCollection($this->parse_table($table))->count($this->parse_where($where));
		}else{
			return $db->selectCollection($this->parse_table($table))->count();
		}
	}
	
	public function get_query_time()
	{
		return $this->query_time;
	}
	
	protected function parse_table($table)
	{
		return $this->table_prefix.$table;
	}
	
	protected function parse_column($column)
	{
		if(false === is_array($column)){
			if($column === '*'){
				return array();
			}else{
				return array($column => 1);
			}
		}
		
		return $column;
	}
	
	protected function parse_where($where)
	{
		if(false === $where){
			return array();
		}
		
		/*
		if(isset($where['_id']) && is_string($where['_id'])){
			$where['_id'] = new MongoId($where['_id']);
		}
		*/
		
		$where = $this->convert_id($where);
		
		return $where;
	}
	
	protected function convert_id($array, $is_id = false){
		foreach($array as $key => $value){
			if($is_id || $key === '_id'){
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, true);
				}else{
					$array[$key] = new MongoId($value);
				}
			}else{
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, false);
				}
			}
		}
		
		return $array;
	}
	
	protected function parse_limit($limit)
	{
		if(false === is_array($limit)){
			return array(0 => 0, 1 => intval($limit));
		}else{
			return $limit;
		}
	}
	
}