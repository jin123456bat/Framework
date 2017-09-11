<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

/**
 * 基于文件的缓存
 * 
 * @author fx
 */
class file extends cacheBase implements cache
{

	private $_path;

	private $_content;

	public function __construct($config)
	{
		$this->_path = $config['file']['path'];
		
		array_map(function ($file) {
			if ($file != '.' && $file != '..')
			{
				$file = rtrim($this->_path, '/\\') . '/' . $file;
				$data = unserialize(file_get_contents($file));
				if ($data->expires != 0 && $data->expires + filemtime($file) < time())
				{
					@unlink($file);
				}
			}
		}, scandir($this->_path));
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
		$file = $this->getFileByName($name);
		if (! file_exists($file))
		{
			if (file_put_contents($file, $this->serialize($value, $expires), LOCK_EX))
			{
				return true;
			}
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
		$file = $this->getFileByName($name);
		if (file_put_contents($file, $this->serialize($value, $expires), LOCK_EX))
		{
			return true;
		}
		return false;
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
		$file = $this->getFileByName($name);
		if (file_exists($file))
		{
			return $this->getContentFromFile($file);
		}
		return NULL;
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
		// 这里注意不能直接get 然后 set 因为过期时间会刷新，而实际上不希望刷新过期时间
		if (! $this->has($name))
		{
			$result = $this->serialize(1, 0);
		}
		else
		{
			$file = $this->getFileByName($name);
			$result = unserialize(file_get_contents($file));
			$result->data += $amount;
			$result = serialize($result);
		}
		if(file_put_contents($file, $result))
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
		if (! $this->has($name))
		{
			$result = $this->serialize(1, 0);
		}
		else
		{
			$file = $this->getFileByName($name);
			$result = unserialize(file_get_contents($file));
			$result->data -= $amount;
			$result = serialize($result);
		}
		if(file_put_contents($file, $result))
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
		$file = $this->getFileByName($name);
		if (file_exists($file))
		{
			$result = unserialize(file_get_contents($file));
			if ($result->expires == 0 || $result->expires + filemtime($file) >= time())
			{
				return true;
			}
		}
		return false;
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
		return @unlink($this->getFileByName($name));
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
		array_map(function ($file) {
			if ($file != '.' && $file != '..')
			{
				$file = rtrim($this->_path, '/\\') . '/' . $file;
				@unlink($file);
			}
		}, scandir($this->_path));
		return true;
	}

	/**
	 * 获取文件完整路径
	 * 
	 * @param unknown $name        
	 * @return string
	 */
	private function getFileByName($name)
	{
		$filename = md5($name);
		$file = rtrim($this->_path, '/\\') . '/' . $filename;
		return $file;
	}

	/**
	 * 将数据变化为字符串
	 * 
	 * @param unknown $content        
	 * @return string
	 */
	private function serialize($content, $expires)
	{
		$data = new \stdClass();
		$data->expires = $expires;
		$data->data = $content;
		return serialize($data);
	}

	/**
	 * 获取文件中保存的内容
	 * 
	 * @param unknown $file        
	 */
	private function getContentFromFile($file)
	{
		$result = unserialize(file_get_contents($file));
		if ($result->expires == 0 || $result->expires + filemtime($file) >= time())
		{
			return $result->data;
		}
		return NULL;
	}
}