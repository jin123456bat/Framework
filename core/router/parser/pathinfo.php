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
			$t = explode('?', $queryString,2);
			$pathinfo = $t[0];
			if (!empty($pathinfo))
			{
				$temp = array_filter(explode('/', $pathinfo));
				$temp = array_values(array_map(function($data){
					return trim($data);
				}, $temp));
				
				$this->_data['c'] = isset($temp[0])?$temp[0]:NULL;
				$this->_data['a'] = isset($temp[1])?$temp[1]:NULL;
				
				for ($i=2;$i<count($temp);$i+=2)
				{
					$this->_data[$temp[$i]] = isset($temp[$i+1])?$temp[$i+1]:NULL;
				}
				
			}
			if (isset($t[1]) && !empty($t[1]))
			{
				$query = $t[1];
				parse_str($query,$data);
				$this->_data = array_merge($this->_data,$data);
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
		if (isset($this->_data['c']) && !empty($this->_data['c']) && preg_match('/[a-zA-Z_]\w*/', $this->_data['c']))
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
		if (isset($this->_data['a']) && !empty($this->_data['a']) && preg_match('/[a-zA-Z_]\w*/', $this->_data['a']))
		{
			return $this->_data['a'];
		}
	}
}