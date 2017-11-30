<?php
namespace framework\core\router\parser;
use framework\core\router\parser;

/**
 * 普通url解析器
 * @author jin
 *
 */
class common extends parser
{
	private $_queryString;
	
	private $_data;
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::__construct()
	 */
	public function __construct($queryString)
	{
		// TODO Auto-generated method stub
		if ($queryString[0] == '?')
		{
			$queryString = substr($queryString, 1);
		}
		
		$this->_queryString = $queryString;
		
		parse_str($this->_queryString,$this->_data);
		
		$this->_data = array_map(function($data){
			return trim($data);
		},$this->_data);
	}
	
	function initlize()
	{
		parent::initlize();
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControllName()
	 */
	public function getControllName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data['c']) && !empty($this->_data['c']))
		{
			return trim($this->_data['c']);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getActionName()
	 */
	public function getActionName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data['a']) && !empty($this->_data['a']))
		{
			return $this->_data['a'];
		}
	}

}