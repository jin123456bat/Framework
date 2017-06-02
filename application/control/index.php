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
use application\entity\user;
use framework\vendor\authorize;
use application\extend\application;
use framework\core\cookie;
use framework\vendor\csrf;
use framework\core\response\url;
use framework\core\database\mysql\table;
use framework\core\database\mysql\field;
use framework\core\database\driver\mysql;
use framework\core\cache;

class index extends BaseControl
{

	function index()
	{
		//var_dump(cookie::set('name','555'));
		//var_dump($_COOKIE);
		/* $token = csrf::token();
		var_dump($token);
		var_dump(csrf::verify($token)); */
		
		//$authorize = application::load('authorize');
		//$file = request::file('file','video');
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
		return new url('index','page'); */
		//return $view;
		
		/* 
		 * var_dump(model::isExist('asdfadsf'));
		 */
		
		
		/* $mysql = model::getConnection('test');
		
		$this->model('tree')->drop();
		$table = new table('tree');
		$table->int('id')->primary()->default('1');
		$table->varchar('name', 64)->unique()->default('jin')->comment('名称');
		$table->timestamp('create_time')->prototype(field::PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP);
		$table->int('age')->prototype(field::PROTOTYPE_UNSIGNED_ZEROFILL)->nullable();
		var_dump($mysql->create($table,'test'));
		var_dump($this->model('tree')->select()); */
		
		
		/* $table = new table('authorize');
		$table->int('id')->primary()->default('1');
		$table->varchar('name', 64)->unique()->default('jin')->comment('名称');
		$table->timestamp('create_time')->prototype(field::PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP);
		$table->int('age')->prototype(field::PROTOTYPE_UNSIGNED_ZEROFILL)->nullable(); */
		
		/* $table = new table('authorize');
		$table->varchar('address', 128); */
		
		/* $authorize = new authorize();
		if($authorize->attempt(array('username' => 'jin123456bat','password'=>'jin2164389')))
		{
			echo "验证通过";
		}
		else
		{
			echo "验证失败";
		} */
		
		cache::set('name', array(
			'jin',
			'jin1'
		));
		cache::set('name1', 'jin');
		$name2 = new \stdClass();
		$name2->a = 1;
		$name2->b = 2;
		cache::set('name2', $name2);
		var_dump(cache::get('name'));
		var_dump(cache::get('name1'));	
		var_dump(cache::get('name2'));
		
	}

	function page()
	{
		return new view('test/page.html');
	}
}
