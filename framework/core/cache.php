<?php
namespace framework\core;
use framework;

class cache extends component
{
	static private $_instance;
	
	static private $_expires;
	
	protected static function init()
	{
		$cache = self::getConfig('cache');
		self::$_expires = isset($cache['expires'])?$cache['expires']:0;
		$type = isset($cache['type'])?$cache['type']:'mysql';
		if (!(isset(self::$_instance[$type]) && !empty(self::$_instance[$type])))
		{
			$cache = 'framework\\core\\cache\\driver\\'.$type;
			self::$_instance[$type] = new $cache();
		}
		return self::$_instance[$type];
	}
	
	public static function __callstatic($name,$args)
	{
		return self::$name($args);
	}
	
	/**
	 * 数据有效期
	 * @param unknown $expires
	 */
	static function setExpires($expires)
	{
		self::$_expires = $expires;
	}
	
	/**
	 * 设置或者更新数据
	 * @param unknown $name
	 * @param unknown $value
	 * @param number $cache
	 */
	static function set($name,$value,$expires = 0)
	{
		$app = self::getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			$config = self::getConfig('cache');
			$expires = empty($expires)?self::$_expires:$expires;
			return $cacheInstance->set($name,$value,$expires);
		}
		return false;
	}
	
	/**
	 * 获取数据
	 * @param unknown $name
	 * @return mixed|unknown
	 */
	static function get($name)
	{
		$app = self::getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			$value = $cacheInstance->get($name);
			return $value;
		}
		return NULL;
	}
}