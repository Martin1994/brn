<?php

class chlorocomet_mongodb implements IChloroComet
{
	
	protected $collection;
	protected $cursor = array();
	protected $self = -1;
	protected $ajax = false;
	
	public function __construct(array $config)
	{
		if(false === isset($config['mongodb'])){
			return throw_error('COMET ERROR: Invaild Configuration.');
		}
		
		$db = new Mongo('mongodb://'.$config['mongodb']['user'].':'.$config['mongodb']['pass'].'@'.$config['mongodb']['host']);
		$this->collection = $db->selectDB($config['mongodb']['db'])->selectCollection($config['mongodb']['collection']);
		
		return;
	}
	
	public function set_self($hash, $ajax)
	{
		$this->self = strval($hash);
		$this->ajax = $ajax;
	}
	
	public function set($hash, array $value)
	{
		if(false === is_array($hash)){
			$hash = array($hash);
		}
		
		return $this->collection->update(
			array('hash' => array('$in' => $hash)),
			array('$set' => array('value' => array($value)))
			);
	}
	
	public function add($hash, array $value)
	{
		if($hash === true){
			$this->add_all($value);
			return;
		}else{
			if(false === is_array($hash)){
				$hash = array($hash);
			}
			$key = array_search($this->self, $hash);
			if($key !== false){
				$this->ajax->push($value);
				unset($hash[$key]);
			}
		}
		if(sizeof($hash) === 0){
			return;
		}
		
		return $this->collection->update(
			array('hash' => array('$in' => $hash)),
			array('$push' => array('value' => $value))
			);
	}
	
	public function add_all(array $value, $exception = false)
	{
		if(false === is_array($exception)){
			$exception = array();
		}
		
		if($this->self !== -1 && false === in_array($this->self, $exception)){
			$this->ajax->push($value);
			$exception[] = $this->self;
		}
		
		$condition = array('hash' => array('$nin' => $exception));
		
		return $this->collection->update(
			$condition,
			array('$push' => array('value' => $value))
			);
	}
	
	public function clear($hash, $delete = false)
	{
		if(false === is_array($hash)){
			$hash = array($hash);
		}
		
		if($delete){
			return $this->collection->remove(
				array('hash' => array('$in' => $hash))
				);
		}else{
			return $this->collection->update(
				array('hash' => array('$in' => $hash)),
				array('$set' => array('value' => array()))
				);
		}
	}
	
	public function create($hash){
		if(false !== is_array($hash)){
			foreach($hash as $subhash){
				$this->create($hash);
			}
			return true;
		}
		
		return $this->collection->insert(array(
			'hash' => $hash,
			'value' => array()
			));
	}
	
	public function query($hash){
		$result = $this->collection->findOne(array('hash' => $hash));
		if($result !== false){
			if(false === isset($result['value']) || sizeof($result['value']) === 0){
				return false;
			}else{
				$this->clear($hash);
				return json_encode($result['value']);
			}
		}else{
			return false;
		}
		
	}
	
	public function clear_all()
	{
		return $this->collection->remove(array());
	}
	
}
	
?>