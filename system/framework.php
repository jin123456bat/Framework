<?php
use system\core\application;

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
	];
	
	function __construct()
	{
		spl_autoload_register([$this,'autoload']);
	}
	
	/**
	 * 创建应用程序
	 */
	function createApplication($root)
	{
		$this->_root = $root;
		$isCli = in_array(php_sapi_name(), $this->_cli_spai_name);
		return self::object('application',[$isCli]);
	}
	
	
	/**
	 * 实例化核心class
	 * @param unknown $classname
	 * @param unknown $args
	 */
	static public function object($classname,$args)
	{
		$class = new ReflectionClass($classname);
		//var_dump($class);
	}
	
	protected function autoload($classname)
	{
		
		$pos = stripos('\\', $classname);
		if ($pos === false)
		{
			foreach ($this->_include_path as $file_path)
			{
				$path = SYSTEM_ROOT.'\\'.$file_path.'\\'.$classname.'.php';
				if (file_exists($path))
				{
					include_once $path;
				}
			}
		}
		else
		{
			$path = SYSTEM_ROOT.str_replace('\\', '/', $classname).'.php';
			if (file_exists($path))
			{
				include_once $path;
			}
		}
	}
}