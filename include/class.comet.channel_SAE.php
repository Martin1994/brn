<?php

class chlorocomet_channel_SAE implements IChloroComet
{
	
	protected $channel;
	protected $cache_prefix = '';
	protected $expired = 3600;
	protected $self = -1;
	protected $ajax = false;
	
	public function __construct(array $config)
	{
		$this->channel = new SaeChannel();
		$this->cache_prefix = $config['channel_SAE']['cache_prefix'];
		$this->expired = $config['channel_SAE']['expired_time'];
		
		return;
	}
	
	public function set_self($hash, $ajax)
	{
		$this->self = strval($hash);
		$this->ajax = $ajax;
	}
	
	public function set($hash, array $value)
	{
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->set(strval($subhash), $value);
			}
			return;
		}
		
		$hash = $this->hash($hash);
		
		return $this->channel->sendMessage($hash, json_encode($value, JSON_FORCE_OBJECT));
	}
	
	public function add($hash, array $value)
	{
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->add(strval($subhash), $value);
			}
			return;
		}
		
		if($hash === true){
			$this->add_all($value);
			return;
		}
		
		if(strval($hash) === $this->self){
			$this->ajax->push($value);
			return;
		}
		
		$hash = $this->hash($hash);
		
		return $this->channel->sendMessage($hash, json_encode($value, JSON_FORCE_OBJECT));
	}
	
	public function add_all(array $value, $exception = false)
	{
		if(false === is_array($exception)){
			$exception = array();
		}
		
		$list = cache_read($this->cache_prefix.'userlist');
		if($list === false){
			$this->rebuild();
			$list = cache_read($this->cache_prefix.'userlist');
		}
		$list = json_decode($list, true);
		foreach($list as $key => $v){
			if(false === in_array($key, $exception)){
				$this->add($key, $value);
			}
		}
		
		return;
	}
	
	public function clear($hash, $delete = false)
	{
		return true;
	}
	
	public function create($hash){
		$url = $this->channel->createChannel($this->hash($hash), $this->expired);
		
		$userlist = json_decode(cache_read($this->cache_prefix.'userlist'), true);
		$userlist[$hash] = $url;
		cache_write(
			$this->cache_prefix.'userlist',
			json_encode($userlist, JSON_FORCE_OBJECT)
			);
		
		return true;
	}
	
	public function query($hash){
		return false;
	}
	
	public function clear_all()
	{
		cache_write($this->cache_prefix.'userlist', json_encode(array()));
		
		return true;
	}
	
	public function hash($key)
	{
		return 'comet-'.strval($key);
	}
	
	protected function rebuild()
	{
		global $db;
		$players = $db->select('players', 'uid', array('type' => 1));
		foreach($players as $player){
			$this->create($player['uid']);
		}
	}
	
	public function get_url($hash)
	{
		$userlist = cache_read($this->cache_prefix.'userlist');
		if(false === $userlist){
			$this->rebuild();
			$list = cache_read($this->cache_prefix.'userlist');
		}
		
		$userlist = json_decode($userlist, true);
		if(false === isset($userlist[$hash])){
			$this->rebuild();
			$list = json_decode(cache_read($this->cache_prefix.'userlist'), true);
		}
		
		
		return $userlist[$hash];
	}
	
}
	
?>