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
		/* $pattern = array('/(12)[a-zA-Z0-9]{1,}(12)/');
		$str = '12adf1ba2ds1af3ds12  456asdfasdf1234';
		preg_replace_callback($pattern,function($match){
			var_dump($match);
		}, $str);
		exit(); */
		$view = new view('index/index.php');
		$view->assign('name1', '(1)');
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
