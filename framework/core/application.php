<?php
namespace framework\core;

class application extends component
{
	function __construct($name,$path)
	{
		base::$APP_NAME = $name;
		base::$APP_PATH = $path;
		
		//spl_autoload_register([$this,'autoload']);
		
		parent::__construct();
	}
	
	function initlize()
	{
		//载入系统默认配置
		$this->setConfig('framework');
		//载入app的配置
		$this->setConfig(base::$APP_NAME);
		//载入环境变量
		$this->env();
		
		$this->import('app');
		
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
				}
			}
			else
			{
				if (is_file($config['import']))
				{
					include $config['import'];
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
		if (is_array($env) && !empty($env))
		{
			foreach ($env as $name => $variable)
			{
				if (is_array($variable) && !empty($variable))
				{
					foreach ($variable as $prefix => $value)
					{
						ini_set($name.'.'.$prefix, $value);
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
	 * 运行application
	 */
	function run()
	{
		$router = $this->load('router');
		$control = $router->getControlName();
		$action = $router->getActionName();
		
		$controller = self::control(base::$APP_NAME,$control);
		
		$filter = $this->load('actionFilter');
		$filter->load($controller,$action);
		if (!$filter->allow())
		{
			$this->doResponse($filter->getMessage());
		}
		else
		{
			//control的初始化返回内容
			if (method_exists($controller, 'initlize') && is_callable(array($controller,'initlize')))
			{
				$response = call_user_func(array($controller,'initlize'));
				$this->doResponse($response);
			}
			
			$response = call_user_func(array($controller,$action));
			$this->doResponse($response);
		}
	}
	
	/**
	 * 如何输出response对象
	 * @param unknown $response
	 */
	protected function doResponse($response)
	{
		xhprof_stop();
		if ($response !== NULL)
		{
			if (is_string($response))
			{
				echo $response;
				exit();
			}
			else if ($response instanceof response)
			{
				//设置status_code
				if (function_exists('http_response_code'))
				{
					http_response_code($response->getHttpStatus());
				}
				else
				{
					header('OK',true,$response->getHttpStatus());
				}
				$response->getHeader()->sendAll();
				echo $response->getBody();
				exit();
			}
		}
	}
	
	/**
	 * 实例化控制器
	 */
	public static function control($app,$name)
	{
		$namespace = $app.'\\control\\'.$name;
		if (class_exists($namespace))
		{
			return new $namespace();
		}
		return NULL;
	}
	
	
	/**
	 * 载入系统类
	 * @param unknown $classname
	 * @return unknown
	 */
	public static function load($classname)
	{
		$classnames = explode('\\', $classname);
		if (count($classnames) == 1)
		{
			$namespaces = array(
				base::$APP_NAME.'\\extend',
				'framework\\core',
				'framework\\core\\database',
				'framework\\core\\response',
				'framework\\lib',
				'framework\\vendor',
			);
		}
		else
		{
			array_pop($classnames);
			$namespaces = array(
				implode('\\',$classnames),
			);
		}
	
		static $instance;
	
		foreach ($namespaces as $namespace)
		{
			$class = $namespace.'\\'.$classname;
			if (isset($instance[$class]))
			{
				return $instance[$class];
			}
			else
			{
				if(class_exists($class,true))
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