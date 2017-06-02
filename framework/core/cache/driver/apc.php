<?php
namespace framework\core\cache\driver;
use framework\core\cache\cache;
use framework\core\base;

class apc extends base implements cache
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::add()
	 */
	public function add($name,$value,$expires = 0)
	{
		// TODO Auto-generated method stub
		return apc_add($name, $value,$expires);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::set()
	 */
	public function set($name,$value,$expires = 0)
	{
		// TODO Auto-generated method stub
		apc_delete($name);
		return apc_add($name, $value,$expires);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::get()
	 */
	public function get($name)
	{
		// TODO Auto-generated method stub
		$success = false;
		$value = apc_fetch($name,$success);
		if ($success)
		{
			return $value;
		}
		return null;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name,$amount = 1)
	{
		// TODO Auto-generated method stub
		$success = false;
		apc_inc($name,$amount,$success);
		return $success;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name,$amount = 1)
	{
		// TODO Auto-generated method stub
		$success = false;
		apc_dec($name,$amount,$success);
		return $success;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::has()
	 */
	public function has($name)
	{
		// TODO Auto-generated method stub
		return apc_exists($name);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::remove()
	 */
	public function remove($name)
	{
		// TODO Auto-generated method stub
		return apc_delete($name);
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::flush()
	 */
	public function flush()
	{
		// TODO Auto-generated method stub
		return apc_clear_cache();
	}

	
}