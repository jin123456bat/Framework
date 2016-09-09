<?php
/**
 * @author fx
 *
 */
class framework
{
	/**
	 * 存储了所有app进程
	 * @var unknown
	 */
	private $_application;
	
	function __construct()
	{
		spl_autoload_register([$this,'autoload']);
	}
	
	/**
	 * 创建应用程序
	 * @param unknown $name
	 * @param unknown $path
	 */
	function createApplication($name,$path)
	{
		$appkey = md5($name.$path);
		
		$paths = explode('/', $path);
		$namespace = end($paths).'\\extend\\'.$name;
		$user_deinfed_component_path = trim($path,'/').'/extend/'.$name.'.php';
		if (file_exists($user_deinfed_component_path))
		{
			$this->_application[$appkey] = new $namespace($name,$path);
		}
		else
		{
			//载入系统的application
			$namespace = 'framework\\core\\application';
			$this->_application[$appkey] = new $namespace($name,$path);
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
		$class = explode('\\', $classname);
		if (trim(strtolower(current($class))) == 'framework')
		{
			$class[0] = 'system';
			$classname = implode('\\', $class);
		}
		$path = trim(ROOT,'/').'/'.str_replace('\\', '/', $classname).'.php';
		include_once $path;
	}
}