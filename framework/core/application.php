<?php
namespace framework\core;

class application extends component
{
	
	private $_name;
	
	private $_path;
	
	function __construct($name,$path)
	{
		$this->_name = $name;
		$this->_path = $path;
		
		//spl_autoload_register([$this,'autoload']);
		
		parent::__construct();
	}
	
	function initlize()
	{
		//载入系统默认配置
		$this->setConfig('system');
		//载入app的配置
		$this->setConfig($this->_name);
		//载入环境变量
		$this->env();
		parent::initlize();
	}
	
	/**
	 * 设置app的环境变量
	 */
	private function env()
	{
		$env = $this->getConfig('environment');
		if (is_array($env) && !empty($env))
		{
			foreach ($env as $name => $variable)
			{
				if (is_array($variable) && !empty($variable))
				{
					foreach ($variable as $prefix => $value)
					{
						ini_set($name.'.'.$prefix, $value);
					}
				}
				else
				{
					ini_set($name, $variable);
				}
			}
		}
	}
	
	/**
	 * 运行application
	 */
	function run()
	{
		$router = $this->load('router');
	}
	
	function load($classname)
	{
		$classnames = explode('\\', $classname);
		$class = end($classnames);
		
		if (count($classnames) == 1)
		{
			$namespaces = [
				$this->_name.'\\extend',
				'framework\\core',
				'framework\\core\\database',
				'framework\\core\\response',
				'framework\\lib',
				'framework\\vendor',
			];
		}
		else
		{
			array_pop($classnames);
			$namespaces = [
				implode('\\',$classnames),
			];
		}
		foreach ($namespaces as $namespace)
		{
			var_dump($namespace.'\\'.$classname);
			
			if(class_exists($namespace.'\\'.$classname,true))
			{
				return new $namespace.'\\'.$classname();
			}
		}
	}
}