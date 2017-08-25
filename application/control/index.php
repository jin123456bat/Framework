<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\view;
use application\entity\test;
use framework\core\cache;

class index extends BaseControl
{

	function index()
	{
		/*
		 * 缓存测试
		 */
		/* cache::set('jin','name');
		if (cache::get('jin') == 'name')
		{
			var_dump("缓存测试通过");
		} */
		var_dump($this->model('cache')->select());
		
		/*
		 * $test = new test(array(
		 * 'username' => 'jin123',
		 * 'password' => '111',
		 * 'age' => 18,
		 * 'money' => '-1',
		 * 'telephone' => 15868481019,
		 * 'ip' => '255.255.255.4/24',
		 * 'email' => '326550324@qq.com',
		 * 'time' => '2017-05-06 12:12:12',
		 * 'sex' => '男',
		 * 'user' => array(
		 * 			'name'=>'jin',
		 * 		)
		 * ));
		 * if (!$test->validate())
		 * {
		 * var_dump($test->getError());
		 * }
		 */
		 //验证码
		 //$this->table('a')->checksum();
		 //cache::set('name', 'jin');
		//return new captcha();
		//$this->cache('apc')->set('name','jin');
		//$this->model('session');
		//captcha::validate($code);
		
		//$a->field('id')->int(11)->comment('ID主键');
		//$a->field('name')->varchar(32)->comment('用户名');
		//var_dump($a->index('primary')->add('sd')->remove('sd'));
		//$a->primary()->add('id');
		//$a->field('a')->int(11);
		//$a->field('b');
		//动态创建表有点蛋疼啊  这个是laravel的方式
		//$a->create(function($table){
		//	$table->field('a')->int(11)->create();
		//	$table->field('b')->varchar(32)->create();
		//});
		//var_dump($a->field('name')->comment('name的注释123'));
		//var_dump($a->field('aa')->enum([1,2,3,'']));
		
		// var_dump(cookie::set('name','555'));
		// var_dump($_COOKIE);
		/*
		 * $token = csrf::token();
		 * var_dump($token);
		 * var_dump(csrf::verify($token));
		 */
		
		// $authorize = application::load('authorize');
		// $file = request::file('file','video');
		/*
		 * $view = new view('index/index.php');
		 * $view->assign('array', array(
		 * 'name' => array(
		 * 'firstname' => 'jin',
		 * ),
		 * 'age' => 108,
		 * ));
		 * $view->assign('name', array(
		 * 'a',
		 * 'b',
		 * 'c'
		 * ));
		 * $view->assign('fruit', array(
		 * 'apple',
		 * 'banana',
		 * 'oringe',
		 * ));
		 * $view->assign('name1', '4');
		 * $view->assign('name2', '李四');
		 * $view->assign('name3', 'ABC');
		 * $view->assign('age', function($name,$age = 18){
		 * if (strlen($name)==0)
		 * {
		 * return $age;
		 * }
		 * return strlen($name);
		 * });
		 * return new url('index','page');
		 */
		// return $view;
		
		/*
		 * var_dump(model::isExist('asdfadsf'));
		 */
		
		/*
		 * $mysql = model::getConnection('test');
		 * $this->model('tree')->drop();
		 * $table = new table('tree');
		 * $table->int('id')->primary()->default('1');
		 * $table->varchar('name', 64)->unique()->default('jin')->comment('名称');
		 * $table->timestamp('create_time')->prototype(field::PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP);
		 * $table->int('age')->prototype(field::PROTOTYPE_UNSIGNED_ZEROFILL)->nullable();
		 * var_dump($mysql->create($table,'test'));
		 * var_dump($this->model('tree')->select());
		 */
		
		/*
		 * $table = new table('authorize');
		 * $table->int('id')->primary()->default('1');
		 * $table->varchar('name', 64)->unique()->default('jin')->comment('名称');
		 * $table->timestamp('create_time')->prototype(field::PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP);
		 * $table->int('age')->prototype(field::PROTOTYPE_UNSIGNED_ZEROFILL)->nullable();
		 */
		
		/*
		 * $table = new table('authorize');
		 * $table->varchar('address', 128);
		 */
		
		/*
		 * $authorize = new authorize();
		 * if($authorize->attempt(array('username' => 'jin123456bat','password'=>'jin2164389')))
		 * {
		 * echo "验证通过";
		 * }
		 * else
		 * {
		 * echo "验证失败";
		 * }
		 */
		
		/*
		 * cache::set('name', array(
		 * 'jin',
		 * 'jin1'
		 * ),1);
		 * sleep(3);
		 * var_dump(cache::get('name'));
		 */
		
		/*
		 * cache::store('file')->set('abc', 'sss');//存储到file中
		 * cache::store('mysql')->set('name','1234');//存储到mysql中
		 * cache::set('vbb', 'dd');//存储到默认的type中
		 */
		 
		 
		/*
		 * cache::set('name1', 'jin');
		 * $name2 = new \stdClass();
		 * $name2->a = 1;
		 * $name2->b = 2;
		 * get('name',333));//123 不存在的时候333
		 * var_dump(request::get('name',NULL,'strlen'));//3 使用过滤器
		 * var_dump(request::get('name',NULL,'strlen|explode:",","?"'));//使用多个过滤器
		 * var_dump(request::get('name',NULL,NULL,'a'));//array(123); 使用强制变量转换
		 */
		
		/*
		 * request::file('file');//使用默认配置
		 * request::file('file','video');//使用视频配置
		 */
		
		/*
		 * $a = new sql();
		 * $b = new sql();
		 * $a->setFrom('a')->select();
		 * $b->setFrom('b')->select();
		 * $a->union(true,$b);
		 * $config = $this->getConfig('db');
		 * $m = mysql::getInstance($config['test']);
		 * $result = $m->query($a);
		 * var_dump($result);
		 */
		
		// return new json(array('c'=>'test','a'=>'message','data'=>'参数1','data2'=>'参数2'));
	}

	function page()
	{
		return new view('test/page.html');
	}
}
