<?php
namespace framework\core;

use framework;

class server extends component
{
	/**
	 * run control function
	 * @var callable
	 */
	public $_run_control = array();
	
	/**
	 * 监听端口号
	 *
	 * @var integer
	 */
	private $_port = 2000;
	
	/**
	 * 链接超时时间
	 *
	 * @var integer
	 */
	private $_timeout = 60;
	
	/**
	 * 最大链接数量
	 *
	 * @var integer
	 */
	private $_max_connection = 10;
	
	/**
	 * 当前链接socket
	 *
	 * @var unknown
	 */
	private $_master = NULL;
	
	private $_config = array();
	
	/**
	 * socket
	 * @var array
	 */
	static public $_sockets = array();
	
	/**
	 * @var connection[]
	 */
	static public $_connection = array();
	
	function __construct()
	{
		$this->_config = self::getConfig(base::$APP_CONF);
		$this->_port = isset($this->_config['port']) && !empty($this->_config['port'])?$this->_config['port']:$this->_port;
		$this->_timeout= isset($this->_config['timeout']) && !empty($this->_config['timeout'])?$this->_config['timeout']:$this->_timeout;
		$this->_max_connection= isset($this->_config['max_connection']) && !empty($this->_config['max_connection'])?$this->_config['max_connection']:$this->_max_connection;
	}
	
	function start()
	{
		ini_set('max_execution_time', 0);
		
		//保存cmd的server变量到env里面
		$_ENV = $_SERVER;
		
		$this->_master = socket_create_listen($this->_port, SOMAXCONN);
		if ($this->_master === false)
		{
			console::log('socket创建失败');
			exit(1);
		}
		self::$_sockets[(int)$this->_master] = $this->_master;
		
		while (true)
		{
			$read = self::$_sockets;
			$write = NULL;
			$except = NULL;
			socket_select($read, $write, $except, $this->_timeout);
			foreach ($read as $socket)
			{
				if ($socket == $this->_master)
				{
					$client = socket_accept($this->_master);
					if ($client === false)
					{
						continue;
					}
					else
					{
						if (count(self::$_sockets) > $this->_max_connection)
						{
							//$this->call('error', 0, '超过最大链接数');
							continue;
						}
						self::$_sockets[(int)$client] = $client;
					}
				}
				else
				{
					$init_result = true;
					if (!isset(self::$_connection[(int)$socket]))
					{
						self::$_connection[(int)$socket] = new connection($socket);
						if (method_exists(self::$_connection[(int)$socket], 'initlize'))
						{
							$init_result = call_user_func(array(self::$_connection[(int)$socket],'initlize'));
						}
					}
					$connection = self::$_connection[(int)$socket];
					
					if ($init_result !== false)
					{
						$buffer = $connection->read();
						$protocal = $connection->getProotcal();
						if (!empty($buffer))
						{
							//经过socket的消息一般都是二进制的方式传递，需要进行解码之后变为字符串才可读
							$request = call_user_func(array($protocal,'decode'),$buffer);
							
							if (!empty($request))
							{
								$request = call_user_func(array($protocal,'parse'),$request);
								
								$_GET = $request['_GET'];
								$_POST = $request['_POST'];
								$_COOKIE = $request['_COOKIE'];
								$_SERVER = $request['_SERVER'];
								$_FILES = $request['_FILES'];
								$_REQUEST = $request['_REQUEST'];
								$_SESSION = $request['_SESSION'];
								
								$router = application::load('router');
								$router->appendParameter($_GET);
								$router->parse();
								$control = $router->getControlName();
								$action = $router->getActionName();
								
								call_user_func($this->_run_control, $control, $action, function ($response, $exit, $callback) use($connection) {
									if ($response !== NULL)
									{
										$connection->write($response);
									}
								});
							}
						}
						else
						{
							$connection->close();
						}
					}
				}
			}
		}
	}
	
	function daemon()
	{
		$pid = pcntl_fork();
		if ($pid == -1)
		{
			console::log('创建进程失败',TEXT_COLOR_RED);
			exit(1);
		}
		else if ($pid == 0)
		{
			if(posix_setsid() === -1)
			{
				console::log('进程号设置失败');
				exit();
			}
			
			//在子进程中 执行socket监听等等
			$this->start();
			
			//这里来自workman的源代码，需要重新fork一次
			// Fork again avoid SVR4 system regain the control of terminal.
			/* $pid = pcntl_fork();
			if (-1 === $pid)
			{
				console::log('创建进程失败');
				exit();
			}
			else if (0 !== $pid) 
			{
				exit(0);
			} */
			
			
		}
		else
		{
			//父进程退出
			exit(0);
		}
	}
	
	function stop()
	{
		
	}
	
	function status()
	{
		
	}
	
	function reload()
	{
		
	}
	
	function restart()
	{
		
	}
}