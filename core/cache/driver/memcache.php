<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

/**
 * 对于memcached已经考虑过负载均衡，
 * 
 * @author fx
 */
class memcache extends cacheBase implements cache
{
	/**
	 *
	 * @var \Memcache
	 */
	private $_memcache;

	function __construct($config)
	{
		$this->_memcache = new \Memcache();
		if (isset($config['memcache']) && ! empty($config['memcache']))
		{
			if (! isset($config['memcache']['host']))
			{
				foreach ($config['memcache'] as $server)
				{
					$host = $server['host'];
					$port = $server['port'];
					$weight = $server['weight'];
					$this->_memcache->addserver($host,$port,null,$weight);
				}
			}
			else
			{
				$this->_memcache->addServer($config['memcache']['host'], $config['memcache']['port'], $config['memcache']['weight']);
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
		return $this->_memcache->add($name, $value,0, $expires);
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
		return $this->_memcache->set($name, $value,0, $expires);
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
		$result = $this->_memcache->get($name);
		if ($result === false)
		{
			return null;
		}
		return $result;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name, $amount = 1)
	{
		$value = $this->get($name);
		if ($value === null)
		{
			return $this->set($name, $amount);
		}
		return $this->set($name, $value+$amount);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name, $amount = 1)
	{
		$value = $this->get($name);
		if ($value === null)
		{
			return $this->set($name, -$amount);
		}
		return $this->set($name, $value-$amount);
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
		return $this->get($name)!==false;
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
		return $this->_memcache->delete($name, 0);
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
		return $this->_memcache->flush();
	}

	public function __destruct()
	{
		$this->_memcache->close();
	}
}