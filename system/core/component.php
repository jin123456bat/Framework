<?php
namespace framework\core;

/**
 * 组件基类
 * @author fx
 */
class component extends base
{
	private $_config = [];
	
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
					$this->_config = array_merge($this->_config,$config);
				}
				else
				{
					$classname = $app.'\\config\\'.pathinfo($config_file,PATHINFO_FILENAME);
					if (class_exists($classname))
					{
						$class = new $classname();
						//var_dump($class->toArray());
						//$this->_config = array_merge($this->_config,$class->toArray());
						
					}
				}
			}
		}
	}
	
	/**
	 * 获取组件配置
	 */
	function getConfig($name = NULL)
	{
		if ($name !== NULL)
		{
			$names = explode('.', $name);
			$config = $this->_config;
			foreach ($names as $name)
			{
				if (!empty($name))
				{
					if (isset($config[$name]))
					{
						$config = $config[$name];
					}
				}
			}
			return $config;
		}
		else
		{
			return $this->_config;
		}
	}
}