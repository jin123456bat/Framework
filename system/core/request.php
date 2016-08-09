<?php
namespace core;
class request extends base
{
	/**
	 * 读取file变量
	 * @param unknown $name
	 */
	static public function file($name)
	{
		if (isset($_FILES[$name]))
		{
			
		}
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
}