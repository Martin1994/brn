<?php

include(ROOT_DIR.'/include/class.comet.memcache.php');

class chlorocomet_memcache_SAE extends chlorocomet_memcache implements IChloroComet
{
	
	protected $mc;
	
	public function __construct(array $config)
	{
		
		$mc = memcache_init();
		
		$this->mc = $mc;
		
		return;
	}
	
}
	
?>