<?php
namespace system\core;
use framework\core\control;
use framework\core\response;

class actionFilter
{
	/**
	 * @var control
	 */
	private $_control;
	
	/**
	 * @var string
	 */
	private $_action;
	
	/**
	 * @var response
	 */
	private $_message;
	
	function load(control $control,$action)
	{
		$this->_control = $control;
		$this->_action = $action;
	}
	
	/**
	 * action是否允许访问
	 */
	function allow()
	{
		if (!method_exists($this->_control, $this->_action))
		{
			
		}
	}
	
	/**
	 * 假如禁止访问的话获取禁止访问的消息
	 */
	function getMessage()
	{
		
	}
}