<?php
namespace framework\core\router;

use framework\core\base;

abstract class parser extends base implements iparser
{
	protected $_data;
	
	function getData()
	{
		return $this->_data;
	}
}