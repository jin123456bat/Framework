<?php
namespace core;
use lib\config;

class application extends base
{
	private static $_config;
	
	function __construct($config = NULL)
	{
		if (is_string($config) && file_exists($config))
		{
			include_once $config;
			$config = pathinfo($config,PATHINFO_FILENAME);
			
			if (class_exists($config))
			{
				self::$_config = new $config();
			}
		}
		if (is_object($config))
		{
			self::$_config = $config;
		}
		
		
		$this->initlize();
	}
	
	function initlize()
	{
		//载入环境变量
		$this->env();
	}
	
	/**
	 * 获取当前application的配置信息
	 * @return config
	 */
	static function config()
	{
		return self::$_config;
	}
	
	private function env()
	{
		foreach (self::$_config as $key => $config)
		{
			$key = str_replace('_', '.', $key);
			ini_set($key,$config);
		}
	}
	
	/**
	 * 运行application
	 */
	function run()
	{
	}
}