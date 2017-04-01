<?php

/**
 * @author fx
 *
 */
class framework
{

	/**
	 * 存储了所有app进程
	 *
	 * @var unknown
	 */
	private $_application;

	/**
	 * 命令行参数个数
	 *
	 * @var unknown
	 */
	private $_argc;

	/**
	 * 命令行参数
	 *
	 * @var unknown
	 */
	private $_argv;

	function __construct()
	{
		spl_autoload_register(array(
			$this,
			'autoload'
		), true);
	}

	/**
	 * 创建应用程序
	 *
	 * @param unknown $name        	
	 * @param unknown $path        	
	 */
	function createApplication($name, $path)
	{
		$appkey = md5($name . $path);
		
		$paths = explode('/', $path);
		$namespace = end($paths) . '\\extend\\' . $name;
		$user_deinfed_component_path = trim($path, '/') . '/extend/' . $name . '.php';
		if (file_exists($user_deinfed_component_path))
		{
			$this->_application[$appkey] = new $namespace($name, $path);
		}
		else
		{
			// 载入系统的application
			$namespace = 'framework\\core\\application';
			$this->_application[$appkey] = new $namespace($name, $path);
		}
		
		if (method_exists($this->_application[$appkey], 'initlize'))
		{
			$this->_application[$appkey]->initlize();
		}
		return $this->_application[$appkey];
	}

	/**
	 * 自动载入
	 */
	protected function autoload($classname)
	{
		$classname = ltrim($classname);
		$class = explode('\\', $classname);
		$path = rtrim(ROOT, '/') . '/' . str_replace('\\', '/', $classname) . '.php';
		if (file_exists($path))
		{
			if ((include $path) !== 1)
			{
				echo "include failed";
			}
		}
	}
}
