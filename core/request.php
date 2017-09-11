<?php
namespace framework\core;

class request extends base
{

	public static $_php_sapi_name = 'cli';

	/**
	 * 代码执行方式 cli web
	 * @return string
	 */
	static function php_sapi_name()
	{
		if (stripos(php_sapi_name(), 'cli') !== false)
		{
			return self::$_php_sapi_name;
		}
		else
		{
			self::$_php_sapi_name = 'web';
			return self::$_php_sapi_name;
		}
	}

	/**
	 * 当前请求方式 并自动转小写
	 * 
	 * @return unknown
	 */
	static function method()
	{
		return strtolower($_SERVER['REQUEST_METHOD']);
	}

	/**
	 * 判断是否是https链接
	 * 
	 * @return boolean
	 */
	static function isHttps()
	{
		return isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on';
	}

	/**
	 * 判断是ajax请求
	 * 
	 * @return boolean
	 */
	static function isAjax()
	{
		return isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest";
	}

	/**
	 * 读取file变量
	 * 
	 * @param string $name
	 *        文件上传名
	 * @param string $config
	 *        使用指定配置上传文件，不填写使用默认配置 配置填写在upload配置中
	 */
	public static function file($name, $config = null)
	{
		$uploader = new upload();
		$files = $uploader->receive($name, $config);
		$class = '\framework\vendor\file';
		if (is_scalar($files))
		{
			if (class_exists($class, true))
			{
				$object = new $class($files);
				if ($object->hasError())
				{
					return $files;
				}
				else
				{
					return $object;
				}
			}
			else
			{
				return $files;
			}
		}
		else if (is_array($files))
		{
			$temp = array();
			
			foreach ($files as $filename => $file)
			{
				if (class_exists($class, true))
				{
					$object = new $class($file);
					if ($object->hasError())
					{
						$temp[$filename] = $file;
					}
					else
					{
						$temp[$filename] = $object;
					}
				}
				else
				{
					$temp[$filename] = $files;
				}
			}
			return $temp;
		}
	}

	/**
	 * 读取post变量
	 * 
	 * @param unknown $name        
	 * @param unknown $defaultValue        
	 * @param unknown $filter        
	 * @param string $type        
	 * @return mixed|string|boolean|number|\core\StdClass|\core\unknown|string
	 */
	public static function post($name = '', $defaultValue = null, $filter = null, $type = 's')
	{
		if ($name === '')
		{
			return $_POST;
		}
		else if (isset($_POST[$name]))
		{
			$data = $_POST[$name];
			
			if (is_string($filter) && ! empty($filter))
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
						if (is_callable(array(
							$filterClass,
							$filter_t
						)))
						{
							$data = call_user_func(array(
								$filterClass,
								$filter_t
							), $data);
						}
						else if (! empty($filter_t))
						{
							list ($func, $param) = explode(':', $filter_t);
							if (is_callable($func))
							{
								$pattern = '$["\'].["\']$';
								if (preg_match_all($pattern, $param, $matches))
								{
									$params = array_map(function ($param) use ($data) {
										if (trim($param, '\'"') == '?')
										{
											return $data;
										}
										return trim($param, '\'"');
									}, $matches[0]);
									$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data, $type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data, $type);
			}
		}
		else
		{
			return $defaultValue;
		}
	}

	/**
	 * 读取get变量
	 * 
	 * @param unknown $name
	 *        参数名称
	 * @param unknown $defaultValue
	 *        默认值
	 * @param unknown $filter
	 *        过滤器名称
	 * @param string $type
	 *        默认是s
	 */
	public static function get($name = '', $defaultValue = null, $filter = null, $type = '')
	{
		if ($name === '')
		{
			return $_GET;
		}
		else if (isset($_GET[$name]))
		{
			$data = $_GET[$name];
			
			if (is_string($filter) && ! empty($filter))
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
						if (is_callable(array(
							$filterClass,
							$filter_t
						)))
						{
							$data = call_user_func(array(
								$filterClass,
								$filter_t
							), $data);
						}
						else if (! empty($filter_t))
						{
							list ($func, $param) = explode(':', $filter_t);
							if (is_callable($func))
							{
								$pattern = '/["\'][^"\']+["\']/';
								if (preg_match_all($pattern, $param, $matches))
								{
									$params = array_map(function ($param) use ($data) {
										if (trim($param, '\'"') == '?')
										{
											return $data;
										}
										return trim($param, '\'"');
									}, $matches[0]);
									$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data, $type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data, $type);
			}
		}
		else
		{
			return $defaultValue;
		}
	}

	/**
	 * 读取request变量
	 * 
	 * @param unknown $name        
	 * @param unknown $defaultValue        
	 * @param unknown $filter        
	 * @param string $type        
	 */
	public static function param($name = '', $defaultValue = null, $filter = null, $type = 's')
	{
		if ($name === '')
		{
			return $_REQUEST;
		}
		else if (isset($_REQUEST[$name]))
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
						if (is_callable(array(
							$filterClass,
							$filter_t
						)))
						{
							$data = call_user_func(array(
								$filterClass,
								$filter_t
							), $data);
						}
						else if (! empty($filter_t))
						{
							list ($func, $param) = explode(':', $filter_t);
							if (is_callable($func))
							{
								$pattern = '$["\'].["\']$';
								if (preg_match_all($pattern, $param, $matches))
								{
									$params = array_map(function ($param) use ($data) {
										if (trim($param, '\'"') == '?')
										{
											return $data;
										}
										return trim($param, '\'"');
									}, $matches[0]);
									$data = call_user_func_array($func, $params);
								}
							}
						}
					}
				}
				return self::setVariableType($data, $type);
			}
			else
			{
				if (is_callable($filter))
				{
					$data = call_user_func($filter, $data);
				}
				return self::setVariableType($data, $type);
				;
			}
		}
		else
		{
			return $defaultValue;
		}
	}

	/**
	 * 获取请求的header
	 * 
	 * @param unknown $name        
	 * @return NULL|unknown
	 */
	public static function header($name)
	{
		return isset($_SERVER[$name]) ? $_SERVER[$name] : null;
	}
}