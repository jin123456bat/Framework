<?php
namespace framework\core;

use framework;
use framework\core\response\json;

class application extends component
{
	/**
	 * 将要执行的控制器的名称
	 * @var unknown
	 */
	private $_control;
	
	/**
	 * 将要执行的action的名称
	 * @var unknown
	 */
	private $_action;
	
	function __construct($name, $path, $configName = '')
	{
		base::$APP_NAME = $name;
		base::$APP_PATH = $path;
		
		// 应用程序的配置文件名称
		// 默认用应用程序的前3个字符
		if (empty($configName))
		{
			$configName = substr($name, 0, 3);
			base::$APP_CONF = $configName;
		}
		parent::__construct();
	}
	
	function initlize()
	{
		// 载入系统默认配置
		$this->setConfig(true);
		// 载入用户自定义配置
		$this->setConfig(false);
		// 载入环境变量
		self::setEnvironment($this->getConfig('environment'));
		// 导入app配置中的文件类
		$this->import(base::$APP_CONF);
		
		//设置当前访问模式
		if (isset($_SERVER['argc']) && isset($_SERVER['argv']) && !empty($_SERVER['argc']) && !empty($_SERVER['argv']))
		{
			array_shift($_SERVER['argv']);
			$_SERVER['argc'] --;
			
			if (isset($_SERVER['argc']) && $_SERVER['argc'] == 1)
			{
				request::$_php_sapi_name = 'server';
			}
		}
		
		// set_error_handler
		$app = $this->getConfig(base::$APP_CONF);
		if (isset($app['errorHandler']) && ! empty($app['errorHandler']))
		{
			if (isset($app['errorHandler']['class']))
			{
				$types = isset($app['errorHandler']['types']) ? $app['errorHandler']['types'] : '';
				$result = explode('::', $app['errorHandler']['class']);
				$class = array_shift($result);
				$method = array_shift($result);
				$method = empty($method) ? 'run' : $method;
				if (! empty($class) && class_exists($class))
				{
					$class = new $class();
					set_error_handler(array(
						$class,
						$method
					), $types);
				}
			}
		}
		
		//rewrite
		if (isset($app['rewrite']) && !empty($app['rewrite']))
		{
			base::$_rewrite = $app['rewrite'];
		}
		
		// 设置默认编码
		$charset = 'UTF-8';
		if (isset($app['charset']) && ! empty($app['charset']))
		{
			$charset = strtoupper(trim($app['charset']));
		}
		else
		{
			$this->replaceConfig('charset', $charset);
		}
		mb_internal_encoding($charset);
		mb_http_output($charset);
		ini_set('default_charset', $charset);
		
		parent::initlize();
	}
	
	private function import($name)
	{
		$config = $this->getConfig($name);
		if (isset($config['import']))
		{
			if (is_array($config['import']))
			{
				foreach ($config['import'] as $import)
				{
					if (is_file($import))
					{
						include_once $import;
					}
					else if (is_file(APP_ROOT . '/' . ltrim($import, '/')))
					{
						include_once APP_ROOT . '/' . ltrim($import, '/');
					}
				}
			}
			else
			{
				if (is_file($config['import']))
				{
					include $config['import'];
				}
				else if (is_file(APP_ROOT . '/' . ltrim($config['import'], '/')))
				{
					include APP_ROOT . '/' . ltrim($config['import'], '/');
				}
			}
		}
	}
	
	/**
	 * 设置app的环境变量
	 */
	public static function setEnvironment($env)
	{
		if (is_array($env) && ! empty($env))
		{
			foreach ($env as $name => $variable)
			{
				// 对于date.timezone特殊处理
				if (is_array($variable) && ! empty($variable))
				{
					foreach ($variable as $prefix => $value)
					{
						ini_set($name . '.' . $prefix, $value);
					}
				}
				else
				{
					ini_set($name, $variable);
				}
			}
		}
	}
	
	/**
	 * 这里还是有问题 还是需要share memory来实现
	 * 判断程序在cli下是否在运行
	 * @return boolean
	 */
	private function isRunning($control, $action)
	{
		$shell = 'ps -ef | grep "' . APP_ROOT . '/index.php -c ' . $control . ' -a ' . $action . '" | grep -v grep | grep -v "sh -c"';
		exec($shell, $response);
		return count($response) >= 2;
	}
	
