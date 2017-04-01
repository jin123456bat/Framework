<?php
namespace framework\core;

class base
{

	private static $_model_instance = array();

	static $APP_NAME;

	static $APP_PATH;

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
	 *        	模块名
	 * @return model
	 */
	protected function model($name)
	{
		if (! isset($this->_model_instance[$name]))
		{
			if (! class_exists(self::$APP_NAME . '\\model\\' . $name))
			{
				$path = ROOT . '/' . self::$APP_NAME . '/model/' . $name . '.php';
				if (file_exists($path))
				{
					include $path;
					$model = self::$APP_NAME . '\\model\\' . $name;
					$this->_model_instance[$name] = new $model($name);
				}
				else
				{
					$this->_model_instance[$name] = new model($name);
				}
				if (method_exists($this->_model_instance[$name], 'initlize'))
				{
					$this->_model_instance[$name]->initlize();
				}
			}
			else
			{
				$model = self::$APP_NAME . '\\model\\' . $name;
				$this->_model_instance[$name] = new $model($name);
				if (method_exists($this->_model_instance[$name], 'initlize'))
				{
					$this->_model_instance[$name]->initlize();
				}
			}
		}
		return $this->_model_instance[$name];
	}
}
