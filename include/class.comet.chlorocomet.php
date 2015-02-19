<?php

class chlorocomet_chlorocomet implements IChloroComet
{
	
	protected $host_url;
	protected $client_url;
	protected $host_id;
	protected $host_pass;
	protected $cache_prefix;
	protected $self = -1;
	protected $ajax = false;
	
	protected $request_option_template;
	
	public function __construct(array $config)
	{
		$this->host_url = trim($config['chlorocomet']['host'], '/');
		$this->host_id = $config['chlorocomet']['id'];
		$this->host_pass = $config['chlorocomet']['pass'];
		$this->cache_prefix = $config['chlorocomet']['cache_prefix'];

		$this->rebuilded = false;
		
		return;
	}
	
	private function request($action, $data)
	{
		$data['id'] = $this->host_id;
		$data['pass'] = $this->host_pass;
		$options = array(
			'http' => array(
				'method' => 'POST',
				'header' => 'Content-Type: application/x-www-form-urlencoded',
				'content' => http_build_query($data)
			));
		$context = stream_context_create($options);
		$result = file_get_contents($this->host_url.'/api/'.$action, false, $context);
		$result = json_decode($result, true);

		if($result == null){
			throw new Exception('ChloroComet Error: Server is not available.');
		}

		if(!$result['success'] && !$this->rebuilded){
			$this->rebuild();
			throw new Exception('ChloroComet Error: '.$result['error']);
		}
		return $result;
	}
	
	public function set_self($clientid, $ajax)
	{
		$this->self = strval($clientid);
		$this->ajax = $ajax;
	}
	
	public function add($clientid, array $value)
	{
		if(true === is_array($clientid)){
			foreach($clientid as $subid){
				$this->add(strval($subid), $value);
			}
			return;
		}
		
		if(strval($clientid) === $this->self){
			$this->ajax->push($value);
			return;
		}
		
		$data = array(
			'client_id' => $clientid,
			'message' => json_encode($value, JSON_FORCE_OBJECT)
			);
		$result = $this->request('send', $data);
		
		return $result['success'];
	}
	
	public function add_all(array $value, $exception = false)
	{
		if(false === is_array($exception)){
			$exception = array();
		}
		
		$list = cache_read($this->cache_prefix.'userlist');
		$list = json_decode($list, true);
		foreach($list as $clientid => $v){
			if(false === in_array($clientid, $exception)){
				$this->add($clientid, $value);
			}
		}
		
		return true;
	}
	
	public function clear($clientid, $delete = false)
	{
		if($delete){
			$data = array('client_id' => $clientid);
			$result = $this->request('removeClient', $data);

			return $result['success'];
		}
		return true;
	}
	
	public function create($clientid){
		$data = array('client_id' => $clientid);
		$result = $this->request('addClient', $data);
		
		$userlist = json_decode(cache_read($this->cache_prefix.'userlist'), true);
		$userlist[$clientid] = $result['token'];
		cache_write(
			$this->cache_prefix.'userlist',
			json_encode($userlist, JSON_FORCE_OBJECT)
			);
		
		return $result['success'];
	}
	
	public function query($clientid){
		return false;
	}
	
	public function clear_all()
	{
		$data = array();
		$result = $this->request('removeAllClients', $data);

		cache_write($this->cache_prefix.'userlist', json_encode(array()));
		
		return $result['success'];
	}

	protected function rebuild()
	{
		$this->rebuilded = true;
		$userlist = json_decode(cache_read($this->cache_prefix.'userlist'), true);

		if($userlist == null){
			// Cache is not available, regenerate user list
			global $db;
			$userlist = array();
			$active_player = $db->select('players', array('uid'), array('type' => GAME_PLAYER_USER));
			foreach($active_player as $player){
				$userlist[$player['uid']] = '';
			}
		}

		$this->clear_all();
		foreach($userlist as $id => $token){
			$this->create($id);
		}
	}
	
	public function get_token($clientid)
	{
		$userlist = cache_read($this->cache_prefix.'userlist');
		
		$userlist = json_decode($userlist, true);
		
		return $userlist[$clientid];
	}
	
}
	
?>