<?php
namespace framework\core\router\parser;
use framework\core\router\parser;

class cliParser extends parser
{
	private $_argv;
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::setQueryString()
	 */
	public function setQueryString($queryString)
	{
		// TODO Auto-generated method stub
		$this->_argv = $queryString;
		
		$this->_data = array();
		foreach ($this->_argv as $index => $value)
		{
			if (substr($value, 0, 1) == '-')
			{
				if (isset($this->_argv[$index + 1]))
				{
					if (isset($this->_data[substr($value, 1)]))
					{
						if (is_array($this->_data[substr($value, 1)]))
						{
							$this->_data[substr($value, 1)][] = $this->_argv[$index + 1];
						}
						else if (is_string($this->_data[substr($value, 1)]))
						{
							$this->_data[substr($value, 1)] = array(
								$this->_data[substr($value, 1)],
								$this->_argv[$index + 1]
							);
						}
					}
					else
					{
						$this->_data[substr($value, 1)] = $this->_argv[$index + 1];
					}
					unset($this->_argv[$index + 1]);
				}
				else
				{
					$this->_data[substr($value, 1)] = true;
				}
				unset($this->_argv[$index]);
			}
		}
		return $this->_data;
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControlName()
	 */
	public function getControlName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data['c']) && !empty($this->_data['c']))
		{
			return $this->_data['c'];
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