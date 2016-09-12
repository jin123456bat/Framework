<?php
namespace framework\core;

use framework\lib\data;

class entity extends data
{
	public $_data;
	
	function __construct($data = NULL)
	{
		$this->_data = $data;
	}
}