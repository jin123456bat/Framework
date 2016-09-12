<?php
namespace framework\core;

class router extends component
{
	private $_control_name;
	
	private $_action_name;
	
	function __construct()
	{
		parent::__construct();
	}
	
	function initlize()
	{
		$this->parse();
		parent::initlize();
	}
	
	private function parse()
	{
		$config = $this->getConfig('router');
		
		if ($config['mode'] == 'normal')
		{
			$this->_control_name = isset($_GET['c'])?trim($_GET['c']):$config['default']['control'];
			$this->_action_name = isset($_GET['a'])?trim($_GET['a']):$config['default']['action'];
			
			if (!$config['case_sensitive'])
			{
				$this->_control_name = strtolower($this->_control_name);
				$this->_action_name = strtolower($this->_action_name);
			}
		}
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