<?php
namespace application\control;

use framework\core\cliControl;

class cli extends cliControl
{

	function index()
	{
		return ($this->getParam('abc'));
	}
}