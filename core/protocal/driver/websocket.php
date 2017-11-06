<?php
namespace framework\core\protocal\driver;
use framework\core\protocal\protocal;
use framework\core\connection;

class websocket implements protocal
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::init()
	 * @param string $request 请求原文 未解码的数据
	 * @param connection $connection
	 * @return void
	 */
	public function init($request,$connection)
	{
		// TODO Auto-generated method stub
		if (!$connection->_init)
		{
			if (preg_match('/Sec-WebSocket-Key: (.*)/i', $request, $match))
			{
				$Sec_WebSocket_Key = trim($match[1]);
				
				$new_key = base64_encode(sha1($Sec_WebSocket_Key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));
				
				$message= "HTTP/1.1 101 Switching Protocols\r\n";
				$message.= "Upgrade: websocket\r\n";
				$message.= "Sec-WebSocket-Version: 13\r\n";
				$message.= "Connection: Upgrade\r\n";
				$message.= "Server: framework\r\n";
				$message.= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
				$connection->_init = true;
				$connection->send($message,true);
				return false;
			}
			else
			{
				$message = "HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b>没有找到websocket_key";
				$connection->send($message,true);
				$connection->close();
				return false;
			}
		}
		else
		{
			return true;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::encode()
	 */
	public function encode($string)
	{
		// TODO Auto-generated method stub
		$a = str_split($string, 125);
		if (count($a) == 1)
		{
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		$ns = "";
		foreach ($a as $o)
		{
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		return $ns;
	}

	/**
	 * {@inheritDoc}
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
	 * @see \framework\core\protocal\protocal::get()
	 */
	public function get($string)
	{
		return json_decode($string,true);
	}
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::post()
	 */
	public function post($string)
	{
		return json_decode($string,true);
	}
	
	public function cookie($string)
	{
		return array();
	}
	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::server()
	 */
	public function server($buffer)
	{
		// TODO Auto-generated method stub
		return $_SERVER;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::files()
	 */
	public function files($buffer)
	{
		// TODO Auto-generated method stub
		return $_FILES;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::request()
	 */
	public function request($buffer)
	{
		// TODO Auto-generated method stub
		return $_REQUEST;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::env()
	 */
	public function env($buffer)
	{
		// TODO Auto-generated method stub
		return $_ENV;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\protocal\protocal::session()
	 */
	public function session($buffer)
	{
		// TODO Auto-generated method stub
		return $_SESSION;
	}

}