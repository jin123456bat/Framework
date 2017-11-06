<?php
namespace framework\core;
use framework\core\protocal\protocal;

class connection extends base
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
	 * @var boolean
	 */
	public $_init = false;
	
	/**
	 * @param resource $socket
	 * @param protocal $protocal
	 */
	function __construct($socket,protocal $protocal)
	{
		$this->_socket = $socket;
		$this->_protocal = $protocal;
	}
	
	/**
	 * @param string $buffer
	 * @return int|boolean  成功返回写入的字节数  失败返回false
	 */
	function send($buffer,$raw = false)
	{
		if (!$raw)
		{
			$buffer = $this->_protocal->encode($buffer);
		}
		//var_dump($buffer);
		return socket_write($this->_socket, $buffer,strlen($buffer));
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