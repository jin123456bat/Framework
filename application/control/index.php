<?php
namespace application\control;
use framework\core\control;
use framework\core\response;
class index extends control
{
	function index()
	{	
		var_dump($this->model('feedback')->order('sn','asc')->order('ns','desc')->limit(1,2)->distinct()->forUpdate()->where(array(
			'sn=?',
			'ns=?',
		),array(1,2),'or')->select());
		return new response("123");
	}
}