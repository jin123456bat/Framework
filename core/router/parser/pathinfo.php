<?php
namespace framework\core\router\parser;
use framework\core\router\parser;

class pathinfo extends parser
{
	private $_data;
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::__construct()
	 */
	public function __construct($queryString)
	{
		if ($queryString[0]!='?')
		{
			// TODO Auto-generated method stub
			$this->_data = array_filter(explode('/', $queryString));
			$this->_data = array_map(function($data){
				return trim($data);
			}, $this->_data);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControllName()
	 */
	public function getControllName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data[0]) && !empty($this->_data[0]))
		{
			return $this->_data[0];
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getActionName()
	 */
	public function getActionName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data[1]) && !empty($this->_data[1]))
		{
			return $this->_data[1];
		}
	}

	
}