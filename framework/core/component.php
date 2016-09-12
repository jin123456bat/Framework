<?php
namespace framework\core;

/**
 * 组件基类
 * @author fx
 */
class component extends base
{
	private static $_config = array();
	
	function __construct()
	{
		parent::__construct();
	}
	
	function initlize()
	{
		parent::initlize();
	}
	
	/**
	 * 载入组件配置
	 */
	function setConfig($app)
	{
		$config_path = rtrim(ROOT,'/').'/'.$app.'/config/';
		foreach(scandir($config_path) as $config_file)
		{
			if ($config_file != '.' && $config_file != '..')
			{
				$config = include $config_path.$config_file;
				if (is_array($config) && !empty($config))
				{
					if (isset(self::$_config[pathinfo($config_file,PATHINFO_FILENAME)]) && is_array(self::$_config[pathinfo($config_file,PATHINFO_FILENAME)]))
					{
						self::$_config[pathinfo($config_file,PATHINFO_FILENAME)] = array_merge(self::$_config[pathinfo($config_file,PATHINFO_FILENAME)],$config);
					}
					else
					{
						self::$_config[pathinfo($config_file,PATHINFO_FILENAME)] = $config;
					}
				}
			}
		}
	}
	
	/**
	 * 获取组件配置
	 */
	public static function getConfig($name = NULL)
	{
		if ($name !== NULL)
		{
			$names = explode('.', $name);
			$config = self::$_config;
			foreach ($names as $name)
			{
				if (!empty($name))
				{
					if (isset($config[$name]))
					{
						$config = $config[$name];
					}
					else
					{
						return NULL;
					}
				}
			}
			return $config;
		}
		else
		{
			return self::$_config;
		}
	}
}