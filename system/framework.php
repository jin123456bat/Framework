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
		set_error_handler([$this,'error']);
		//异常处理
		set_exception_handler([$this,'exception']);
	}
	
	/**
	 * 创建应用程序
	 */
	function createApplication($name,$root)
	{
		$this->_root = $root;
		$isCli = in_array(php_sapi_name(), $this->_cli_spai_name);
		
		$appConfig = $this->object('config.app');
		return self::object('core.application',[
			
		]);
	}
	
	
	/**
	 * 实例化核心class
	 * @param unknown $classname
	 * @param unknown $args
	 */
	static public function object($classname,$args = [])
	{
		$classname = str_replace('.', '\\', $classname);
		$class = new ReflectionClass($classname);
		$classObj = $class->newInstanceArgs($args);
		if (method_exists($classObj, 'initlize') && is_callable([$classObj,'initlize']))
		{
			$classObj->initlize();
		}
		return $classObj;
	}
	
	protected function exception($exception)
	{
		var_dump($exception);
	}
	
	protected function error($a,$b,$c)
	{
		var_dump($a);
		var_dump($b);
		var_dump($c);
	}
	
	protected function autoload($classname)
	{
		$pos = stripos($classname,'\\');
		var_dump($classname);
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
						include_once $path;
					}
				}
			}
			else
			{
				$path = $root_path.'/'.str_replace('\\', '/', $classname).'.php';
				if (file_exists($path))
				{
					include_once $path;
				}
			}
		}
	}
}