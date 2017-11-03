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
		//项目的初始化
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
							if (!file_exists($file))
							{
								file_put_contents($file,"<?php\nreturn array(\n\n);\n?>");
							}
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
		//程序安装
		$this->install();
		//
		$this->immigrate();
	}
	
	/**
	 * 移植兼容性，当使用低版本的php的时候，对高版本的部分功能的替代
	 */
	function immigrate()
	{
		//兼容array_column
		if (!function_exists('array_column'))
		{
			function array_column(array $input,$column_key,$index_key = null)
			{
				$temp = array();
				foreach ($input as $value)
				{
					if (isset($value[$column_key]))
					{
						if (empty($index_key) || !isset($value[$index_key]))
						{
							$temp[] = $value[$column_key];
						}
						else
						{
							$temp[$value[$index_key]] = $value[$column_key];
						}
					}
				}
				return $temp;
			}
		}
		
		/**
		 * 触发
		 * @param unknown $a 除数
		 * @param unknown $b 被除数
		 * @param number $default 当被除数为0的时候的默认值 默认为0
		 * @return number
		 */
		function division($a,$b,$default = 0)
		{
			if (empty($b))
				return $default;
			return $a/$b;
		}
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
