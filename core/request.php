<?php
namespace framework\core;

use framework\core\request\parser\parser;
use framework\vendor\file;

class request extends base
{

	/**
	 * 获取客户端真实IP
	 * 
	 * @return string|unknown
	 */
	static function getIp()
	{
		$cip = '';
		if (! empty($_SERVER["HTTP_CLIENT_IP"]))
		{
			$cip = $_SERVER["HTTP_CLIENT_IP"];
		}
		else if (! empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else if (! empty($_SERVER["REMOTE_ADDR"]))
		{
			$cip = $_SERVER["REMOTE_ADDR"];
		}
		return $cip;
	}

	/**
	 * 获取客户端的user-agent
	 */
	static function getUA()
	{
		return self::header('user_agent');
	}

	/**
	 * 获取请求头
	 * 
	 * @return unknown[]
	 */
	public static function header($name = NULL)
	{
		if (empty($name))
		{
			$headers = array();
			foreach ($_SERVER as $key => $value)
			{
				if (substr($key, 0, 5) === 'HTTP_')
				{
					$key = substr($key, 5);
					$key = strtolower($key);
					$key = str_replace('_', ' ', $key);
					$key = ucwords($key);
					$key = str_replace(' ', '-', $key);
					$headers[$key] = $value;
				}
			}
			return $headers;
		}
		else
		{
			return isset($_SERVER['HTTP_' . strtoupper($name)]) ? $_SERVER['HTTP_' . strtoupper($name)] : NULL;
		}
	}

	/**
	 * 获取post提交的原始数据
	 * enctype="multipart/form-data" 的时候无效
	 * 
	 * @return string
	 */
	static function body()
	{
		return file_get_contents('php://input');
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
	 * 获取当前的url
	 */
	static function url()
	{
		$secheme = self::isHttps() ? 'https://' : 'http://';
		return $secheme . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
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
	 * @return file
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
				return new $class($files);
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
					$temp[$filename] = $object;
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
	 * @param unknown $defaultValue 默认值       
	 * @param string $type 变量类型
	 * @param unknown $filter 过滤器
	 * @param string|parser $parser 请求解析器
	 * @return mixed|string|boolean|number|\core\StdClass|\core\unknown|string
	 */
	public static function post($name = '', $defaultValue = null, $type = '', $filter = null, $parser = '')
	{
		if (! empty($parser))
		{
			if (is_string($parser))
			{
				$parser = class_exists($parser) ? application::load($parser) : application::load(parser::class, $parser);
			}
			
			if (! empty($parser) && $parser instanceof parser)
			{
				$parser->setData(file_get_contents('php://input'));
				$requestContent = $parser->getData();
			}
			else
			{
				$requestContent = $_POST;
			}
		}
		else
		{
			$requestContent = $_POST;
		}
		
		if ($name === '')
		{
			return $requestContent;
		}
		else if (isset($requestContent[$name]) && $requestContent[$name] !== '')
		{
			$data = $requestContent[$name];
			
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
						$filterClass = application::load(filter::class);
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
							list($func, $param) = explode(':', $filter_t);
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
	 * @param string $type
	 *        默认是s
	 * @param unknown $filter
	 *        过滤器名称
	 * @param parser|string $parser 请求解析器
	 * @return mixed
	 */
	public static function get($name = '', $defaultValue = null, $type = '', $filter = null, $parser = '')
	{
		if (! empty($parser))
		{
			if (is_string($parser))
			{
				$parser = class_exists($parser) ? application::load($parser) : application::load(parser::class, $parser);
			}
			
			if (! empty($parser) && $parser instanceof parser)
			{
				$parser->setData($_SERVER['QUERY_STRING']);
				$requestContent = $parser->getData();
			}
			else
			{
				$requestContent = $_GET;
			}
		}
		else
		{
			$requestContent = $_GET;
		}
		
		if ($name === '')
		{
			return $requestContent;
		}
		else if (isset($requestContent[$name]) && $requestContent[$name] !== '')
		{
			$data = $requestContent[$name];
			
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
						$filterClass = application::load(filter::class);
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
							list($func, $param) = explode(':', $filter_t);
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
	 * @param unknown $name        变量名称
	 * @param unknown $defaultValue       默认值 
	 * @param string $type  变量类型
	 * @param unknown $filter     过滤器        
	 */
	public static function param($name = '', $defaultValue = null, $type = '',$filter = null)
	{
		if ($name === '')
		{
			return $_REQUEST;
		}
		else if (isset($_REQUEST[$name]) && $_REQUEST[$name] !== '')
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
						$filterClass = application::load(filter::class);
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
							list($func, $param) = explode(':', $filter_t);
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
}
