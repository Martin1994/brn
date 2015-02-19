<?php

class chlorocomet_memcache implements IChloroComet
{
	
	protected $mc;
	protected $prefix = '';
	protected $self = -1;
	protected $ajax = false;
	
	public function __construct(array $config)
	{
		if(false === isset($config['memcache'])){
			return throw_error('COMET ERROR: Invaild Configuration.');
		}
		
		$config = $config['memcache'];
		if(false === isset($config['host'])){
			$config['host'] = '127.0.0.1';
		}
		if(false === isset($config['port'])){
			$config['port'] = 11211;
		}
		if(false !== isset($config['prefix'])){
			$this->prefix = $config['prefix'];
		}
		
		$mc = new Memcache;
		$mc->connect($config['host'], $config['port']);
		
		$this->mc = $mc;
		
		return;
	}
	
	public function set_self($hash, $ajax)
	{
		$this->self = strval($hash);
		$this->ajax = $ajax;
	}
	
	public function add($hash, array $value)
	{
		$mc = $this->mc;
		
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
		
		$content = $mc->get($hash);
		return $mc->set($hash, $content.json_encode($value, JSON_FORCE_OBJECT).',');
	}
	
	public function add_all(array $value, $exception = false)
	{
		$mc = $this->mc;
		
		if(false === is_array($exception)){
			$exception = array();
		}
		
		$list = $mc->get($this->prefix.'userlist');
		if($list === false){
			$this->rebuild();
			$list = $mc->get($this->prefix.'userlist');
		}
		$list = json_decode($list);
		foreach($list as $key){
			if(false === in_array($key, $exception)){
				$this->add($key, $value);
			}
		}
		
		return;
	}
	
	public function clear($hash, $delete = false)
	{
		$mc = $this->mc;
		
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->clear(strval($subhash));
			}
			return;
		}
		
		$hash = $this->hash($hash);
		
		if($delete){
			return $mc->delete($hash);
		}else{
			return $mc->set($hash, '');
		}
	}
	
	public function create($hash){
		$mc = $this->mc;
		
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->create(strval($subhash));
			}
			return;
		}
		
		$list = $mc->get($this->prefix.'userlist');
		if($list == false){
			$list = array();
		}else{
			$list = json_decode($list);
		}
		$list[] = strval($hash);
		$mc->set($this->prefix.'userlist', json_encode($list));
		
		$hash = $this->hash($hash);
		
		return $mc->set($hash, '');
	}
	
	public function query($hash){
		$mc = $this->mc;
		
		$hash = $this->hash($hash);
		
		$content = $mc->get($hash);
		if($content){
			$mc->set($hash, '');
			return '['.rtrim($content, ',').']';
		}else{
			return false;
		}
	}
	
	public function clear_all()
	{
		$mc = $this->mc;
		
		$mc->set($this->prefix.'userlist', json_encode(array()));
		
		return true;
	}
	
	public function hash($key)
	{
		return $this->prefix.md5(strval($key));
	}
	
	protected function rebuild()
	{
		global $db;
		$players = $db->select('players', 'uid', array('type' => 1));
		foreach($players as $player){
			$this->create($player['uid']);
		}
	}
	
}
	
?>