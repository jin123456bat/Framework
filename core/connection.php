<?php
namespace framework\core;
use framework\core\protocal\protocal;

/**
 * @author jin
 *
 */
class connection extends component
{
	/**
	 * @var resource
	 */
	private $_socket;
	
	/**
	 * @var protocal
	 */
	private $_protocal;
	
	/**
	 * @param resource $socket
	 */
	function __construct($socket)
	{
		$this->_socket = $socket;
		
		$config = self::getConfig(base::$APP_CONF);
		$class_name= 'framework\\core\\protocal\\driver\\'.$config['protocal'];
		if (class_exists($class_name,true))
		{
			$this->_protocal = new $class_name();
			if (method_exists($this->_protocal, 'initlize'))
			{
				call_user_func(array($this->_protocal,'initlize'));
			}
		}
	}
	
	/**
	 * 连接初始化
	 * {@inheritDoc}
	 * @see \framework\core\base::initlize()
	 */
	function initlize()
	{
		if (!empty($this->_protocal))
		{
			return $this->_protocal->init($this);
		}
	}
	
	/**
	 * @param string $buffer
	 * @return int|boolean  成功返回写入的字节数  失败返回false
	 */
	function write($buffer,$raw = false)
	{
		if (!$raw)
		{
			$buffer = $this->_protocal->encode($buffer);
		}
		return socket_write($this->_socket, $buffer,strlen($buffer));
	}
	
	/**
	 * 从socket读取数据
	 * @param number $length
	 * @return string
	 */
	function read($length = 4096)
	{
		$buffer = '';
		do{
			$str = socket_read($this->_socket, 4096);
			$buffer.=$str;
		}while(strlen($str)==4096);
		return $buffer;
	}
	
	/**
	 * 获取socket
	 * @return resource
	 */
	function getSocket()
	{
		return $this->_socket;
	}
	
	/**
	 * 获取protocal
	 * @return \framework\core\protocal\protocal
	 */
	function getProotcal()
	{
		return $this->_protocal;
	}
	
	/**
	 * 获取该链接的id
	 */
	function id()
	{
		return (int)$this->_socket;
	}
	
	function close()
	{
		socket_close($this->_socket);
	}
}