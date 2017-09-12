<?php
namespace framework\core;

class cache extends component
{
	private static $_instance;
	
	/* function setType($type = NULL)
	{
		$config = $this->getConfig('cache');
		
		if (empty($type))
		{
			$this->_type = $config['type'];
			$this->_expires = $config['expires'];
		}
		else
		{
			$this->_type = $type;
			$this->_expires = isset($config[$this->_type]['expires'])?$config[$this->_type]['expires']:$config['expires'];
		}
		
		$cache = 'framework\\core\\cache\\driver\\' . $this->_type;
		$this->_instance[$this->_type] = new $cache($config);
	} */
	
	/**
	 * 初始化缓存驱动
	 * @param string $type
	 * @return \framework\core\cache\cache
	 */
	private static function initInstance($type = NULL)
	{
		$config = self::getConfig('cache');
		$type = empty($type)?$config['type']:$type;
		
		if (isset(self::$_instance[$type]) || empty(self::$_instance[$type]))
		{
			$cache = 'framework\\core\\cache\\driver\\' . $type;
			if (class_exists($cache,true))
			{
				self::$_instance[$type] = new $cache($config);
				if (method_exists(self::$_instance[$type], 'initlize'))
				{
					self::$_instance[$type]->initlize();
				}
			}
		}
		return self::$_instance[$type];
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
		return self::initInstance()->set($name, $value, $expires);
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
		return self::initInstance()->increase($name, $amount);
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
		return self::initInstance()->decrease($name, $amount);
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
		$value = self::initInstance()->get($name);
		if ($value === NULL)
		{
			return $default;
		}
		return $value;
	}
	
	/**
	 * 删除缓存
	 *
	 * @param string $name
	 */
	static function remove($name)
	{
		return self::initInstance()->remove($name);
	}
	
	/**
	 * 清空所有缓存
	 */
	static function flush()
	{
		return self::initInstance()->flush();
	}
	
	/**
	 * 判断缓存是否存在
	 *
	 * @param string $name
	 * @return bool
	 */
	static function has($name)
	{
		return self::initInstance()->has($name);
	}
	
	/**
	 * 和set相同，不同的是假如原来的name存在了，会失败，并且返回false
	 * 有效期为默认有效期
	 *
	 * @param unknown $name
	 * @param unknown $value
	 * @return boolean
	 */
	static function add($name, $value)
	{
		return self::initInstance()->add($name, $value, $this->_expires);
	}
	
	/**
	 * 使用指定类型的缓存
	 */
	static function store($type)
	{
		return self::initInstance($type);
	}
}
