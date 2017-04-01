<?php
namespace framework\core\socket;

use framework\core\socket;
use framework\core\console;

abstract class websocket extends socket
{
	private $_handshake = array();
	
	private $_http_request = '';
	
	function initlize()
	{
		parent::initlize();	
	}
	
	/**
	 * 重写socket的receive函数 这个函数到这里就为止了 不允许在继续重写
	 * {@inheritDoc}
	 * @see \framework\core\socket::receive()
	 */
	final public function receive($message,$socket)
	{
		$socketid = (int)$socket;
		if (!isset($this->_handshake[$socketid]) || $this->_handshake[$socketid]===false)
		{
			$this->_http_request = $message;
			$this->handShanke($socket);
		}
		else
		{
			var_dump($this->_handshake);
			$this->message($this->decode($message),$socket);
		}
	}
	
	public function message($message,$socket)
	{
		console::log($message,console::TEXT_COLOR_YELLOW);
	}
	
	/**
	 * 解码websocket发送过来的数据
	 * @param string $buffer
	 * @return NULL|boolean
	 */
	protected function decode($buffer)
	{
		$len = $masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;
		if ($len === 126)  {
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		} else if ($len === 127)  {
			$masks = substr($buffer, 10, 4);
			$data = substr($buffer, 14);
		} else  {
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 6);
		}
		for ($index = 0; $index < strlen($data); $index++) {
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		return $decoded;
	}
	
	/**
	 * 发送到websocket的时候需要编码
	 * @param string $string
	 * @return string
	 */
	protected function encode($string)
	{
		$a = str_split($string, 125);
		if (count($a) == 1) {
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		$ns = "";
		foreach ($a as $o) {
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		return $ns;
	}
	
	/**
	 * 处理websocket链接的握手程序
	 */
	protected function handShanke($socket)
	{
		$acceptKey = $this->getAccpetKey();
		$message = "HTTP/1.1 101 Switching Protocols\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
			"\r\n";
		parent::write($message,$socket);
		$this->_handshake[(int)$socket] = true;
	}
	
	protected function write($message,$socket = NULL)
	{
		$message = $this->encode($message);
		return parent::write($message,$socket);
	}
	
	/**
	 * 判断是否是websocket链接
	 * @return boolean
	 */
	public function isWebSocket()
	{
		return $this->getKey('Connection') == 'Upgrade' && $this->getKey('Upgrade') == 'websocket';
	}
	
	/**
	 * 当socket断开链接的时候触发
	 * {@inheritDoc}
	 * @see \framework\core\socket::disconnect()
	 */
	public function disconnect($socket)
	{
		unset($this->_handshake[(int)$socket]);
		parent::disconnect($socket);
	}
	
	/**
	 * 当新链接链接到服务器的时候触发函数
	 * {@inheritDoc}
	 * @see \framework\core\socket::open()
	 */
	public function open($client)
	{
		parent::open($client);
	}
	
	/**
	 * 获取当websocket第一次链接的时候发送的http请求中的请求参数
	 * @param string $name 请求的Key
	 * @return string|boolean 成功返回字符串，失败返回false
	 */
	public function getKey($name)
	{
		if (preg_match('/'.$name.': (.*)/i', $this->_http_request, $match))
		{
			return trim($match[1]);
		}
		return false;
	}
	
	/**
	 * 获取websocket链接需要的AccpetKey
	 * @return string
	 */
	private function getAccpetKey()
	{
		$key = $this->getKey('Sec-WebSocket-Key');
		if ($key !== false)
		{
			$mask = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
			return base64_encode(sha1($key . $mask, true));
		}
		return '';
	}
}