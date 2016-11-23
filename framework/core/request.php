<?php
namespace framework\core;
class request extends base
{
	static function php_sapi_name()
	{
		if (stripos(php_sapi_name(), 'cli') !== false)
		{
			return 'cli';
		}
		else
		{
			return 'web';
		}
	}
	
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
			$data = $_POST[$name];
				
			if (is_string($filter) && !empty($filter))
			{
				$filters = explode('|', $filter);
				foreach ($filters as $filter_t)
				{
					if (is_callable($filter_t))
					{
						$data = call_user_func($filter_t, $data);
					}
					else 
					{
						$filterClass = application::load('filter');
						if (is_callable(array($filterClass,$filter_t)))
						{
							$data = filter::$filter_t($data);
						}
						else
						{
							list($func,$param) = explode(':', $filter_t);
							if (is_callable($func))
							{
								$pattern = '$["\'].["\']$';
								if (preg_match_all($pattern, $param,$matches))
								{
									$params = array_map(function($param) use($data){
										if (trim($param,'\'"') == '?')
										{
											return $data;
										}
										return trim($param,'\'"');
									}, $matches[0]);
										$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data,$type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data,$type);
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
			$data = $_GET[$name];
		
			if (is_string($filter) && !empty($filter))
			{
				$filters = explode('|', $filter);
				foreach ($filters as $filter_t)
				{
					
					if (is_callable($filter_t))
					{
						$data = call_user_func($filter_t, $data);
					}
					else 
					{
						$filterClass = application::load('filter');
						if (is_callable(array($filterClass,$filter_t)))
						{
							$data = filter::$filter_t($data);
						}
						else
						{
							list($func,$param) = explode(':', $filter_t);
							if (is_callable($func))
							{
								$pattern = '$["\'].["\']$';
								if (preg_match_all($pattern, $param,$matches))
								{
									$params = array_map(function($param) use($data){
										if (trim($param,'\'"') == '?')
										{
											return $data;
										}
										return trim($param,'\'"');
									}, $matches[0]);
										$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data,$type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data,$type);
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
			$data = $_REQUEST[$name];
			
			if (is_string($filter))
			{
				$filters = explode('|', $filter);
				foreach ($filters as $filter_t)
				{
					
					if (is_callable($filter_t))
					{
						$data = call_user_func($filter_t, $data);
					}
					else 
					{
						$filterClass = application::load('filter');
						if (is_callable(array($filterClass,$filter_t)))
						{
							$data = filter::$filter_t($data);
						}
						else
						{
							list($func,$param) = explode(':', $filter);
							if (is_callable($func))
							{
								$pattern = '$["\'].["\']$';
								if (preg_match_all($pattern, $param,$matches))
								{
									$params = array_map(function($param) use($data){
										if (trim($param,'\'"') == '?')
										{
											return $data;
										}
										return trim($param,'\'"');
									}, $matches[0]);
										$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data,$type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data,$type);;
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
	public static function header($name)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : NULL;
	}
}