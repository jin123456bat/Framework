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
		
		
		$view = new view('index/index.php');
		$view->assign('name1', '[3,4]');
		$view->assign('name2', '李四');
		$view->assign('name3', 'ABC');
		$view->assign('age', function($name,$age = 18){
			if (strlen($name)==0)
			{
				return $age;
			}
			return strlen($name);
		});
		return $view;
		//echo compress::css('./test.css');
		//file_put_contents('./test.min.css', compress::css('./test.css'));
		
		//echo compress::js('./test.js');
		//file_put_contents('./test.min.js', compress::js('./test.js'));
	}

	function page()
	{
		return new view('test/page.html');
	}
}
