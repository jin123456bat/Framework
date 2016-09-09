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
	}
	
	function initlize()
	{
		$this->setConfig($this->_name);
		//载入环境变量
		$this->env();
		
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
	}
}