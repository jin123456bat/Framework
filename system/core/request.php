<?php
namespace framework\core;
class request extends base
{
	/**
	 * 当前请求方式
	 * @return unknown
	 */
	static function method()
	{
		return $_SERVER['REQUEST_METHOD'];
	}
	
	/**
	 * 判断是否是https链接
	 * @return boolean
	 */
	static function isHttps()
	{
		return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
	}
	
	/**
	 * 判断是ajax请求
	 * @return boolean
	 */
	static function isAjax()
	{
		return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest";
	}
	
	/**
	 * 读取file变量
	 * @param unknown $name
	 */
	static public function file($name)
	{
		if (isset($_FILES[$name]))
		{
			return $_FILES[$name];
		}
		return false;
	}
	
	/**
	 * 读取post变量
	 * @param unknown $name
	 * @param unknown $defaultValue
	 * @param unknown $filter
	 * @param string $type
	 * @return mixed|string|boolean|number|\core\StdClass|\core\unknown|string
	 */
	static public function post($name,$defaultValue = NULL,$filter = NULL,$type = 's')
	{
		if (isset($_POST[$name]))
		{
			$data = $this->setVariableType($_POST[$name],$type);
				
			if (is_callable($filter))
			{
				return call_user_func_array($filter, [$data]);
			}
			else if (is_callable(['core\filter',$filter]))
			{
				return filter::$filter($data);
			}
			else
			{
				return $data;
			}
		}
		else
		{
			return $defaultValue;
		}
	}
	
	/**
	 * 读取get变量
	 * @param unknown $name
	 * @param unknown $defaultValue
	 * @param unknown $filter
	 * @param string $type
	 */
	static public function get($name,$defaultValue = NULL,$filter = NULL,$type = 's')
	{
		if (isset($_GET[$name]))
		{
			$data = $this->setVariableType($_GET[$name],$type);
		
			if (is_callable($filter))
			{
				return call_user_func_array($filter, [$data]);
			}
			else if (is_callable(['core\filter',$filter]))
			{
				return filter::$filter($data);
			}
			else
			{
				return $data;
			}
		}
		else
		{
			return $defaultValue;
		}
	}
	
	/**
	 * 读取request变量
	 * @param unknown $name
	 * @param unknown $defaultValue
	 * @param unknown $filter
	 * @param string $type
	 */
	static public function param($name,$defaultValue = NULL,$filter = NULL,$type = 's')
	{
		if (isset($_REQUEST[$name]))
		{
			$data = $this->setVariableType($_REQUEST[$name],$type);
		
			if (is_callable($filter))
			{
				return call_user_func_array($filter, [$data]);
			}
			else if (is_callable(['core\filter',$filter]))
			{
				return filter::$filter($data);
			}
			else
			{
				return $data;
			}
		}
		else
		{
			return $defaultValue;
		}
	}
	
	/**
	 * 获取请求的header
	 * @param unknown $name
	 * @return NULL|unknown
	 */
	function header($name)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : NULL;
	}
}