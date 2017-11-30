<?php
namespace framework\core\router\parser;

use framework\core\router\parser;

/**
 * 静态绑定的url解析
 * @author jin
 *
 */
class bind extends parser
{
	private $_queryString;
	
	private $_config;
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::setQueryString()
	 */
	public function setQueryString($queryString)
	{
		// TODO Auto-generated method stub
		$this->_config = self::getConfig('router');
		$this->_queryString = $queryString;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControlName()
	 */
	public function getControlName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_config['bind'][$this->_queryString]) && !empty($this->_config['bind'][$this->_queryString]))
		{
			return isset($this->_config['bind'][$this->_queryString]['c'])?$this->_config['bind'][$this->_queryString]['c']:$this->_config['bind'][$this->_queryString][0];
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getActionName()
	 */
	public function getActionName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_config['bind'][$this->_queryString]) && !empty($this->_config['bind'][$this->_queryString]))
		{
			return isset($this->_config['bind'][$this->_queryString]['a'])?$this->_config['bind'][$this->_queryString]['a']:$this->_config['bind'][$this->_queryString][1];
		}
	}

	
}