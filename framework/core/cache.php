<?php
namespace framework\core;
use framework;

class cache extends component
{
	static private $_instance;
	
	static private $_expires;
	
	static private function init()
	{
		$cache = self::getConfig('cache');
		self::$_expires = isset($cache['expires'])?$cache['expires']:5;
		$type = isset($cache['type'])?$cache['type']:'mysql';
		if (!(isset(self::$_instance[$type]) && !empty(self::$_instance[$type])))
		{
			$cache = 'framework\\core\\cache\\driver\\'.$type;
			self::$_instance[$type] = new $cache();
		}
		return self::$_instance[$type];
	}
	
	static function setExpires($expires)
	{
		self::$_expires = $expires;
	}
	
	static function set($name,$value,$cache = 0)
	{
		$cache = self::init();
		$config = self::getConfig('cache');
		$cache = empty($cache)?self::$_expires:$cache;
		return $cache->set($name,$value,$cache);
	}
	
	static function get($name)
	{
		$cache = self::init();
		return $cache->get($name);
	}
}