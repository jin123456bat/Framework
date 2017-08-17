<?php
namespace framework\core;

use framework;

class cache extends component
{

	private static $_instance;

	private static $_expires;

	private static $_store;

	function __construct($store)
	{
		self::$_store = $store;
	}

	protected static function init()
	{
		$config = $this->getConfig('cache');
		
		self::$_expires = isset($config['expires']) ? $config['expires'] : 0;
		
		if (empty(self::$_store))
		{
			$type = isset($config['type']) ? $config['type'] : 'mysql';
		}
		else
		{
			$type = self::$_store;
		}
		
		if (! (isset(self::$_instance[$type]) && ! empty(self::$_instance[$type])))
		{
			$cache = 'framework\\core\\cache\\driver\\' . $type;
			self::$_instance[$type] = new $cache($config);
		}
		return self::$_instance[$type];
	}

	public static function __callstatic($name, $args)
	{
		return self::$name($args);
	}

	/**
	 * 设置默认的数据有效期
	 * 
	 * @param unknown $expires        
	 */
	static function setExpires($expires)
	{
		self::$_expires = $expires;
	}

	/**
	 * 设置或者更新数据
	 * 
	 * @param unknown $name
	 *        数据名称
	 * @param unknown $value
	 *        数据值
	 * @param number $cache
	 *        数据有效期 当为0的时候使用默认的数据有效期
	 */
	static function set($name, $value, $expires = 0)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			$config = $this->getConfig('cache');
			$expires = empty($expires) ? self::$_expires : $expires;
			return $cacheInstance->set($name, $value, $expires);
		}
		return false;
	}

	/**
	 * 自增
	 * 
	 * @param unknown $name        
	 * @param number $amount        
	 * @return bool true on success or false on failure
	 */
	static function increase($name, $amount = 1)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			return $cacheInstance->increase($name, $amount);
		}
		return null;
	}

	/**
	 * 自减
	 * 
	 * @param unknown $name        
	 * @param number $amount        
	 * @return bool true on success or false on failure
	 */
	static function decrease($name, $amount = 1)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			return $cacheInstance->decrease($name, $amount);
		}
		return null;
	}

	/**
	 * 获取数据
	 * 
	 * @param unknown $name        
	 * @param $default NULL
	 *        当数据不存在的时候的默认值
	 * @return mixed|unknown
	 */
	static function get($name, $default = NULL)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			$value = $cacheInstance->get($name);
			if ($value === NULL)
			{
				return $default;
			}
			return $value;
		}
		return null;
	}

	/**
	 * 删除缓存
	 * 
	 * @param string $name        
	 */
	static function remove($name)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			return $cacheInstance->remove($name);
		}
		return false;
	}

	/**
	 * 清空所有缓存
	 */
	static function flush()
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			return $cacheInstance->flush();
		}
		return false;
	}

	/**
	 * 判断缓存是否存在
	 * 
	 * @param string $name        
	 * @return bool
	 */
	static function has($name)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			return $cacheInstance->has($name);
		}
		return false;
	}

	/**
	 * 和set相同，不同的是假如原来的name存在了，会失败，并且返回false
	 * 
	 * @param unknown $name        
	 * @param unknown $value        
	 * @return boolean
	 */
	static function add($name, $value)
	{
		$app = $this->getConfig('app');
		if (isset($app['cache']) && $app['cache'])
		{
			$cacheInstance = self::init();
			$config = $this->getConfig('cache');
			$expires = empty($expires) ? self::$_expires : $expires;
			return $cacheInstance->add($name, $value, $expires);
		}
		return false;
	}

	/**
	 * 强制使用某一个类型的缓存来存储或者读取数据
	 * 返回一个特殊的class，这个class可以存储到变量中下次继续使用
	 * 
	 * @param string $type        
	 * @return cache
	 */
	static function store($type)
	{
		return new self($type);
	}
}
