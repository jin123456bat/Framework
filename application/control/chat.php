<?php
namespace application\control;

use framework\core\socketControl;

class chat extends socketControl
{

	function message()
	{
		return strrev($this->getParam('data'));
	}
}
?>