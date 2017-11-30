<?php
namespace framework\core\router\parser;
use framework\core\router\parser;

/**
 * pathinfo方式的url解析
 * @author jin
 *
 */
class pathinfo extends parser
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::setQueryString()
	 */
	public function setQueryString($queryString)
	{
		if (!isset($queryString[0]) || $queryString[0]!='?')
		{
			// TODO Auto-generated method stub
			$this->_data = array_filter(explode('/', $queryString));
			$this->_data = array_values(array_map(function($data){
				return trim($data);
			}, $this->_data));
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControlName()
	 */
	public function getControlName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data[0]) && !empty($this->_data[0]) && preg_match('/[a-zA-Z_]\w*/', $this->_data[0]))
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
		if (isset($this->_data[1]) && !empty($this->_data[1]) && preg_match('/[a-zA-Z_]\w*/', $this->_data[1]))
		{
			return $this->_data[1];
		}
	}
}