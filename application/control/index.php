<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\request;
class index extends BaseControl
{
	function index()
	{
		$file = request::file('file');
		var_dump($file);
	}
}