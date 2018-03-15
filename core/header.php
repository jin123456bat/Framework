<?php
namespace framework\core;

class header extends base
{

	private $_header = array();

	/**
	 * constructor
	 * 
	 * @param array $array
	 *        = array()
	 * @example new header(array(
	 *          'Content-Type: application/json',
	 *          ));
	 *          or
	 *          new header(array(
	 *          'Content-Type' => 'application/json',
	 *          ));
	 */
	function __construct($array = array())
	{
		$headers = headers_list();
		foreach ($headers as $header)
		{
			$header = explode(":", $header);
			$this->_header[trim(array_shift($header))] = trim(implode(":", $header));
		}
		foreach ($array as $key => $value)
		{
			if (is_int($key))
			{
				$this->add($value);
			}
			else
			{
				$this->_header[$key] = $value;
			}
		}
	}

	/**
	 * 添加一个header
	 * 
	 * @param unknown $key        
	 * @param string $value        
	 * @example add("Location: http://www.baidu.com");
	 *          add("Location","http://www.baidu.com");
	 */
	function add($key, $value = null)
	{
		if (empty($value))
		{
			list($name,$value) = explode(":", $key,2);
			
			$name = trim($name);
			$value = trim($value);
			
			if (isset($this->_header[$name]))
			{
				if (is_array($this->_header[$name]))
				{
					$this->_header[$name][] = $value;
				}
				else
				{
					$this->_header[$name] = array(
						$this->_header[$name],
						$value
					);
				}
			}
			else
			{
				$this->_header[$name] = $value;
			}
		}
		else
		{
			$this->_header[$key] = $value;
		}
	}

	/**
	 * 检查header是否存在
	 * 
	 * @param unknown $string        
	 * @return boolean
	 */
	function check($string)
	{
		if (isset($this->_header[$string]))
		{
			return true;
		}
		return in_array($string, $this->_header);
	}

	/**
	 * 删除一个header，假如有同名的 则只会删除一个
	 * 
	 * @param unknown $string        
	 */
	function delete($string)
	{
		foreach ($this->_header as $key => $value)
		{
			if ($key === $string)
			{
				unset($this->_header[$key]);
				return true;
			}
			
			if ($key . ': ' . $value === $string)
			{
				unset($this->_header[$key]);
				return true;
			}
			header_remove(substr($string, 0, strpos($string, ':')));
		}
	}

	/**
	 * 设置一个头信息，已经设置的会被覆盖
	 * 
	 * @param unknown $name
	 * @param unknown $value 默认NULL
	 */
	function set($name, $value = NULL)
	{
		if (empty($value))
		{
			if (is_string($name))
			{
				list($name,$value) = explode(':', $name,2);
				$this->_header[$name] = $value;
			}
			else if (is_array($name))
			{
				foreach ($name as $key => $value)
				{
					if (is_int($key) && is_string($value))
					{
						$this->set($value);
					}
					else if (is_string($key) && is_string($value))
					{
						$this->set($key,$value);
					}
				}
			}
		}
		else if (is_string($name))
		{
			$this->_header[$name] = $value;
		}
	}

	/**
	 * 根据建名获取已经设置的头信息
	 * 
	 * @param unknown $name        
	 * @return mixed
	 */
	function get($name)
	{
		return $this->_header[$name];
	}
	
	/**
	 * 获取所有的已经设置的头信息
	 * @return array
	 */
	function getAll()
	{
		return $this->_header;
	}

	/**
	 * 立即发送一个header
	 * 
	 * @param unknown $key        
	 * @param string $value        
	 */
	static function send($key, $value = null)
	{
		if (env::php_sapi_name() == 'web')
		{
			if (empty($value))
			{
				header($key, true);
			}
			else
			{
				header($key . ': ' . $value, true);
			}
		}
	}

	/**
	 * 发送所有hander
	 */
	function sendAll()
	{
		if (env::php_sapi_name() == 'web') 
		{
			foreach ($this->_header as $key => $value)
			{
				header($key . ': ' . $value, true);
			}
		}
	}
}
