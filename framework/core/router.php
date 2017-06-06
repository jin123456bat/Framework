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
			$query_string = substr($_SERVER['REQUEST_URI'], strlen($_SERVER['SCRIPT_NAME']));
		}
		
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
		
		if (empty($this->_control_name) && empty($this->_action_name))
		{
			if ($config['mode'] == 'normal')
			{
				$this->_control_name = isset($this->_data['c']) ? trim($this->_data['c']) : $config['default']['control'];
				$this->_action_name = isset($this->_data['a']) ? trim($this->_data['a']) : $config['default']['action'];
				
				if (! $config['case_sensitive'])
				{
					$this->_control_name = $this->_control_name;
					$this->_action_name = $this->_action_name;
				}
			}
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
