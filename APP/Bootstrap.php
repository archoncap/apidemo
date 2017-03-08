<?php
class Bootstrap extends Yaf_Bootstrap_Abstract {
	protected $config;

	public function _initConfig(Yaf_Dispatcher $dispatcher) {
		$this->config = Yaf_Application::app()->getConfig();		
		Yaf_Registry::set('config', $this->config);		
		Yaf_Loader::import(APPLICATION_PATH . '/conf/function.php');		
		//判断请求方式，命令行请求应跳过一些HTTP请求使用的初始化操作，如模板引擎初始化
		define('REQUEST_METHOD', strtoupper($dispatcher->getRequest()->getMethod()));				
		if(!empty($this->config->application->suffix)) {
			$dispatcher->getRequest()->setRequestUri( str_replace('.'.$this->config->application->suffix,'/',$_SERVER['REQUEST_URI']) );
        }						$dispatcher->autoRender(FALSE);
	}

	public function _initError(Yaf_Dispatcher $dispatcher) {
		if ($this->config->application->debug)
		{
			define('DEBUG_MODE', true);
			ini_set('display_errors', 'On');
			error_reporting(E_ALL & ~E_NOTICE);
		}
		else
		{
			define('DEBUG_MODE', false);
			ini_set('display_errors', 'Off');
			error_reporting(0);
		}
	}

	public function _initPlugin(Yaf_Dispatcher $dispatcher) {
		if (isset($this->config->application->benchmark) && $this->config->application->benchmark == true)
		{
			$benchmark = new BenchmarkPlugin();
			$dispatcher->registerPlugin($benchmark);
		}
		$verify = new VerifyPlugin();
		$dispatcher->registerPlugin($verify);
		$antizy = new AntizyPlugin();
		$dispatcher->registerPlugin($antizy);
	}

	public function _initRoute(Yaf_Dispatcher $dispatcher) {
		$routes = $this->config->routes;
		if (!empty($routes))
		{
			$router = $dispatcher->getRouter();
			$router->addConfig($routes);
		}		
	}

	public function _initMemcache() {
		if (!empty($this->config->cache->caching_system))
		{
			Yaf_Registry::set('cache_exclude_table', explode('|', $this->config->cache->cache_exclude_table));
			Yaf_Loader::import(APPLICATION_PATH . '/library/Cache/Cache.php');
			if (isset($this->config->cache->prefix))
			{
				define('CACHE_KEY_PREFIX', $this->config->cache->prefix);
			}
			if (isset($this->config->cache->object_cache_enable) && $this->config->cache->object_cache_enable)
			{
				define('OBJECT_CACHE_ENABLE', true);
			}
			else
			{
				define('OBJECT_CACHE_ENABLE', false);
			}
		}
		else
		{
			define('OBJECT_CACHE_ENABLE', false);
		}
	}

	public function _initDatabase() {
		$servers = array();
		$database = $this->config->database;
		Yaf_Registry::set('database', $database);
		if (isset($database->mysql_log_error) && $database->mysql_log_error && !defined('MYSQL_LOG_ERROR'))
		{			define('MYSQL_LOG_ERROR', true);
		}
		if (isset($database->prefix) && !defined('MYSQL_PREFIX'))
		{
			define('MYSQL_PREFIX', $database->prefix);
		}		
		Yaf_Loader::import(APPLICATION_PATH . '/library/Db/Db.php');
		Yaf_Loader::import(APPLICATION_PATH . '/library/Db/DbQuery.php');
	}
}
