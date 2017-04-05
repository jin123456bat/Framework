<?php
namespace framework\core;

class router extends component
{

	private $_control_name;

	private $_action_name;

	public static $_data = array();

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
		
		if ($config['mode'] == 'normal')
		{
			$this->_control_name = isset(self::$_data['c']) ? trim(self::$_data['c']) : $config['default']['control'];
			$this->_action_name = isset(self::$_data['a']) ? trim(self::$_data['a']) : $config['default']['action'];
			
			if (! $config['case_sensitive'])
			{
				$this->_control_name = $this->_control_name;
				$this->_action_name = $this->_action_name;
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
		self::$_data = array_merge(self::$_data, $array);
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
