<?php
namespace framework\core;

use framework;

class service extends component
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
	private $_max_connection = 3;
	
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
	static private $_sockets = array();
	
	/**
	 * @var connection[]
	 */
	static private $_connection = array();
	
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
		$this->_master = socket_create_listen($this->_port, SOMAXCONN);
		if ($this->_master === false)
		{
			console::log('socket创建失败');
			exit(1);
		}
		self::$_sockets[] = $this->_master;
		
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
						self::$_sockets[] = $client;
					}
				}
				else
				{
					$buffer = '';
					do{
						$str = socket_read($socket, 2048);
						$buffer.=$str;
					}while(strlen($str)==2048);
					
					if (empty($buffer))
					{
						//关闭socket
						$index = array_search($socket, self::$_sockets);
						socket_close($socket);
						array_splice(self::$_sockets, $index, 1);
					}
					else
					{
						$class_name= 'framework\\core\\protocal\\driver\\'.$this->_config['protocal'];
						if (class_exists($class_name,true))
						{
							$protocal = new $class_name();
							if (method_exists($protocal, 'initlize'))
							{
								call_user_func(array($protocal,'initlize'));
							}
							
							if (!isset(self::$_connection[(int)$socket]))
							{
								self::$_connection[(int)$socket] = new connection($socket, $protocal);
							}
							$connection = self::$_connection[(int)$socket];
							
							$init_result = true;
							if (method_exists($protocal, 'init'))
							{
								$init_result = call_user_func(array($protocal,'init'),$buffer,self::$_connection[(int)$socket]);
							}
							
							if ($init_result !== false)
							{
								//经过socket的消息一般都是二进制的方式传递，需要进行解码之后变为字符串才可读
								$request = call_user_func(array($protocal,'decode'),$buffer);
								
								//通常一个字符串并不能满足系统的参数需求，这里必须要将字符串转化为数组，作为系统的输入参数
								$request = call_user_func(array($protocal,'parse'),$request);
								
								//设置$_GET参数
								$_GET = $request;
								//设置$_POST参数
								$_POST = $request;
								//设置cookie 尚未实现
								$_COOKIE = array();
								
								$router = application::load('router');
								$router->appendParameter($request);
								$router->parse();
								$control = $router->getControlName();
								$action = $router->getActionName();
								
								call_user_func($this->_run_control, $control, $action, function ($response, $exit, $callback) use($connection) {
									if ($response !== NULL)
									{
										$connection->send($response);
									}
								});
							}
						}
						else
						{
							console::log('无法找到协议:'.$this->_config['protocal']);
							exit(1);
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