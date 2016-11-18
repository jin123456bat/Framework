<?php
namespace application\control;

use application\extend\BaseControl;
class index extends BaseControl
{
	function index()
	{
		var_dump($this->combineSns());
	}
}