	/**
	 * 执行控制器中的方法
	 * @param string $control
	 *        控制器名称
	 * @param string $action
	 *        控制器方法
	 * @param string $doResponse
	 *        是否输出方法的返回值， 这个是回调函数 假如没有回调函数，则返回方法的返回值
	 * @example function($response,$exit = false,$callback = NULL){} 参考application::doResponse方法
	 * @return NULL|response
	 */
	function runControl($control, $action, $doResponse = NULL)
	{
		$controller = self::control(base::$APP_NAME, $control);
		if ($controller instanceof control)
		{
			$this->_control = $control;
			$this->_action = $action;
			
			$callback = array(
				$controller,
				'__output'
			);
			
			if (method_exists($this, 'onRequestStart'))
			{
				$response = call_user_func(array(
					$this,
					'onRequestStart'
				), $controller, $action);
				if ($response !== NULL)
				{
					if (is_callable($doResponse))
					{
						call_user_func($doResponse, $response, true, $callback);
					}
					else
					{
						return $response;
					}
				}
			}
			
			$filter = self::load(actionFilter::class);
			$filter->load($controller, $action);
			if (! $filter->allow())
			{
				$response = $filter->getMessage();
				if ($response !== NULL)
				{
					if (is_callable($doResponse))
					{
						call_user_func($doResponse, $response, true, $callback);
					}
					else
					{
						return $response;
					}
				}
			}
			else
			{
				//cli模式下防止重复调用
				if ($filter->singleThread() && $this->isRunning($control, $action))
				{
					exit(0);
				}
				// control的初始化返回内容
				if (method_exists($controller, 'initlize') && is_callable(array(
					$controller,
					'initlize'
				)))
				{
					$response = call_user_func(array(
						$controller,
						'initlize'
					));
					if ($response !== NULL)
					{
						if (is_callable($doResponse))
						{
							call_user_func($doResponse, $response, true, $callback);
						}
						else
						{
							return $response;
						}
					}
				}
				$response = call_user_func(array(
					$controller,
					$action
				));
				if ($response !== NULL)
				{
					if (is_callable($doResponse))
					{
						call_user_func($doResponse, $response, true, $callback);
					}
					else
					{
						return $response;
					}
				}
			}
		}
		else if ($controller instanceof response)
		{
			if (is_callable($doResponse))
			{
				call_user_func($doResponse, $controller, true, function ($msg) {
					echo $msg;
				});
			}
			else
			{
				return $controller;
			}
		}
	}
	
	/**
	 * 运行application
	 */
	function run()
	{
		switch (request::php_sapi_name())
		{
			case 'cli':
				$router = self::load(router::class);
				$query_string = $_SERVER['argv'];
				$router->parse($query_string);
				$control = $router->getControlName();
				$action = $router->getActionName();
				$this->runControl($control, $action, array(
					$this,
					'doResponse'
				));
				break;
			case 'web':
				$router = self::load(router::class);
				$query_string = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
				$router->parse($query_string);
				$control = $router->getControlName();
				$action = $router->getActionName();
				$this->runControl($control, $action, array(
					$this,
					'doResponse'
				));
				break;
			case 'server':
				$server = self::load(server::class);
				$command = trim(strtolower($_SERVER['argv'][0]));
				
				if (method_exists($server, $command))
				{
					$server->_run_control = array(
						$this,
						'runControl'
					);
					call_user_func(array(
						$server,
						$command
					));
				}
				else
				{
					console::log('未知命令:' . $command);
				}
				break;
		}
	}
	
	/**
	 * 输出response
	 * @param mixed $response
	 *        输出的对象
	 * @param bool $exit
	 *        输出完毕后是否exit()
	 * @param callback $callback
	 *        对输出对像使用什么样的方法输出
	 * @example $this->doResponse('123',true,function($msg){echo $msg;})
	 */
	protected function doResponse($response, $exit = true, $callback = NULL)
	{
		if (method_exists($this, 'onRequestEnd'))
		{
			$newResponse = call_user_func(array(
				$this,
				'onRequestEnd'
			),$this->_control,$this->_action,$response);
			if ($newResponse !== NULL)
			{
				$response = $newResponse;
			}
		}
		
		if ($response !== null)
		{
			if (is_scalar($response))
			{
				call_user_func($callback, $response);
			}
			else if ($response instanceof response)
			{
				if (request::php_sapi_name() == 'web')
				{
					$response->initlize();
					
					// 设置status_code
					if (function_exists('http_response_code'))
					{
						http_response_code($response->getHttpStatus());
					}
					else
					{
						header('OK', true, $response->getHttpStatus());
					}
					$response->getHeader()->sendAll();
				}
				call_user_func($callback, $response->getBody());
			}
			else
			{
				call_user_func($callback, json::json_encode_ex($response));
			}
			if ($exit)
			{
				exit(0);
			}
		}
	}
	
