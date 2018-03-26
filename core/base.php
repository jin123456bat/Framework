<?php
namespace framework\core;

use framework\core\database\mysql\table;

class base
{
	
	private static $_model_instance = array();
	
	private static $_table_instance = array();
	
	private static $_cache_instance = array();
	
	/**
	 * 应用程序的名称
	 * @var string
	 */
	static $APP_NAME;
	
	/**
	 * 应用程序的路径
	 * @var unknown
	 */
	static $APP_PATH;
	
	/**
	 * 应用程序配置文件的名称
	 * @var unknown
	 */
	static $APP_CONF;
	
	/**
	 * 全部配置
	 * @var array
	 */
	private static $_config = array();
	
	/**
	 * 核心类重写
	 * @var array
	 */
	static $_rewrite = array();
	
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
	 * @param string $type  假如用逗号分割，按照枚举类型算
	 * @return string|array|boolean|number|StdClass|unknown
	 */
	protected static function setVariableType($variable, $type = '')
	{
		if (empty($type))
		{
			return $variable;
		}
		
		if (strpos($type,',') === false)
		{
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
		else
		{
			//有英文逗号按照枚举类型
			$enum = explode(',', $type);
			if (in_array($variable,$enum))
			{
				return $variable;
			}
			return current($enum);
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
				self::$_table_instance[$name]->initlize();
			}
		}
		return self::$_table_instance[$name];
	}
	
	
	/**
	 * 载入组件配置
	 * @param boolean $is_framework
	 */
	function setConfig($is_framework = true)
	{
		$root = $is_framework ? SYSTEM_ROOT : APP_ROOT;
		// 用户配置
		$config_path = $root . '/config/';
		foreach (scandir($config_path) as $config_file)
		{
			if ($config_file != '.' && $config_file != '..' && is_file($config_path . $config_file))
			{
				$config = include $config_path . $config_file;
				if (is_array($config) && ! empty($config))
				{
					$name = pathinfo($config_file, PATHINFO_FILENAME);
					if (isset(self::$_config[$name]) && is_array(self::$_config[$name]))
					{
						self::$_config[$name] = array_merge(self::$_config[$name], $config);
					}
					else
					{
						self::$_config[$name] = $config;
					}
				}
			}
		}
	}
	
	/**
	 * 替换配置
	 * @param unknown $key
	 * @param unknown $value
	 * @param string $file
	 * @return boolean
	 */
	final public function replaceConfig($key, $value, $file = '')
	{
		if (empty($file))
		{
			$file = self::$APP_CONF;
		}
		self::$_config[$file][$key] = $value;
		return true;
	}
	
	/**
	 * 获取组件配置
	 */
	public static function getConfig($name = null)
	{
		if ($name !== null)
		{
			if (isset(self::$_config[$name]))
			{
				return self::$_config[$name];
			}
			return NULL;
		}
		else
		{
			return self::$_config;
		}
	}
	
	/**
	 * 获取配置目录下任意文件的路径
	 * @param unknown $filename
	 * @return string
	 */
	public static function getConfigFile($filename)
	{
		return rtrim(self::$APP_CONF,'/').'/'.$filename;
	}
}
