<?php
namespace framework\core;

/**
 * @author fx
 *
 */
abstract class socket extends component
{
	private $_port = 2000;
	
	private $_timeout = 60;
	
	private $_max_connection = 3;
	
	private $_master = NULL;
	
	/**
	 * 消息长度  最长不能超过这个数量否则会出现数据丢失
	 * @var integer
	 */
	private $_message_length = 2048;
	
	static private $_sockets = array();
	
	function initlize()
	{
		if (request::php_sapi_name() != 'cli')
		{
			exit('this program must be running in cli mode');
		}
		
		$this->_master = socket_create_listen( $this->_port );
		if (!$this->_master)
		{
			call_user_func(array($this,'error'),'error',socket_last_error($this->_master),socket_strerror(socket_last_error($this->_master)));
		}
		
		self::$_sockets[] = $this->_master;
		call_user_func(array($this,'open'),$this->_master);
		
		console::log('Server Start on '.$this->_port.'!');
		
		parent::initlize();
	}
	
	public function isClose()
	{
		return false;
	}
	
	public function run()
	{
		console::log('['.date('Y-m-d H:i:s').'][notice] socket running');
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
					call_user_func(array($this,'error'),'error',socket_last_error($this->_master),socket_strerror(socket_last_error($this->_master)));
					continue;
				}
				else
				{
					if (count(self::$_sockets)> $this->_max_connection){
						continue;
					}
					self::$_sockets[] = $client;
					call_user_func(array($this,'open'),$client);
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
					call_user_func(array($this,'receive'),$buffer,$socket);
				}
			}
		}
	}
	
	/**
	 * 从socket中读取数据
	 * @param unknown $socket
	 * @return string
	 */
	function read($socket)
	{
		return socket_read($socket, $this->_message_length);
	}
	
	/**
	 * 当socket底层出现错误的时候会触发这个函数
	 * @param unknown $level
	 * @param unknown $errno
	 * @param unknown $error
	 */
	public function error($level,$errno,$error)
	{
		console::log('['.date('Y-m-d H:i:s').']['.$level.'] socket error: ('.$errno.') '.$error,console::TEXT_COLOR_RED);
	}
	
	/**
	 * 每当有新的链接进来的时候都会触发这个函数
	 * @param unknown $client
	 */
	public function open($client)
	{
		console::log('socket connect success '.$client);
	}
	
	/**
	 * 关闭socket链接
	 * @param unknown $socket
	 */
	public function close($socket)
	{
		$index = array_search( $socket, self::$_sockets );
		socket_close( $socket );
		call_user_func(array($this,'disconnect'),$socket);
		array_splice(self::$_sockets, $index, 1 );
	}
	
	/**
	 * 当socket断开链接的时候触发这个函数
	 */
	public function disconnect($socket)
	{
		console::log('socket disconnect: '.$socket);
	}
	
	/**
	 * 向socket写入信息
	 * @param unknown $message
	 * @param unknown $socket = NULL 当为空的时候为向所有客户端发送
	 */
	protected function write($message,$socket = NULL)
	{
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
	
	/**
	 * 当socket接受到信息的时候触发这个函数
	 * @param unknown $message
	 */
	public function receive($message,$socket)
	{
		console::log('receive message: '.$message);
	}
}