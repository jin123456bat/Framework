<?php
namespace framework\core;

use framework\core\router\parser;

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
		parent::initlize();
	}

	public function parse($query_string)
	{
		$config = $this->getConfig('router');
		
		switch (env::php_sapi_name())
		{
			case 'web':
				$config_parser = $config['web_parser'];
			break;
			case 'cli':
				$config_parser = $config['cli_parser'];
			break;
			case 'server':
				$config_parser = $config['server_parser'];
			break;
		}
		
		foreach ($config_parser as $parser)
		{
			$parser = application::load(parser::class,$parser);
			$parser->setQueryString($query_string);
			$this->_control_name = $parser->getControlName();
			$this->_action_name = $parser->getActionName();
			if (!empty($this->_control_name) || !empty($this->_action_name))
			{
				break;
			}
		}
		
		$this->_control_name = empty($this->_control_name)?$config['default']['control']:$this->_control_name;
		$this->_action_name= empty($this->_action_name)?$config['default']['action']:$this->_action_name;
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
