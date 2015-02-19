<?php

class chlorocomet_file implements IChloroComet
{
	
	protected $directory = '';
	protected $self = -1;
	protected $ajax = false;
	
	public function __construct(array $config)
	{
		if(false === isset($config['file'])){
			return throw_error('COMET ERROR: Invaild Configuration.');
		}
		
		$this->directory = $config['file']['dir'];
		
		if(false === is_dir($this->directory)){
			mkdir($this->directory, 0777, true);
		}
		
		return;
	}
	
	public function set_self($hash, $ajax)
	{
		$this->self = strval($hash);
		$this->ajax = $ajax;
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
		
		$fp = fopen($this->get_uri($hash), 'a');
		fwrite($fp, json_encode($value, JSON_FORCE_OBJECT).',');
		fclose($fp);
		return true;
	}
	
	public function add_all(array $value, $exception = false)
	{
		if(false === is_array($exception)){
			$exception = array();
		}
		
		if($handle = opendir($this->directory)){
			while(false !== ($hash = readdir($handle))){
				if($hash != "." && $hash != ".."){
					$hash = substr($hash, 0, strlen($hash) - 4); //去掉".txt"
					if(false === in_array($hash, $exception)){
						$this->add($hash, $value);
					}
				}
			}
		}
		return;
	}
	
	public function clear($hash, $delete = false)
	{
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->clear(strval($subhash), $value);
			}
			return;
		}
		
		if($delete){
			return unlink($this->get_uri($hash));
		}else{
			return file_put_contents($this->get_uri($hash), '');
		}
	}
	
	public function create($hash){
		if(true === is_array($hash)){
			foreach($hash as $subhash){
				$this->create(strval($subhash), $value);
			}
			return;
		}
		
		$fp = fopen($this->get_uri($hash), 'a');
		fclose($fp);
		
		return true;
	}
	
	public function query($hash){
		$content = file_get_contents($this->get_uri($hash));
		if($content != ''){
			file_put_contents($this->get_uri($hash), '');
			return '['.rtrim($content, ',').']';
		}else{
			return false;
		}
	}
	
	public function clear_all()
	{
		if(false === is_dir($this->directory)){
			mkdir($this->directory, 0777, true);
		}
		if($handle = opendir($this->directory)){
			while(false !== ($item = readdir($handle))){
				if($item != "." && $item != ".."){
					unlink($this->directory.'/'.$item);
				}
			}
		}
		
		return true;
	}
	
	protected function get_uri($hash)
	{
		return $this->directory.'/'.$hash.'.txt';
	}
	
}
	
?>