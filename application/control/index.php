<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\request;
use framework\core\debugger;
use framework\data\collection;
use application\extend\model;
use framework\core\view;
use framework\vendor\compress;
use framework\core\session;

class index extends BaseControl
{

	function index()
	{
		$file = request::file('file','video');
		/* $view = new view('index/index.php');
		$view->assign('array', array(
			'name' => array(
				'firstname' => 'jin',
			),
			'age' => 108,
		));
		$view->assign('name', array(
			'a',
			'b',
			'c'
		));
		$view->assign('fruit', array(
			'apple',
			'banana',
			'oringe',
		));
		$view->assign('name1', '4');
		$view->assign('name2', '李四');
		$view->assign('name3', 'ABC');
		$view->assign('age', function($name,$age = 18){
			if (strlen($name)==0)
			{
				return $age;
			}
			return strlen($name);
		});
		return $view; */
	}

	function page()
	{
		return new view('test/page.html');
	}
}
