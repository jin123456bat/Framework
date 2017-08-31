<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

class redis extends cacheBase implements cache
{

	/**
	 * @var \Redis
	 */
	private $_redis = array();

	function __construct($config)
	{
		if (isset($config['redis']))
		{
			if (array_key_exists('host', $config['redis']))
			{
				$redis = new \Redis();
				$host = $config['redis']['host'];
				$port = isset($config['redis']['port'])?$config['redis']['port']:6379;
				$timeout = isset($config['redis']['timeout'])?$config['redis']['timeout']:0;
				$redis->connect($host, $port,$timeout);
				if (isset($config['redis']['password']))
				{
					$redis->auth($config['redis']['password']);
				}
				$database = isset($config['redis']['database'])?$config['redis']['database']:0;
				$redis->select($database);
				// 这里添加服务器乱七八糟的
				$this->_redis[] = $redis;
			}
			else
			{
				foreach ($config['redis'] as $c)
				{
					$redis = new \Redis();
					
					$host = $c['host'];
					$port = isset($c['port'])?$c['port']:6379;
					$timeout = isset($c['timeout'])?$c['timeout']:0;
					$database = isset($c['database'])?$c['database']:0;
					$redis->connect($host, $port,$timeout);
					if (isset($c['password']))
					{
						$redis->auth($c['password']);
					}
					$redis->select($database);
					// 这里添加服务器乱七八糟的
					$this->_redis[] = $redis;
				}
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
		if (!$this->has($name))
		{
			return $this->set($name, $value,$expires);
		}
		return false;
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
		$serverNo = $this->getServerNo($name, count($this->_redis));
		$content = serialize(array(
			'content' => $value,
			'expires' => $expires,
			'starttime' => time(),
		));
		return $this->_redis[$serverNo]->set(md5($name),$content);
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
		$serverNo = $this->getServerNo($name, count($this->_redis));
		$result = $this->_redis[$serverNo]->get(md5($name));
		if ($result === false)
		{
			return null;
		}
		$result = unserialize($result);
		if ($result['expires']!=0 && time() > $result['expires'] + $result['starttime'])
		{
			return null;
		}
		return $result['content'];
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
		$result = $this->get($name);
		return $this->set($name, $result + $amount);
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
		$result = $this->get($name);
		return $this->set($name, $result - $amount);
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
		$serverNo = $this->getServerNo($name, count($this->_redis));
		return $this->_redis[$serverNo]->exists(md5($name));
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
		$serverNo = $this->getServerNo($name, count($this->_redis));
		return $this->_redis[$serverNo]->delete(md5($name));
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
		foreach ($this->_redis as $redis)
		{
			return $redis->flushDB();
		}
		return true;
	}
}