<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

/**
 * 对于memcached已经考虑过负载均衡，
 * 
 * @author fx
 */
class memcached extends cacheBase implements cache
{
	/**
	 *
	 * @var \Memcached
	 */
	private $_memcached;

	function __construct($config)
	{
		$this->_memcached = new \Memcached();
		if (isset($config['memcached']) && ! empty($config['memcached']))
		{
			if (! isset($config['memcached']['host']))
			{
				$array = array();
				foreach ($config['memcached'] as $server)
				{
					$data = array(
						$server['host'],
						$server['port'],
						$server['weight']
					);
					$array[] = $data;
					// $this->_memcached->addServer($server['host'],$server['port'],$server['weight']);
				}
				$this->_memcached->addServers($array);
			}
			else
			{
				$this->_memcached->addServer($config['memcached']['host'], $config['memcached']['port'], $config['memcached']['weight']);
			}
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::add()
	 */
	public function add($name, $value, $expires = 0)
	{
		return $this->_memcached->set($name, $value, $expires);
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
		return $this->_memcached->set($name, $value, $expires);
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
		return $this->_memcached->get($name);
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
		if ($this->_memcached->increment($name, $amount))
		{
			return true;
		}
		return false;
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
		if ($this->_memcached->decrement($name, $amount))
		{
			return true;
		}
		return false;
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
		$result = ! $this->_memcached->add($name, 0);
		if (! $result)
		{
			$this->remove($name);
		}
		return $result;
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
		return $this->_memcached->delete($name, 0);
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
		return $this->_memcached->flush();
	}

	public function __destruct()
	{
		$this->_memcached->quit();
	}
}