<?php
namespace framework\core;

use framework;

class application extends component
{

	function __construct($name, $path, $configName = '')
	{
		base::$APP_NAME = $name;
		base::$APP_PATH = $path;
		
		// 配置文件名称
		if (empty($configName))
		{
			$configName = substr($name, 0, 3);
			base::$APP_CONF = $configName;
		}
		
		parent::__construct();
	}

	function initlize()
	{
		// 载入所有配置
		$this->setConfig();
		// 载入环境变量
		$this->env();
		// 导入app配置中的文件类
		$this->import(base::$APP_CONF);
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
						include $import;
					}
					else if (is_file(APP_ROOT . '/' . ltrim($import, '/')))
					{
						include APP_ROOT . '/' . ltrim($import, '/');
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
	private function env()
	{
		$env = self::getConfig('environment');
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
	 * 执行控制器中的方法
	 * 
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
			$callback = array(
				$controller,
				'__output'
			);
			
			if ($controller instanceof socketControl && request::php_sapi_name() != 'socket')
			{
				if (method_exists($controller, '__runningMode'))
				{
					$response = call_user_func(array(
						$controller,
						'__runningMode'
					), request::php_sapi_name());
					if (! $response !== NULL)
					{
						$this->doResponse($response, true);
					}
				}
			}
			
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
			
			$filter = $this->load('actionFilter');
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
	}

	/**
	 * 运行application
	 */
	function run()
	{
		$router = $this->load('router');
		if (request::php_sapi_name() == 'web')
		{
			$router->appendParameter($_GET);
		}
		elseif (request::php_sapi_name() == 'cli')
		{
			array_shift($_SERVER['argv']);
			$_SERVER['argc'] --;
			$argment = cliControl::parseArgment($_SERVER['argc'], $_SERVER['argv']);
			if (isset($argment['c']) && isset($argment['a']))
			{
				$router->appendParameter($argment);
			}
			else
			{
				request::$_php_sapi_name = 'socket';
				$socekt = isset($argment['websocket']) && ! empty($argment['websocket']) ? $argment['websocket'] : 'webSocket';
				$websocket = self::load($socekt, 'framework\core\webSocket');
				if (empty($websocket))
				{
					exit('don\'t exist websocket: ' . $socekt . "\r\n");
				}
				else
				{
					while (true)
					{
						$websocket->run(array(
							$this,
							'runControl'
						));
					}
				}
			}
		}
		$router->parse();
		$control = $router->getControlName();
		$action = $router->getActionName();
		$this->runControl($control, $action, array(
			$this,
			'doResponse'
		));
	}

	/**
	 * 输出response
	 * 
	 * @param mixed $response
	 *        输出的对象
	 * @param bool $exit
	 *        输出完毕后是否exit()
	 * @param callback $callback
	 *        对输出对像使用什么样的方法输出，默认为echo的方式
	 * @example $this->doResponse('123',true,function($msg){echo $msg;})
	 */
	protected function doResponse($response, $exit = true, $callback = NULL)
	{
		if (method_exists($this, 'onRequestEnd'))
		{
			$newResponse = call_user_func(array(
				$this,
				'onRequestEnd'
			), $response);
			if ($newResponse !== NULL)
			{
				$response = $newResponse;
			}
		}
		if ($response !== null)
		{
			if (is_scalar($response))
			{
				if (is_callable($callback))
				{
					call_user_func($callback, $response);
				}
				else
				{
					echo $response;
				}
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
				if (is_callable($callback))
				{
					call_user_func($callback, $response);
				}
				else
				{
					echo $response->getBody();
				}
			}
			else
			{
				if (is_callable($callback))
				{
					call_user_func($callback, $response);
				}
				else
				{
					echo json_encode($response);
				}
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
		return null;
	}

	public function onRequestStart()
	{
	}

	public function onRequestEnd()
	{
	}

	/**
	 * 载入系统类
	 * 
	 * @param string $classname
	 *        类名
	 * @param string $instance
	 *        继承的类
	 * @return object
	 * @example application::load('control')
	 *          application::load('framework\core\model')
	 */
	public static function load($classname, $instanceof = '')
	{
		static $instance;
		
		$classnames = explode('\\', $classname);
		if (count($classnames) == 1)
		{
			$namespaces = array(
				base::$APP_NAME . '\\extend',
				'framework\\core',
				'framework\\core\\database',
				'framework\\core\\response',
				'framework\\lib',
				'framework\\vendor'
			);
			
			foreach ($namespaces as $namespace)
			{
				$class = $namespace . '\\' . $classname;
				
				if (isset($instance[$class]))
				{
					return $instance[$class];
				}
				else
				{
					if (class_exists($class, true))
					{
						$temp = new $class();
						if (! empty($instanceof) && class_exists($instanceof, true))
						{
							if (! ($temp instanceof $instanceof))
							{
								return null;
							}
						}
						$instance[$class] = $temp;
						if (method_exists($instance[$class], 'initlize'))
						{
							$instance[$class]->initlize();
						}
						return $instance[$class];
					}
				}
			}
		}
		else
		{
			if (class_exists($classname, true))
			{
				$instance[$classname] = new $classname();
				if (method_exists($instance[$classname], 'initlize'))
				{
					$instance[$classname]->initlize();
				}
				return $instance[$classname];
			}
		}
	}
}
