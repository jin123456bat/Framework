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
		// self::$_data = array_merge(self::$_data, $_GET);
		parent::initlize();
	}

	public function parse()
	{
		$config = $this->getConfig('router');
		$query_string = substr($_SERVER['PHP_SELF'], strlen($_SERVER['SCRIPT_NAME']));
		
		foreach ($config['parser'] as $parser)
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
