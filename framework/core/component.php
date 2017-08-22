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
		return parent::initlize();
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
	function setConfig($app_name)
	{
		//用户配置
		$config_path = ROOT.'/'.trim($app_name,'/') . '/config/';
		foreach (scandir($config_path) as $config_file)
		{
			if ($config_file != '.' && $config_file != '..')
			{
				$config = include $config_path . $config_file;
				if (is_array($config) && ! empty($config))
				{
					$name = pathinfo($config_file, PATHINFO_FILENAME);
					if (isset(self::$_config[$name]) && is_array(self::$_config[$name]))
					{
						self::$_config[$name] = array_merge(self::$_config[$name], $config);
					}
					else
					{
						self::$_config[$name] = $config;
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
	final public function replaceConfig($key, $value, $file = 'app')
	{
		self::$_config[$file][$key] = $value;
		return true;
	}
	
	/**
	 * 获取组件配置
	 */
	final public function getConfig($name = null,$config_name = NULL)
	{
		if ($name !== null)
		{
			$class_config = '';
			//直接使用类中的配置
			if (method_exists($this, '__config'))
			{
				$class_config = $this->__config();
				if (!empty($class_config) && is_array($class_config))
				{
					return $class_config;
				}
			}
			
			$config = self::$_config;
			
			if (isset($config[$name]))
			{
				
				if (!empty($class_config) && is_scalar($class_config))
				{
					if (isset($config[$name][$class_config]))
					{
						return $config[$name][$class_config];
					}
				}
				
				if (empty($config_name))
				{
					foreach ($config[$name] as $c)
					{
						if (isset($c['default']) && $c['default']===true)
						{
							return $c;
						}
					}
				}
				else if (!empty($config_name))
				{
					return $config[$name][$config_name];
				}
				
				return $config[$name];
			}
			return NULL;
		}
		else
		{
			return self::$_config;
		}
	}
}
