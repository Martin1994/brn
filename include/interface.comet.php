<?php
interface IChloroComet
{
	public function __construct(array $config);
	
	public function set_self($hash, $ajax);
	
	public function query($hash);
	
	public function clear_all();
	
	public function set($hash, array $value);
	
	public function add($hash, array $value);
	
	public function clear($hash, $delete);
	
	public function create($hash);
}
?>