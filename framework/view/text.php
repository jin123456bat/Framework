<?php
namespace framework\view;

class text extends dom
{
	private $_string = '';
	
	function __construct($string)
	{
		$this->_string = $string;
	}
	
	function __toString()
	{
		return $this->_string;
	}
}