<?php
namespace framework\core;

class router extends component
{

	private $_control_name;

	private $_action_name;

	private $_data = array();

	function __construct()
	{
		parent::__construct();
	}

	function initlize()
	{
		//self::$_data = array_merge(self::$_data, $_GET);
		parent::initlize();
	}

	public function parse()
	{
		$config = $this->getConfig('router');
		
		$query_string = $_SERVER['QUERY_STRING'];
		if (empty($query_string))
		{
			//假如没有？或者？后面为空  获取index.php后面的内容，index.php不能省略  可以通过rewrite规则来实现
			$query_string = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
		}
		
		//路由绑定判断
		if (!empty($query_string))
		{
			if (isset($config['bind'][$query_string]) && !empty($config['bind'][$query_string]))
			{
				$bind = $config['bind'][$query_string];
				
				if (isset($bind['c']))
				{
					$this->_control_name = $bind['c'];
				}
				if (isset($bind['a']))
				{
					$this->_action_name = $bind['a'];
				}
				
				if (isset($bind[0]))
				{
					$this->_control_name = $bind[0];
				}
				if (isset($bind[1]))
				{
					$this->_action_name = $bind[1];
				}
			}
		}
		
		//pathinfo模式的支持
		if (!empty($query_string) && empty($this->_action_name) && empty($this->_control_name))
		{
			$params = array_filter(explode('/', $query_string));
			$this->_control_name = isset($params[0])?$params[0]:NULL;
			$this->_action_name = isset($params[1])?$params[1]:NULL;
			for($i = 2;$i<count($params);$i+=2)
			{
				$_GET[$params[$i]] = isset($params[$i+1])?$params[$i+1]:NULL;
			}
		}
		
		//路由的正则表达式的支持
		if (!empty($query_string) && empty($this->_action_name) && empty($this->_control_name))
		{
			if (isset($config['bind']) && is_array($config['bind']))
			{
				$bind = $config['bind'];
				foreach ($bind as $key => $value)
				{
					//\/about\/(?<id>[^\/]+)
					$key = str_replace(array(
						'/'
					), array(
						'\/'
					), $key);
					
					$key = preg_replace_callback('/{(?<name>[a-zA-Z_]\w*)}/', function($matches){
						return '(?<'.$matches['name'].'>[^\/]+)';
					}, $key);
					
					if (preg_match('/'.$key.'/', $query_string,$matches))
					{
						foreach($matches as $a=>$v)
						{
							if (!is_numeric($a))
							{
								$_GET[$a] = $v;
							}
						}
						
						
						if (isset($value['c']))
						{
							$this->_control_name = $value['c'];
						}
						if (isset($value['a']))
						{
							$this->_action_name = $value['a'];
						}
						
						if (isset($value[0]))
						{
							$this->_control_name = $value[0];
						}
						if (isset($value[1]))
						{
							$this->_action_name = $value[1];
						}
						
						break;
					}
				}
			}
		}
		
		if (empty($this->_control_name) && empty($this->_action_name))
		{
			$this->_control_name = isset($this->_data['c']) ? trim($this->_data['c']) : $config['default']['control'];
			$this->_action_name = isset($this->_data['a']) ? trim($this->_data['a']) : $config['default']['action'];
		}
	}

	/**
	 * 对于要分析的数据追加
	 *
	 * @param array $array        	
	 */
	public function appendParameter(array $array)
	{
		$this->_data = array_merge($this->_data, $array);
	}

	public function getControlName()
	{
		return $this->_control_name;
	}

	public function getActionName()
	{
		return $this->_action_name;
	}
}
