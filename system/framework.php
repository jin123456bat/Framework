<?php
class framework
{
	protected $_root;
	
	/**
	 * 程序通过php_spai_name函数判断当前运行模式是cli还是cgi，当其返回值在cli模式中则为cli模式，否则为cgi模式
	 * @var array
	 */
	private $_cli_spai_name = [
		'cli',
		'cli-server',
	];
	
	/**
	 * 当发现不存在的class的时候搜索的目录（直接使用类名）
	 * @var array
	 */
	private $_include_path = [
		'core',
		'vendor',
		'lib',
		'config'
	];
	
	function __construct()
	{
		//自动加载
		spl_autoload_register([$this,'autoload']);
		//错误处理
		//set_error_handler([$this,'error']);
		//异常处理
		//set_exception_handler([$this,'exception']);
	}
	
	/**
	 * 创建应用程序
	 * @param string $name app的名称
	 * @param string $root app的路径
	 */
	function createApplication($name,$root)
	{
		$this->_root = $root;
		$isCli = in_array(php_sapi_name(), $this->_cli_spai_name);
		
		$appConfig = $this->object('config.app');
		
		$application = self::object($name.'.extend.application',$appConfig);
		if ($application === NULL)
		{
			$application = self::object('application',$appConfig);
			var_dump($application);
		}
		return $application;
	}
	
	
	/**
	 * 实例化核心class
	 * @param unknown $classname
	 * @param unknown $args
	 */
	static public function object($classname,$args = [])
	{
		$classname = $this->findClassNamespace($classname);
		
		$class = new ReflectionClass($classname);
		$classObj = $class->newInstance($args);
		if (method_exists($classObj, 'initlize') && is_callable([$classObj,'initlize']))
		{
			$response = $classObj->initlize();
			if ($response !== NULL)
			{
				return $response;
			}
		}
		return $classObj;
	}
	
	protected function exception($exception)
	{
		//异常信息提示
		//var_dump($exception);
	}
	
	protected function error($errno,$error,$file,$line,$trace)
	{
		//错误信息提示
		//echo 'ERROR:'.$error.' On File:'.$file;
	}
	
	/**
	 * 根据类名猜测命名空间
	 */
	private function findClassNamespace($classname)
	{
		$classname = str_replace('.', '\\', $classname);
		$pos = stripos($classname,'\\');
		$root = [
			$this->_root,//first,find class from user defined folder
			SYSTEM_ROOT
		];
		foreach ($root as $root_path)
		{
			if ($pos === false)
			{
				foreach ($this->_include_path as $file_path)
				{
					$path = $root_path.'\\'.$file_path.'\\'.$classname.'.php';
					if (file_exists($path))
					{
						return $file_path.'\\'.$classname;
					}
				}
			}
			else
			{
				$explode = explode('\\', $classname);
				if (current($explode) == 'framework' && count($explode)>1)
				{
					$root = [
						SYSTEM_ROOT,
					];
					array_shift($explode);
					$classname = implode('\\', $explode);
				}
				$path = $root_path.'/'.str_replace('\\', '/', $classname).'.php';
				if (file_exists($path))
				{
					$namespace = ltrim(str_replace(ROOT, '', $root_path),'\\/').'\\'.$classname;
					return $namespace;
				}
			}
		}
	}
	
	protected function autoload($classname)
	{
		$classname = $this->findClassNamespace($classname);
		
		$path = ROOT.'/'.$classname.'.php';
		if (file_exists($path))
		{
			include_once $path;
		}
	}
}