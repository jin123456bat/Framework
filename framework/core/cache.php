<?php
namespace framework\core;

use framework;

class cache extends component
{
	private $_expires;
	
	private $_type;
	
	private $_instance;
	
	function setType($type = NULL)
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
	}
	
	/**
	 * 设置默认的数据有效期
	 *
	 * @param unknown $expires
	 */
	function expires($expires)
	{
		$this->_expires = $expires;
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
	function set($name, $value, $expires = 0)
	{
		$expires = empty($expires)?$this->_expires:$expires;
		return $this->_instance->set($name, $value, $expires);
	}
	
	/**
	 * 自增
	 *
	 * @param unknown $name
	 * @param number $amount
	 * @return bool true on success or false on failure
	 */
	function increase($name, $amount = 1)
	{
		return $this->_instance->increase($name, $amount);
	}
	
	/**
	 * 自减
	 *
	 * @param unknown $name
	 * @param number $amount
	 * @return bool true on success or false on failure
	 */
	function decrease($name, $amount = 1)
	{
		return $this->_instance->decrease($name, $amount);
	}
	
	/**
	 * 获取数据
	 *
	 * @param unknown $name
	 * @param $default NULL
	 *        当数据不存在的时候的默认值
	 * @return mixed|unknown
	 */
	function get($name, $default = NULL)
	{
		$value = $this->_instance->get($name);
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
	function remove($name)
	{
		return $this->_instance->remove($name);
	}
	
	/**
	 * 清空所有缓存
	 */
	function flush()
	{
		return $this->_instance->flush();
	}
	
	/**
	 * 判断缓存是否存在
	 *
	 * @param string $name
	 * @return bool
	 */
	function has($name)
	{
		return $this->_instance->has($name);
	}
	
	/**
	 * 和set相同，不同的是假如原来的name存在了，会失败，并且返回false
	 * 有效期为默认有效期
	 *
	 * @param unknown $name
	 * @param unknown $value
	 * @return boolean
	 */
	function add($name, $value)
	{
		return $this->_instance->add($name, $value, $this->_expires);
	}
}
