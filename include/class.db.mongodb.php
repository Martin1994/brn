<?php

use MongoDB\BSON\ObjectID;
use MongoDB\Driver\BulkWrite;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\RuntimeException;
use MongoDB\Driver\Manager;
use MongoDB\Driver\Query;
use MongoDB\Driver\ReadPreference;

class chlorodb_mongodb implements IChloroDB
{
	
	protected $query_time = 0;
	protected $table_prefix = '';
	protected $manager;
	protected $server_m;
	protected $server_s;
	protected $db_name;
	
	public function __construct($user, $pass, $name, $host_m, $host_s = false, $persistent = false)
	{
		$url = 'mongodb://'.$user.':'.$pass.'@'.$host_m;
		if($host_s){
			$url .= ','.$host_s;
		}
		
		$this->manager = new Manager($url);
		
		$this->db_name = $name;
		
		$read_preference_m = new ReadPreference(ReadPreference::RP_PRIMARY_PREFERRED);
		$read_preference_s = new ReadPreference(ReadPreference::RP_SECONDARY_PREFERRED);		
		
		$this->server_m = $this->manager->selectServer($read_preference_m);
		$this->server_s = $this->manager->selectServer($read_preference_s);
		
		return;
	}
	
	public function set_table_prefix($table_prefix)
	{
		$this->table_prefix = $table_prefix;
	}
	
	public function select($table, $column, $where = false, $limit = 0, $order = false)
	{
		$table = $this->parse_table($table);
		$column = $this->parse_column($column);
		$where = $this->parse_where($where);
		
		$options = array(
			'partial' => true
		);
		if(sizeof($column) > 0){
			$options['projection'] = $column;
		}
		
		if(false !== $limit){
			$limit = $this->parse_limit($limit);
			if($limit[0] > 0){
				$options['skip'] = $limit[0];
			}
			if($limit[1] > 0){
				$options['limit'] = $limit[1];
			}
		}
		
		if(false !== $order){
			$options['sort'] = $order;			
		}
		
		$query = new Query($where, $options);
		$server = $this->server_s;
		$cursor = $server->executeQuery($this->db_name.'.'.$table, $query);
		
		$this->query_time ++;
		$cursor->setTypeMap(array(
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		));
		$result = $cursor->toArray();
		foreach($result as &$value){
			if(isset($value['_id'])){
				$value['_id'] = strval($value['_id']);
			}
		}
		return $result;
	}
	
	public function update($table, array $data, $where = false, $limit = true)
	{
		$table = $this->parse_table($table);
		$where = $this->parse_where($where);
		if(isset($data['_id'])){
			unset($data['_id']);
		}
		
        $options = array(
            'multi' => !$limit,
            'upsert' => false,
		);
		
		$data = unserialize(serialize($data));
		$bulk = new BulkWrite();
		$bulk->update($where, array('$set' => $data), $options);
		$server = $this->server_m;
		$result = $server->executeBulkWrite($this->db_name.'.'.$table, $bulk);
		$this->query_time ++;
		
		return $result;
	}
	
	public function batch_update($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return false;
		}
		
		$table = $this->parse_table($table);
		
        $delete_options = array('multi' => true);
		
		$data = unserialize(serialize($data));
		$bulk = new BulkWrite();
		$ids = array();
		foreach($data as &$row){
			$row['_id'] = new ObjectID($row['_id']);
			array_push($ids, $row['_id']);
		}
		$bulk->delete(array('_id' => array('$in' => $ids)), $delete_options);
		foreach($data as &$row){
			$bulk->insert($row);
		}
		$server = $this->server_m;
		$result = $server->executeBulkWrite($this->db_name.'.'.$table, $bulk);
		$this->query_time ++;
		return $result;
	}
	
	public function insert($table, array $data)
	{
		$table = $this->parse_table($table);
		
		$data = unserialize(serialize($data));
		$bulk = new BulkWrite();
		$bulk->insert($data);
		$server = $this->server_m;
		$result = $server->executeBulkWrite($this->db_name.'.'.$table, $bulk);
		$this->query_time ++;
		return $result;
	}
	
	public function batch_insert($table, array $data, $matrix = true)
	{
		if(sizeof($data) === 0){
			return false;
		}
		
		$table = $this->parse_table($table);
		
        $delete_options = array('multi' => true);
		
		$data = unserialize(serialize($data));
		$bulk = new BulkWrite();
		foreach($data as &$row){
			$bulk->insert($row);
		}
		$server = $this->server_m;
		$result = $server->executeBulkWrite($this->db_name.'.'.$table, $bulk);
		$this->query_time ++;
		return $result;
	}
	
	public function delete($table, $where = false, $limit = true)
	{
		$table = $this->parse_table($table);
		$where = $this->parse_where($where);
		
        $options = array('multi' => !$limit);
		
		$bulk = new BulkWrite();
		$bulk->delete($where, $options);
		$server = $this->server_m;
		$result = $server->executeBulkWrite($this->db_name.'.'.$table, $bulk);
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
		
		$server = $this->server_m;
		try{
			$cursor = $server->executeCommand($this->db_name, new Command(array('drop' => $table)));
		}catch(RuntimeException $ex){
			
		}
		$cursor = $server->executeCommand($this->db_name, new Command(array('create' => $table)));
		$cursor->setTypeMap(array(
			'root' => 'array',
			'document' => 'array',
			'array' => 'array'
		));
		$result = current($cursor->toArray());
		$this->query_time ++;
		return $result;
	}
	
	public function count($table, $where = false)
	{
		$table = $this->parse_table($table);
		
		$command = array('count' => $table);
		if($where){
			$command['query'] = $this->parse_where($where);
		}
		$server = $this->server_s;
		$cursor = $server->executeCommand($this->db_name, new Command($command));
		$result = current($cursor->toArray());
		return intval($result->n);
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
		}else{
			$result = array();
			foreach($column as $name){
				$result[$name] = 1;
			}
			return $result;
		}
	}
	
	protected function parse_where($where)
	{
		if(false === $where){
			return array();
		}
		
		$this->convert_id($where);
		
		return $where;
	}
	
	protected function convert_id(&$array, $is_id = false){
		foreach($array as $key => $value){
			if($is_id || $key === '_id'){
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, true);
				}else{
					$array[$key] = new ObjectID($value);
				}
			}else{
				if(is_array($value)){
					$array[$key] = $this->convert_id($value, false);
				}
			}
		}
	}
	
	protected function parse_limit($limit)
	{
		$parsed_limit = array();
		if(false === is_array($limit)){
			$parsed_limit[0] = 0;
			$parsed_limit[1] = intval($limit);
		}else{
			$parsed_limit = $limit;
		}
		return $parsed_limit;
	}
	
}