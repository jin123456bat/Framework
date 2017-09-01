<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

/**
 * apc只是存储在本机
 * 
 * @author fx
 */
class apc extends cacheBase implements cache
{
	/**
	 * 判断系统应该使用apcu函数还是apc函数
	 * apc=apcu+opcache
	 * apcu貌似从性能上要比apc要好
	 * 为true的话使用apcu
	 * 默认使用apcu
	 * 
	 * @var boolean
	 */
	private $_apc_or_apcu;
	
	function __construct()
	{
		$this->_apc_or_apcu = function_exists('apcu_add');
	}
	
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::add()
	 */
	public function add($name, $value, $expires = 0)
	{
		// TODO Auto-generated method stub
		if ($this->_apc_or_apcu)
		{
			return apcu_add($name, $value, $expires);
		}
		return apc_add($name, $value,$expires);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::set()
	 */
	public function set($name, $value, $expires = 0)
	{
		// TODO Auto-generated method stub
		if ($this->_apc_or_apcu)
		{
			//apcu_delete($name);
			return apcu_store($name, $value, $expires);
		}
		else
		{
			//apc_delete($name);
			return apcu_store($name, $value, $expires);
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::get()
	 */
	public function get($name)
	{
		// TODO Auto-generated method stub
		$success = false;
		$value = $this->_apc_or_apcu?apcu_fetch($name, $success):apc_fetch($name,$success);
		if ($success)
		{
			return $value;
		}
		return null;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name, $amount = 1)
	{
		// TODO Auto-generated method stub
		$success = false;
		$this->set($name, $this->get($name)+$amount,$success);
		return $success;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name, $amount = 1)
	{
		// TODO Auto-generated method stub
		//apcu_dec函数要求原来的值必须是int类型，string类型的数据会失败
		//apcu_dec($name, $amount, $success);
		$success = false;
		$this->set($name, $this->get($name)-$amount,$success);
		return $success;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::has()
	 */
	public function has($name)
	{
		// TODO Auto-generated method stub
		return $this->_apc_or_apcu?apcu_exists($name):apc_exists($name);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::remove()
	 */
	public function remove($name)
	{
		// TODO Auto-generated method stub
		return $this->_apc_or_apcu?apcu_delete($name):apc_delete($name);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::flush()
	 */
	public function flush()
	{
		// TODO Auto-generated method stub
		return $this->_apc_or_apcu?apcu_clear_cache():apc_clear_cache();
	}
}