<?php
namespace framework\core;

use framework\core\database\mysql\table;
use application\extend\cache;

class base
{
	
	private static $_model_instance = array();
	
	private static $_table_instance = array();
	
	private static $_cache_instance = array();
	
	static $APP_NAME;
	
	static $APP_PATH;
	
	static $APP_CONF;
	
	function __construct()
	{
	}
	
	public function initlize()
	{
	}
	
	public function hash()
	{
		return spl_object_hash($this);
	}
	
	/**
	 * 变量类型强制转换
	 *
	 * @param unknown $variable
	 * @param string $type
	 * @return string|array|boolean|number|StdClass|unknown
	 */
	protected static function setVariableType($variable, $type = 's')
	{
		if (empty($type))
		{
			return $variable;
		}
		switch ($type)
		{
			case 's':
				return (string) $variable;
			case 'a':
				return (array) $variable;
			case 'b':
				return (bool) $variable;
			case 'd': // double
			case 'f':
				return (float) $variable;
			case 'o':
				return (object) $variable;
			case 'i':
				return (int) $variable;
			case 'binary':
				return (string) $variable;
			default:
				if (settype($variable, $type))
				{
					return $variable;
				}
		}
	}
	
	/**
	 * 载入数据模型
	 *
	 * @param string $name
	 *        模块名
	 * @return model
	 */
	protected static function model($name)
	{
		if (! isset(self::$_model_instance[$name]))
		{
			if (! class_exists(self::$APP_NAME . '\\model\\' . $name))
			{
				$path = APP_ROOT . '/model/' . $name . '.php';
				if (file_exists($path))
				{
					include $path;
					$model = self::$APP_NAME . '\\model\\' . $name;
					self::$_model_instance[$name] = new $model($name);
				}
				else
				{
					self::$_model_instance[$name] = new model($name);
				}
				if (method_exists(self::$_model_instance[$name], 'initlize'))
				{
					self::$_model_instance[$name]->initlize();
				}
			}
			else
			{
				$model = self::$APP_NAME . '\\model\\' . $name;
				self::$_model_instance[$name] = new $model($name);
				if (method_exists(self::$_model_instance[$name], 'initlize'))
				{
					self::$_model_instance[$name]->initlize();
				}
			}
		}
		return self::$_model_instance[$name];
	}
	
	/**
	 * 加载数据表
	 * @param string $name 表名
	 * @return table
	 */
	protected static function table($name)
	{
		if (!isset(self::$_table_instance[$name]))
		{
			self::$_table_instance[$name] = new table($name);
			if (method_exists(self::$_table_instance[$name], 'initlize'))
			{
				self::$_model_instance[$name]->initlize();
			}
		}
		return self::$_table_instance[$name];
	}
	
	/**
	 * 缓存模块
	 * @param string $name
	 */
	protected static function cache($name = NULL)
	{
		if (!isset(self::$_cache_instance[$name]))
		{
			self::$_cache_instance[$name] = application::load('cache','framework/core/cache');
			self::$_cache_instance[$name]->setType($name);
		}
		return self::$_cache_instance[$name];
	}
}
