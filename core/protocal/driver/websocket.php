<?php
namespace framework\core\protocal\driver;

use framework\core\protocal\protocal;
use framework\core\connection;

class websocket implements protocal
{
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\protocal\protocal::init()
	 * @param connection $connection        
	 * @return void
	 */
	public function init($connection)
	{
		// TODO Auto-generated method stub
		$request = $connection->read();
		if (preg_match('/Sec-WebSocket-Key: (.*)/i', $request, $match))
		{
			$Sec_WebSocket_Key = trim($match[1]);
			
			$new_key = base64_encode(sha1($Sec_WebSocket_Key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
			
			$message = "HTTP/1.1 101 Switching Protocols\r\n";
			$message .= "Upgrade: websocket\r\n";
			$message .= "Sec-WebSocket-Version: 13\r\n";
			$message .= "Connection: Upgrade\r\n";
			$message .= "Server: framework\r\n";
			$message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
			$connection->_init = true;
			$connection->write($message, true);
			return false;
		}
		else
		{
			$message = "HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b>没有找到websocket_key";
			$connection->write($message, true);
			$connection->close();
			return false;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\protocal\protocal::encode()
	 */
	public function encode($string)
	{
		// TODO Auto-generated method stub
		$len = strlen($string);
		if ($len <= 125)
		{
			return "\x81" . chr($len) . $string;
		}
		else if ($len <= 65535)
		{
			return "\x81" . chr(126) . pack("n", $len) . $string;
		}
		else
		{
			return "\x81" . chr(127) . pack("xxxxN", $len) . $string;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\protocal\protocal::decode()
	 */
	public function decode($buffer)
	{
		$len = $masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;
		if ($len === 126)
		{
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		}
		else if ($len === 127)
		{
			$masks = substr($buffer, 10, 4);
			$data = substr($buffer, 14);
		}
		else
		{
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 6);
		}
		for ($index = 0; $index < strlen($data); $index ++)
		{
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		return $decoded;
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::parse()
	 */
	public function parse($string)
	{
		return array(
			'_GET' => json_decode($string,true),
			'_POST'=> array(),
			'_COOKIE' => array(),
			'_SERVER' => array(),
			'_FILES' => array(),
			'_REQUEST' => array(),
			'_SESSION' => array(),
		);
	}
	
	/**
	 * 发送完数据后是否需要关闭连接
	 * @return boolean
	 */
	public function closeAfterWrite()
	{
		return false;
	}
}