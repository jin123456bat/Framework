<?php
namespace framework\core;

class application extends component
{

	private $_session = null;

	private $_argc = 0;

	private $_argv = array();

	function __construct($name, $path)
	{
		base::$APP_NAME = $name;
		base::$APP_PATH = $path;
		
		// spl_autoload_register([$this,'autoload']);
		
		parent::__construct();
	}

	function initlize($argc = 0, $argv = array())
	{
		$this->_argc = $argc;
		$this->_argv = $argv;
		
		// 载入系统默认配置
		$this->setConfig('framework');
		// 载入app的配置
		$this->setConfig(base::$APP_NAME);
		// 载入环境变量
		$this->env();
		// 导入app配置中的文件类
		$this->import('app');
		// set_error_handler
		$app = $this->getConfig('app');
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
					else if (is_file(ROOT . '/' . ltrim($import, '/')))
					{
						include ROOT . '/' . ltrim($import, '/');
					}
				}
			}
			else
			{
				if (is_file($config['import']))
				{
					include $config['import'];
				}
				else if (is_file(ROOT . '/' . ltrim($config['import'], '/')))
				{
					include ROOT . '/' . ltrim($config['import'], '/');
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
	 * 对命令行参数经行分析
	 * 
	 * @param unknown $argc        	
	 * @param unknown $argv        	
	 */
	public function parseArgment($argc, $argv)
	{
		array_shift($argv);
		$argc --;
		
		$get = array();
		$post = array();
		
		foreach ($argv as $index => $value)
		{
			if (substr($value, 0, 2) == '--')
			{
				if (isset($argv[$index + 1]))
				{
					if (isset($post[substr($value, 2)]))
					{
						if (is_array($post[substr($value, 2)]))
						{
							$post[substr($value, 2)][] = $argv[$index + 1];
						}
						else if (is_string($post[substr($value, 2)]))
						{
							$post[substr($value, 2)] = array(
								$post[substr($value, 2)],
								$argv[$index + 1]
							);
						}
					}
					else
					{
						$post[substr($value, 2)] = $argv[$index + 1];
					}
					unset($argv[$index + 1]);
				}
				else
				{
					$post[substr($value, 2)] = true;
				}
				unset($argv[$index]);
			}
			else if (substr($value, 0, 1) == '-')
			{
				if (isset($argv[$index + 1]))
				{
					if (isset($get[substr($value, 1)]))
					{
						if (is_array($get[substr($value, 1)]))
						{
							$get[substr($value, 1)][] = $argv[$index + 1];
						}
						else if (is_string($get[substr($value, 1)]))
						{
							$get[substr($value, 1)] = array(
								$get[substr($value, 1)],
								$argv[$index + 1]
							);
						}
					}
					else
					{
						$get[substr($value, 1)] = $argv[$index + 1];
					}
					unset($argv[$index + 1]);
				}
				else
				{
					$get[substr($value, 2)] = true;
				}
				unset($argv[$index]);
			}
		}
		
		return array(
			'GET' => $get,
			'POST' => $post
		);
	}

	/**
	 * 运行application
	 */
	function run()
	{
		$argment = $this->parseArgment($this->_argc, $this->_argv);
		$_GET = array_merge($_GET, $argment['GET']);
		$_POST = array_merge($_POST, $argment['POST']);
		$_REQUEST = array_merge($_REQUEST, $argment['GET'], $argment['POST']);
		
		$router = $this->load('router');
		$router->parse();
		$control = $router->getControlName();
		$action = $router->getActionName();
		
		$controller = self::control(base::$APP_NAME, $control);
		
		if (method_exists($this, 'onRequestStart'))
		{
			$response = call_user_func(array(
				$this,
				'onRequestStart'
			), $controller, $action);
			$this->doResponse($response);
		}
		
		$filter = $this->load('actionFilter');
		$filter->load($controller, $action);
		if (! $filter->allow())
		{
			$this->doResponse($filter->getMessage());
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
				$this->doResponse($response);
			}
			
			$response = call_user_func(array(
				$controller,
				$action
			));
			$this->doResponse($response);
		}
	}

	/**
	 * 如何输出response对象
	 * 
	 * @param unknown $response        	
	 */
	protected function doResponse($response)
	{
		if (method_exists($this, 'onRequestEnd'))
		{
			call_user_func(array(
				$this,
				'onRequestEnd'
			), $response);
		}
		if ($response !== null)
		{
			if (is_string($response))
			{
				echo $response;
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
				echo $response->getBody();
			}
			else
			{
				echo json_encode($response);
			}
			exit(0);
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

	/**
	 * 载入系统类
	 * 
	 * @param unknown $classname        	
	 * @return unknown
	 */
	public static function load($classname)
	{
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
		}
		else
		{
			array_pop($classnames);
			$namespaces = array(
				implode('\\', $classnames)
			);
		}
		
		static $instance;
		
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
					$instance[$class] = new $class();
					if (method_exists($instance[$class], 'initlize'))
					{
						$instance[$class]->initlize();
					}
					return $instance[$class];
				}
			}
		}
	}
}
