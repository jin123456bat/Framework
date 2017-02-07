<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\request;
use framework\core\debugger;
class index extends BaseControl
{
	function index()
	{
		$file = request::file('file');
		var_dump($file);
	}
	
}