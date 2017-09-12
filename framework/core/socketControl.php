<?php
namespace framework\core;

class socketControl extends control
{

	function initlize()
	{
		if (request::php_sapi_name() != 'socket')
		{
			return new response('not found', 404);
		}
		return parent::initlize();
	}

	/**
	 * 获取前端传递的参数
	 * 
	 * @param unknown $name        
	 * @param unknown $default        
	 * @param unknown $filter        
	 * @return unknown|string
	 */
	function getParam($name, $default = NULL, $filter = NULL)
	{
		if (isset($GLOBALS['WEBSOCKET'][$name]))
		{
			return $GLOBALS['WEBSOCKET'][$name];
		}
		return $default;
	}

	/**
	 * 获取当前发送消息的客户端
	 * 
	 * @return unknown
	 */
	static function getSocket()
	{
		return $GLOBALS['SOCKET_CLIENT'];
	}

	/**
	 * 获取当前链接的所有的客户端
	 */
	static function getSockets()
	{
		return webSocket::$_sockets;
	}

	/**
	 * 发送给所有客户端
	 * 
	 * @param unknown $msg        
	 */
	static function sendAllClient($msg)
	{
		$msg = webSocket::encode($msg . '');
		webSocket::write($msg);
	}

	/**
	 * 重写control中的echo socket通信不依赖于echo
	 * 
	 * {@inheritdoc}
	 *
	 * @see \framework\core\control::__output()
	 */
	function __output($msg)
	{
		$msg = webSocket::encode($msg . '');
		webSocket::write($msg, self::getSocket());
	}

	function close($socket)
	{
		socket_close($socket);
	}

	/**
	 * 当链接开始的时候会触发这个函数
	 * 
	 * @param unknown $client        
	 */
	function __open($client)
	{
		console::log('socket connect success ' . $client);
	}

	/**
	 * 当链接中断的时候会触发这个函数
	 * 
	 * @param unknown $socket        
	 */
	function __disconnect($socket)
	{
		console::log('socket connect disconnect ' . $socket);
	}

	/**
	 * 当socket底层出现错误的时候会触发这个函数
	 * 
	 * @param unknown $level        
	 * @param unknown $errno        
	 * @param unknown $error        
	 */
	public function __error($level, $errno, $error)
	{
		console::log('[' . date('Y-m-d H:i:s') . '][' . $level . '] socket error: (' . $errno . ') ' . $error, console::TEXT_COLOR_RED);
	}
}