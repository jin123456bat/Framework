<?php
namespace framework\core\router\parser;

use framework\core\router\parser;

/**
 * 正则表达式的url解析
 * @author jin
 *
 */
class preg extends parser 
{
	private $_config;
	
	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::setQueryString()
	 */
	public function setQueryString($queryString)
	{
		// TODO Auto-generated method stub
		$this->_config = self::getConfig('router');
		if (isset($this->_config['preg']) && !empty($this->_config['preg']))
		{
			foreach ($this->_config['preg'] as $key => $value)
			{
				$key = str_replace(array(
					'/'
				), array(
					'\/'
				), $key);
				
				$key = preg_replace_callback('/{(?<name>[a-zA-Z_]\w*)}/', function ($matches) {
					return '(?<' . $matches['name'] . '>[^\/]+)';
				}, $key);
					
				if (preg_match('/' . $key . '/i', $queryString, $matches))
				{
					foreach ($matches as $a => $v)
					{
						if (! is_numeric($a))
						{
							$this->_data[$a] = $v;
						}
					}
					
					$this->_data = array_merge($this->_data,$value);
					
					break;
				}
			}
		}
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\router\iparser::getControlName()
	 */
	public function getControlName()
	{
		// TODO Auto-generated method stub
		if (isset($this->_data['c']) && isset($this->_data['a']))
		{
			return $this->_data['c'];
		}
		
		if (isset($this->_data[0]) && isset($this->_data[1]))
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
		if (isset($this->_data['c']) && isset($this->_data['a']))
		{
			return $this->_data['a'];
		}
		
		if (isset($this->_data[0]) && isset($this->_data[1]))
		{
			return $this->_data[1];
		}
	}
}