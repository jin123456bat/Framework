<?php
namespace framework\core\protocal\driver;

use framework\core\protocal\protocal;
use framework\core\connection;

/**
 * websocket协议貌似全部都是get
 * @author jin
 *
 */
class websocket implements protocal
{
	private $_server = array();
	
	private $_cookie = array();
	
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
		
		$this->_server = array();
		
		$this->_server['REQUEST_TIME'] = time();
		$this->_server['REQUEST_TIME_FLOAT'] = microtime(true);
		$this->_server['QUERY_STRING'] = '';
		
		socket_getpeername($connection->getSocket(),$this->_server['REMOTE_ADDR'],$this->_server['REMOTE_PORT']);
		
		$header = explode("\r\n", $request);
		$head = array_shift($header);
		list($method,$path,$version) = explode(' ', $head,3);
		$method = trim($method);
		$path = trim($path);
		$version = trim($version);
		
		$this->_server['SERVER_PROTOCOL'] = $version;
		$this->_server['REQUEST_METHOD'] = $method;
		$this->_server['REQUEST_URI'] = $path;
		$this->_server['SCRIPT_NAME'] = parse_url($path, PHP_URL_PATH);
		
		$this->_server['QUERY_STRING'] = parse_url($path,PHP_URL_QUERY);
		if (!empty($this->_server['QUERY_STRING']))
		{
			parse_str($this->_server['QUERY_STRING'],$this->_get);
		}
		
		if (!in_array(strtolower($method), array('get')))
		{
			$message = "HTTP/1.1 400 Bad Request\r\n\r\n<b>400 Bad Request</b>";
			$connection->write($message, true);
			$connection->close();
			return false;
		}
		
		//处理其他的请求头
		foreach ($header as $head)
		{
			if (!empty($head))
			{
				list($name,$value) = explode(':', $head,2);
				$name = strtolower(trim($name));
				if (!in_array($name, array(
					'cookie'
				)))
				{
					$this->_server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = trim($value);
				}
				else
				{
					switch ($name)
					{
						case 'cookie':
							//处理cookie的header
							parse_str(str_replace('; ', '&', $value), $this->_cookie);
							break;
					}
				}
			}
		}
		
		if (isset($this->_server['HTTP_SEC_WEBSOCKET_KEY']))
		{
			$Sec_WebSocket_Key = $this->_server['HTTP_SEC_WEBSOCKET_KEY'];
			
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
		$data = array(
			'_GET' => json_decode($string,true),
			'_POST'=> array(),
			'_COOKIE' => $this->_cookie,
			'_SERVER' => $this->_server,
			'_FILES' => array(),
			'_REQUEST' => array(),
			'_SESSION' => array(),
		);
		return $data;
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