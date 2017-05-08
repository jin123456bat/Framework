<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\request;
use framework\core\debugger;
use framework\data\collection;
use application\extend\model;
use framework\core\view;
use framework\vendor\compress;

class index extends BaseControl
{

	function index()
	{
		//return new view('index/index.php');
		
		//echo compress::css('./test.css');
		//file_put_contents('./test.min.css', compress::css('./test.css'));
		
		echo compress::js('./test.js');
		file_put_contents('./test.min.js', compress::js('./test.js'));
	}

	function page()
	{
		return new view('test/page.html');
	}
}
