<?php
namespace framework\core;

/**
 * @author fx
 *
 */
class webSocket extends component
{
	/**
	 * 监听端口号
	 * @var integer
	 */
	private $_port = 2000;
	
	/**
	 * 链接超时时间
	 * @var integer
	 */
	private $_timeout = 60;
	
	/**
	 * 最大链接数量
	 * @var integer
	 */
	private $_max_connection = 3;
	
	/**
	 * 当前链接socket
	 * @var unknown
	 */
	private $_master = NULL;
	
	/**
	 * 消息长度  最长不能超过这个数量否则会出现数据丢失
	 * @var integer
	 */
	private $_message_length = 2048;
	
	static private $_sockets = array();
	
	private $_callback = array(
		'open' => NULL,//当链接打开的时候触发函数
		'error' => NULL,//socket错误函数
		'disconnect' => NULL,//断开链接函数
		''
	);
	
	function initlize()
	{
		$this->_master = socket_create_listen( $this->_port );
		self::$_sockets[] = $this->_master;
		console::log('Server Startted on '.$this->_port.'!');
		parent::initlize();
	}
	
	public function run($onmessage = NULL)
	{
		$read = self::$_sockets;
		$write = NULL;
		$except = NULL;
		//socket_select  必须收到一个socket信息才会执行下一步，否则会一直在这里阻塞
		socket_select($read, $write, $except, $this->_timeout);
		foreach ($read as $socket)
		{
			if ($socket == $this->_master)
			{
				$client = socket_accept($this->_master);//接收新的链接
				if ($client === false)
				{
					console::log('connect failed',console::TEXT_COLOR_RED);
					continue;
				}
				else
				{
					if (count(self::$_sockets)> $this->_max_connection){
						continue;
					}
					self::$_sockets[] = $client;
				}
			}
			else
			{
				$buffer = $this->read($socket);
				if ($buffer===false || empty($buffer))
				{
					$this->close($socket);
				}
				else
				{
					$socketid = (int)$socket;
					if (!isset($this->_handshake[$socketid]) || $this->_handshake[$socketid]===false)
					{
						$this->_http_request = $buffer;
						return $this->handShanke($socket);
					}
					else
					{
						$buffer = $this->decode($buffer);
						if (is_callable($onmessage))
						{
							$request = json_decode($buffer,true);
							$router = application::load('router');
							$router->appendParameter($request);
							$router->parse();
							$control = $router->getControlName();
							$action = $router->getActionName();
							return $onmessage($control,$action);
						}
					}
				}
			}
		}
	}
	
	/**
	 * 从socket中读取数据
	 * @param unknown $socket
	 * @return string
	 */
	private function read($socket)
	{
		return socket_read($socket, $this->_message_length);
	}
	
	/**
	 * 关闭socket链接
	 * @param unknown $socket
	 */
	private function close($socket)
	{
		$this->call('disconnect',$socket);
		$index = array_search( $socket, self::$_sockets );
		socket_close( $socket );
		array_splice(self::$_sockets, $index, 1 );
	}
	
	/**
	 * 向socket写入信息
	 * @param unknown $message
	 * @param unknown $socket = NULL 当为空的时候为向所有客户端发送
	 */
	public function write($message,$socket = NULL)
	{
		$message = $this->encode($message);
		
		if (empty($socket))
		{
			$num = 0;
			foreach (self::$_sockets as $socket)
			{
				if ($socket!=$this->_master)
				{
					$result = socket_write($socket, $message,strlen($message));
					if ($result === fasle)
					{
						call_user_func(array($this,'error'),'error',socket_last_error($socket),socket_strerror(socket_last_error($socket)));
					}
					else
					{
						$num++;
					}
				}
			}
			return $num;
		}
		else
		{
			$bytes = socket_write($socket,$message, strlen($message));
			if(false === $bytes)
			{
				call_user_func(array($this,'error'),'error',socket_last_error($socket),socket_strerror(socket_last_error($socket)));
			}
			return $bytes;
		}
	}
	
	public function receive($message,$socket)
	{
		
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
		$this->_handshake[(int)$socket] = true;
		$message = 
		"HTTP/1.1 101 Switching Protocols\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
		"\r\n";
		return $message;
	}
	
	/**
	 * 判断当前请求是否是websocket链接
	 * @return boolean
	 */
	protected function isWebSocket()
	{
		return $this->getKey('Connection') == 'Upgrade' && $this->getKey('Upgrade') == 'websocket';
	}
	
	/**
	 * 获取当websocket第一次链接的时候发送的http请求中的请求参数
	 * @param string $name 请求的Key
	 * @return string|boolean 成功返回字符串，失败返回false
	 */
	private function getKey($name)
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
	
	private function call($type)
	{
		
	}
}