	/**
	 * 实例化控制器
	 */
	public static function control($app, $name)
	{
		$namespace = '\\' . $app . '\\control\\' . $name;
		if (class_exists($namespace))
		{
			return new $namespace();
		}
		//响应内容直接是一个response
		$response = application::load(response::class,$name);
		if ($response)
		{
			return $response;
		}
		return null;
	}
	
	public function onRequestStart($control,$action)
	{
	}
	
	public function onRequestEnd($control,$action,$response = null)
	{
	}
	
	/**
	 * 检测这个类是否有被重写，假如重写了，加载重写后的类
	 * 没有重写，检测这个类是否可以被实例化，假如可以 通过第三个参数实例化
	 * 假如不可以，加载一个必须继承这个类的类，类名由第二个参数指定
	 * @param string $instanceof ，没有重写加载当前类，继承的类， 要加载的类必须要继承$instanceof指定的类
	 * @param string $classname 类名 默认为空 要加载的类的类名 假如为空 则加载找到的第一个类
	 * @return object
	 */
	public static function load($instanceof,$class_name = '',$args = array())
	{
		if (isset(base::$_rewrite[$instanceof]))
		{
			$class = base::$_rewrite[$instanceof];
		}
		else
		{
			$class = $instanceof;
		}
		
		$reflectionClass = new \ReflectionClass($class);
		if (!$reflectionClass->isTrait() && !$reflectionClass->isAbstract() && !$reflectionClass->isInterface())
		{
			$object = $reflectionClass->newInstanceArgs($args);
			if ($reflectionClass->hasMethod('initlize'))
			{
				$object->initlize();
			}
			return $object;
		}
		else if (!empty($class_name))
		{
			//假如传入的class_name是一个带命名空间的类，则直接使用这个类
			if (strpos($class_name,'\\')!==false)
			{
				if (class_exists($class_name,true))
				{
					$class = new $class_name();
					if (method_exists($class, 'initlize'))
					{
						$class->initlize();
					}
					return $class;
				}
			}
			else
			{
				
				
				//从app目录下查找对应的类
				$files = glob_recursive(rtrim(APP_ROOT,'/').'/'.$class_name.'.php',GLOB_BRACE);
				
				foreach ($files as $file)
				{
					$namespace = str_replace('/', '\\', APP_NAME.str_replace(APP_ROOT, '', pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME)));
					include_once $file;
					if (class_exists($namespace))
					{
						$reflectionClass = new \ReflectionClass($namespace);
						$object = $reflectionClass->newInstanceWithoutConstructor();
						if ($object instanceof $class)
						{
							$object = $reflectionClass->newInstanceArgs($args);
							if ($reflectionClass->hasMethod('initlize'))
							{
								$object->initlize();
							}
							return $object;
						}
					}
				}
				
				//从系统目录中查找对应的类
				$files = glob_recursive(rtrim(SYSTEM_ROOT,'/').'/'.$class_name.'.php',GLOB_BRACE);
				foreach ($files as $file)
				{
					$namespace = str_replace('/', '\\', 'framework'.str_replace(SYSTEM_ROOT, '', pathinfo($file,PATHINFO_DIRNAME).'/'.pathinfo($file,PATHINFO_FILENAME)));
					include_once $file;
					if (class_exists($namespace))
					{
						$reflectionClass = new \ReflectionClass($namespace);
						$object = $reflectionClass->newInstanceWithoutConstructor();
						if ($object instanceof $class)
						{
							$object = $reflectionClass->newInstanceArgs($args);
							if ($reflectionClass->hasMethod('initlize'))
							{
								$object->initlize();
							}
							return $object;
						}
					}
				}
			}
		}
	}
}
