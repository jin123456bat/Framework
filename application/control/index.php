<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\request;
use framework\core\debugger;
use framework\data\collection;
use application\extend\model;

class index extends BaseControl
{

	function index()
	{
		$c = new collection(array(
			1,
			2,
			3
		));
		if ($c->isExist(1) === false)
		{
			var_dump('isExist test 1=> Error');
		}
		
		if ($c->isExist(2) === false)
		{
			var_dump('isExist 2=> Error');
		}
		
		if ($c->isExist(3) === false)
		{
			var_dump('isExist 3=> Error');
		}
		
		if ($c->isExist(4) !== false)
		{
			var_dump('isExist 4 => Error');
		}
		
		var_dump($c);
		foreach ($c as $v)
		{
			var_dump($v);
		}
		var_dump($c);
		
		// $c->append(4);
		
		/*
		 * if ($c->isExist(4) === false)
		 * {
		 * var_dump('append 4 => error');
		 * }
		 */
		
		foreach ($c as $v)
		{
			var_dump($v);
		}
	}

	function test()
	{
		$ordoac = model::getConnection('ordoac');
		var_dump($ordoac->showVariables('character_set_database'));
	}
}
