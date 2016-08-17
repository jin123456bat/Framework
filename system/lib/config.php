<?php
namespace lib;
use core\base;

class config extends base implements \ArrayAccess
{
	private $_data;
	
	function __get($name)
	{
		if (isset($this->_data[$name]))
			return $this->_data[$name];
		if (isset($this->$name))
			return $this->$name;
		$init = $this->initlize();
		if (isset($init[$name]))
			return $init[$name];
		return NULL;
	}
	
	function __set($name,$value)
	{
		$this->_data[$name] = $value;
	}
	
	function offsetExists($offset)
	{
		$init = $this->initlize();
		return isset($this->_data[$offset]) || isset($this->$name) || isset($init[$name]);
	}
	
	function offsetGet($offset)
	{
		if (isset($this->_data[$offset]))
			return $this->_data[$offset];
		if (isset($this->$offset))
			return $this->$offset;
		$init = $this->initlize();
		if (isset($init[$offset]))
			return $init[$offset];
		return NULL;
	}
	
	function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
		unset($this->$offset);
		unset($this->initlize()[$offset]);
	}
	
	function offsetSet($offset, $value)
	{
		$this->_data[$offset] = $value;
	}
}