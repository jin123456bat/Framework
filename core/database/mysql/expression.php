<?php
namespace framework\core\database\mysql;

class expression
{
	private $_content;
	
	function __construct($string)
	{
		$this->_content = $string;
	}
	
	function __toString()
	{
		return $this->_content;
	}
}