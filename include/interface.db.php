<?php

interface IChloroDB
{
	public function __construct($user, $pass, $name, $host_m, $host_s = false, $persistent = false);
	
	public function set_table_prefix($table_prefix);
	
	public function select($table, $column, $where, $limit = 0, $order = false);
	
	public function update($table, array $data, $where, $limit = true);
	
	public function batch_update($table, array $data);
	
	public function insert($table, array $data);
	
	public function batch_insert($table, array $data, $matrix);
	
	public function delete($table, $where, $limit = true);
	
	public function create_table($table, $column);
	
	public function count($table, $where = false);
	
	public function get_query_time();
}

?>