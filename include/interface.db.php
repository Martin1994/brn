<?php
//TODO: compatible with PHP 5.2
interface IChloroDB
{
	public function __construct($user, $pass, $name, $host_m, $host_s = false, $persistent = false);
	
	public function set_table_prefix($table_prefix);
	
	public function select($table, $column, $where, $limit, $order);
	
	public function update($table, array $data, $where, $limit);
	
	public function insert($table, array $data);
	
	public function batch_insert($table, array $data, $matrix);
	
	public function delete($table, $where, $limit);
	
	public function create_table($table, $column);
	
	public function count($table, $where = false);
	
	public function get_query_time();
}

?>