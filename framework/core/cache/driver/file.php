<?php
namespace framework\core\cache\driver;
use framework\core\base;
use framework\core\cache\cache;

/**
 * 基于文件的缓存
 * @author fx
 *
 */
class file extends base implements cache
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::add()
	 */
	public function add($name,$value,$expires = 0)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::set()
	 */
	public function set($name,$value,$expires = 0)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::get()
	 */
	public function get($name)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name,$amount = 1)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name,$amount = 1)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::has()
	 */
	public function has($name)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::remove()
	 */
	public function remove($name)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::flush()
	 */
	public function flush()
	{
		// TODO Auto-generated method stub
		
	}

	
}