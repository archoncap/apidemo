<?php

class CacheRedis extends Cache {

	protected $redis;

	protected $is_connected = false;

	public function __construct() {
		$this->connect();

		$this->keys = array();
		if ($this->is_connected)
		{			
			$this->keys = array_flip($this->redis->keys(Yaf_Registry::get('config')->cache->prefix.'*'));
		}
	}

	public function __destruct() {
		$this->close();
	}

	public function connect() {
		if (extension_loaded('redis') && class_exists('Redis'))
		{
			$this->redis = new Redis();
		}
		else
		{
			return false;
		}
		$servers = self::getRedisServers();
		if (!$servers)
			return false;
		foreach ($servers as $server)
			$this->redis->connect($server['host'], $server['port']);
		$this->is_connected = true;
		
		$this->redis->auth(Yaf_Registry::get('config')->cache->redis->auth);
		return true;
	}

	protected function _set($key, $value, $ttl = 3600) {
		if (!$this->is_connected)
			return false;
		
		if($ttl>0){
			return $this->redis->setEx($key, $ttl, json_encode($value));
		}else{
			return $this->redis->set($key, json_encode($value));
		}
	}

	protected function _get($key) {
		if (!$this->is_connected)
			return false;		
		return json_decode($this->redis->get($key), TRUE);
	}
	
	protected function _expire($key, $ttl = 3600) {
		if (!$this->is_connected)
			return false;
		
		return $this->redis->expire($key, $ttl);
	}

	protected function _exists($key) {		
		if (!$this->is_connected)
			return false;
		
		return isset($this->keys[$key]);
	}

	protected function _delete($key) {
		if (!$this->is_connected)
			return false;

		return $this->redis->delete($key);
	}

	protected function _writeKeys() {

	}

	public function flush() {
		if (!$this->is_connected)
			return false;

		return $this->redis->flushdb();
	}

	protected function close() {
		if (!$this->is_connected)
			return false;

		return $this->redis->close();
	}

	public static function getRedisServers() {
		if (Yaf_Registry::has('redis_servers'))
		{
			return Yaf_Registry::get('redis_servers');
		}
		else
		{
			$servers = array();
			$rediscaches = Yaf_Registry::get('config')->cache->redis;
			if (!empty($rediscaches))
			{
				$hosts = explode('|', $rediscaches->hosts);
				$ports = explode('|', $rediscaches->ports);
				foreach ($hosts as $key => $host)
				{
					if (isset($ports[$key]))
					{
						$servers[] = array('host' => $host, 'port' => $ports[$key]);
					}
				}
				Yaf_Registry::set('redis_servers', $servers);
			}

			return $servers;
		}

	}
}
