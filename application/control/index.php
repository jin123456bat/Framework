<?php
namespace application\control;

use framework\core\view;
use framework\core\webControl;
use framework\data\tree\bstree;
use framework\data\line\stack;
use framework\data\tree\btree;

class index extends webControl
{

	function index()
	{
		/* $tree = new bstree();
		$tree->push(5);
		$tree->push(4);
		$tree->push(3);
		$tree->push(2);
		$tree->push(1); */
		
		$tree = new btree();
		var_dump($tree->inIterator());
	}

	function page()
	{
		$view = new view('index/index.php');
		$view->assign('array', array(
			'name' => array(
				'firstname' => 'jin',
			)
		));
		$view->assign('name1', 'wahaha');
		$view->assign('name3', '???');
		$view->assign('name', array(1,2,3));
		$view->assign('fruit', array('A','B','C'));
		$view->assign('abc', 123123123123);
		return $view;
	}
}
