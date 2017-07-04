<?php
namespace framework\core;

/**
 * @author fx
 *
 */
class webSocket extends component
{
	public $_name = 'webSocket';
	
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
	
	public static $_sockets = array();
	
	function initlize()
	{
		ini_set('max_execution_time', 0);
		//设置名称
		$this->_name = explode('\\', get_class($this));
		$this->_name = ucwords(end($this->_name));
		//设置端口号
		if (method_exists($this, '__port'))
		{
			$this->_port = call_user_func(array($this,'__port'));
		}
		//超时时间
		if (method_exists($this, '__timeout'))
		{
			$this->_timeout = call_user_func(array($this,'__timeout'));
		}
		//最大连接数
		if (method_exists($this, '__max_connection'))
		{
			$this->_max_connection = call_user_func(array($this,'__max_connection'));
		}
		
		$this->_master = socket_create_listen( $this->_port ,SOMAXCONN );
		if ($this->_master === false)
		{
			exit();
		}
		$this->inPool($this->_master);
		console::log('Server '.$this->_name.' Startted on '.$this->_port.'!');
		parent::initlize();
	}
	
	/**
	 * 把socket连接加入到连接池
	 * @param resource $socket
	 */
	private function inPool($socket)
	{
		self::$_sockets[] = $socket;
	}
	
	public function run($runControl = NULL)
	{
		$read = self::$_sockets;
		$write = NULL;
		$except = NULL;
		socket_select($read, $write, $except, $this->_timeout);
		foreach ($read as $socket)
		{
			if ($socket == $this->_master)
			{
				$client = socket_accept($this->_master);//接收新的链接
				if ($client === false)
				{
					console::log('connect failed',console::TEXT_COLOR_RED);
					$this->call('error', socket_last_error($this->_master),socket_strerror(socket_last_error($this->_master)));
					continue;
				}
				else
				{
					if (count(self::$_sockets)> $this->_max_connection){
						$this->call('error', 0,'超过最大链接数');
						continue;
					}
					$this->inPool($client);
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
						$this->handShanke($socket);
					}
					else
					{
						if (is_callable($runControl))
						{
							$buffer = $this->decode($buffer);
							$request = json_decode($buffer,true);
							if (!empty($request) && is_array($request))
							{
								$GLOBALS['WEBSOCKET'] = $request;
								$GLOBALS['SOCKET_CLIENT'] = $socket;//当前客户端
								
								$router = application::load('router');
								$router->appendParameter($request);
								$router->parse();
								$control = $router->getControlName();
								$action = $router->getActionName();
								call_user_func($runControl,$control,$action,function($response,$exit,$callback){
									if ($response!==NULL)
									{
										if (is_callable($callback))
										{
											call_user_func($callback,$response);
										}
										else
										{
											webSocket::write($response,socketControl::getSocket());
										}
									}
								});
							}
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
	public static function write($message,$socket = NULL)
	{
		if (empty($socket))
		{
			$num = 0;
			foreach (self::$_sockets as $socket)
			{
				if ($socket!=$GLOBALS['SOCKET_CLIENT'])
				{
					$result = socket_write($socket, $message,strlen($message));
					if ($result === false)
					{
						console::log('['.date('Y-m-d H:i:s').'] Socket Write Error: ['.$message.'] ['.socket_last_error($socket).'] '.socket_strerror(socket_last_error($socket)),console::TEXT_COLOR_RED);
						continue;
					}
					else if ($result == strlen($message))
					{
						$num++;
					}
					else
					{
						console::log('没发送完毕呢还',console::TEXT_COLOR_YELLOW);
						//socket没有全部发送出去 只发送了一部分
					}
				}
			}
			return $num;
		}
		else
		{
			$bytes = socket_write($socket,$message, strlen($message));
			return $bytes;
		}
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
	public static function encode($string)
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
		if ($this->isWebSocket())
		{
			$acceptKey = $this->getAccpetKey();
			$this->_handshake[(int)$socket] = true;
			$message = 
			"HTTP/1.1 101 Switching Protocols\r\n" .
			"Upgrade: websocket\r\n" .
			"Connection: Upgrade\r\n" .
			"Sec-WebSocket-Accept: " . $acceptKey . "\r\n" .
			"\r\n";
			self::write($message,$socket);
			$this->call('open',$socket);
		}
	}
	
	/**
	 * 判断当前请求是否是websocket链接
	 * @return boolean
	 */
	final private function isWebSocket()
	{
		return $this->getKey('Connection') == 'Upgrade' && $this->getKey('Upgrade') == 'websocket';
	}
	
	/**
	 * 获取当websocket第一次链接的时候发送的http请求中的请求参数
	 * @param string $name 请求的Key
	 * @return string|boolean 成功返回字符串，失败返回false
	 */
	final private function getKey($name)
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
	final private function getAccpetKey()
	{
		$key = $this->getKey('Sec-WebSocket-Key');
		if ($key !== false)
		{
			$mask = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11";
			return base64_encode(sha1($key . $mask, true));
		}
		return '';
	}
	
	/**
	 * @param unknown $type 事件名称
	 * @param unknown $args 事件参数
	 */
	public function call($type,$args)
	{
		$args = func_get_args();
		$type = array_shift($args);
		if(is_callable(array($this,'on'.$type)))
		{
			return call_user_func_array(array($this,'on'.$type),$args);
		}
		return false;
	}
	
	function onopen($socket)
	{
		console::log('Socket Connected '.$socket,console::TEXT_COLOR_BLUE);
	}
	
	function onerror($socket,$errno,$error)
	{
		console::log('Socket Error '.$socket,console::TEXT_COLOR_BLUE);
	}
	
	function ondisconnect($socket)
	{
		console::log('Socket DisConnected '.$socket,console::TEXT_COLOR_BLUE);
	}
}