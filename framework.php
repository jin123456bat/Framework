<?php

/**
 *
 * @author fx
 */
class framework
{

	/**
	 * 存储了所有app进程
	 * 
	 * @var unknown
	 */
	private $_application;

	/**
	 * 命令行参数个数
	 * 
	 * @var unknown
	 */
	private $_argc;

	/**
	 * 命令行参数
	 * 
	 * @var unknown
	 */
	private $_argv;

	function __construct()
	{
		spl_autoload_register(array(
			$this,
			'autoload'
		), true);
		//判断是否第一次打开  是第一次的话判断目录是否存在  不存在的话创建目录
		$this->initlize();
	}
	
	/**
	 * 安装过程
	 */
	function install()
	{
		if (DEBUG)
		{
			$dir = array(
				'control',
				'config' => array(
					substr(APP_NAME, 0,3).'.php',
				),
				'entity',
				'extend',
				'log',
				'template',
			);
			foreach ($dir as $k => $d)
			{
				if (is_string($d))
				{
					$file = APP_ROOT.'/'.$d;
					if (!file_exists($file))
					{
						//创建控制器目录
						mkdir($file,0777,true);
					}
				}
				else if (is_array($d))
				{
					$file = APP_ROOT.'/'.$k;
					if (!file_exists($file))
					{
						mkdir($file,0777,true);
					}
					foreach ($d as $f)
					{
						if (strpos($f, '.'))
						{
							$file = APP_ROOT.'/'.$k.'/'.$f;
							file_put_contents($file,"<?php\nreturn array(\n\n);\n?>");
						}
					}
				}
			}
		}
	}
	
	/**
	 * 程序初始化
	 */
	function initlize()
	{
		$this->install();
	}

	/**
	 * 创建应用程序
	 * 
	 * @param unknown $name        
	 * @param unknown $path        
	 */
	function createApplication($name, $path, $configName = '')
	{
		$appkey = md5($name . $path);
		
		$paths = explode('/', $path);
		$namespace = end($paths) . '\\extend\\' . $name;
		$user_deinfed_component_path = rtrim($path, '/') . '/extend/' . $name . '.php';
		if (file_exists($user_deinfed_component_path))
		{
			$this->_application[$appkey] = new $namespace($name, $path);
		}
		else
		{
			// 载入系统的application
			$namespace = 'framework\\core\\application';
			$this->_application[$appkey] = new $namespace($name, $path, $configName);
		}
		if (method_exists($this->_application[$appkey], 'initlize'))
		{
			$this->_application[$appkey]->initlize();
		}
		return $this->_application[$appkey];
	}

	/**
	 * 自动载入
	 */
	protected function autoload($classname)
	{
		$classname = ltrim($classname);
		$class = explode('\\', $classname);
		if (array_shift($class) == 'framework')
		{
			$path = rtrim(SYSTEM_ROOT, '/') . '/' . implode('/', $class) . '.php';
		}
		else
		{
			$path = rtrim(APP_ROOT,'/').'/'.implode('/', $class).'.php';
		}
		if (file_exists($path))
		{
			if ((include $path) !== 1)
			{
				echo "include failed";
			}
		}
	}
}
