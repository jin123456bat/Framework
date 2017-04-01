<?php
namespace framework\core;

use framework\lib\error;

/**
 * 组件基类
 *
 * @author fx
 */
class component extends error
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
	 * 添加临时配置全局配置变量，这个变量当程序运行完毕后自动注销
	 *
	 * @param unknown $key        	
	 * @param unknown $value        	
	 */
	function addTemporaryConfig($key, $value)
	{
		self::$_config['framework_core_temporary'][$key] = $value;
	}

	/**
	 * 获取临时配置变量
	 *
	 * @param unknown $key        	
	 * @return mixed
	 */
	function getTemporaryConfig($key)
	{
		return self::$_config['framework_core_temporary'][$key];
	}

	/**
	 * 载入组件配置
	 */
	function setConfig($app)
	{
		$config_path = rtrim(ROOT, '/') . '/' . $app . '/config/';
		foreach (scandir($config_path) as $config_file)
		{
			if ($config_file != '.' && $config_file != '..')
			{
				$config = include $config_path . $config_file;
				if (is_array($config) && ! empty($config))
				{
					if (isset(self::$_config[pathinfo($config_file, PATHINFO_FILENAME)]) && is_array(self::$_config[pathinfo($config_file, PATHINFO_FILENAME)]))
					{
						self::$_config[pathinfo($config_file, PATHINFO_FILENAME)] = array_merge(self::$_config[pathinfo($config_file, PATHINFO_FILENAME)], $config);
					}
					else
					{
						self::$_config[pathinfo($config_file, PATHINFO_FILENAME)] = $config;
					}
				}
			}
		}
	}

	/**
	 * 替换配置
	 *
	 * @param unknown $key        	
	 * @param unknown $value        	
	 * @param string $file        	
	 * @return boolean
	 */
	public static function replaceConfig($key, $value, $file = 'app')
	{
		self::$_config[$file][$key] = $value;
		return true;
	}

	/**
	 * 获取组件配置
	 */
	public static function getConfig($name = null)
	{
		if ($name !== null)
		{
			$names = explode('.', $name);
			$config = self::$_config;
			foreach ($names as $name)
			{
				if (! empty($name))
				{
					if (isset($config[$name]))
					{
						$config = $config[$name];
					}
					else
					{
						return null;
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
