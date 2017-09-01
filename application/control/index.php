<?php
namespace application\control;

use framework\core\view;
use application\entity\test;
use framework\core\cache;
use framework\vendor\captcha;
use framework\core\webControl;
use framework\core\response\json;

class index extends webControl
{

	function index()
	{
		
		/**
		 * 快速排序算法测试
		 * @var array $array
		 */
		$array = quickSort([4,3,5,2,1]);
		if ($array != array(1,2,3,4,5))
		{
			echo "快速排序算法错误";
		}
		
		
		$cache_type = array(
			'mysql',
			'file',
			'redis',
			'memcache',
			'apc',
		);
		
		
		foreach ($cache_type as $type)
		{
			cache::flush();
			
			
			if (cache::store($type)->get('jin') != NULL)
			{
				echo "缓存 $type get测试失败，应该返回NULL";
			}
			
			if(!is_bool(cache::store($type)->set('jin', 'name')))
			{
				echo "缓存 $type set返回值不是true或者false";
			}
			
			/*
			 * 缓存测试
			 * db缓存测试
			 */
			cache::store($type)->set('jin','name');
			if (cache::store($type)->get('jin') != 'name')
			{
				echo "缓存 $type set添加测试或get测试 失败";
			}
			
			
			cache::store($type)->set('jin',array(1,2,3));
			if (cache::store($type)->get('jin') !== array(1,2,3))
			{
				echo "缓存 $type set添加测试或get测试数组 失败";
			}
			
			$class = new \stdClass();
			$class->name = 'jin';
			cache::store($type)->set('jin',$class);
			if (cache::store($type)->get('jin')->name !== 'jin')
			{
				echo "缓存 $type set添加测试或get测试对象 失败";
			}
			
			/*
			 * 缓存测试
			 * set覆盖测试
			 */
			cache::store($type)->set('jin','name1');
			if (cache::store($type)->get('jin') != 'name1')
			{
				echo "缓存 $type set覆盖测试 失败";
			}
			
			if(cache::store($type)->add('jin', 'name2') === true)
			{
				echo "缓存 $type add返回值不是boolean或者add失败";
			}
			if (cache::store($type)->get('jin') != 'name1')
			{
				echo "缓存 $type add覆盖测试 失败";
			}
			
			
			
			cache::store($type)->set('jin', 1);
			if (!is_bool(cache::store($type)->decrease('jin')))
			{
				echo "缓存 $type decrease 返回值不是boolean";
			}
			if (cache::store($type)->get('jin') !== 0)
			{
				echo "缓存 $type decrease测试 失败1";
			}
			
			
			cache::store($type)->set('jin', 1);
			if(!is_bool(cache::store($type)->decrease('jin',2)))
			{
				echo "缓存 $type decrease不是boolean";
			}
			if (cache::store($type)->get('jin') !== -1)
			{
				echo "缓存 $type decrease测试 失败2";
			}
			
			cache::store($type)->set('jin', '1');
			if(!is_bool(cache::store($type)->increase('jin')))
			{
				echo "缓存$type increase返回值不是boolean";
			}
			if (cache::store($type)->get('jin') !== 2)
			{
				echo "缓存 $type increase测试 失败";
			}
			
			
			cache::store($type)->set('jin', '1');
			if(!is_bool(cache::store($type)->increase('jin',2)))
			{
				echo "缓存$type increase返回值不是boolean";
			}
			if (cache::store($type)->get('jin') !== 3)
			{
				echo "缓存 $type increase测试 失败";
			}
			
			cache::store($type)->set('jin1', 1);
			cache::store($type)->set('jin2', 2);
			if(!is_bool(cache::store($type)->flush()))
			{
				echo "缓存 $type flush返回值不是boolean";
			}
			if (cache::store($type)->get('jin1') !== NULL || cache::store($type)->get('jin2') !== NULL)
			{
				echo "缓存 $type flush测试 失败";
			}
			
			cache::store($type)->set('jin1', 1);
			if(!is_bool(cache::store($type)->remove('jin1')))
			{
				echo "缓存$type remove返回值不是boolean";
			}
			if (cache::store($type)->get('jin1') !== NULL)
			{
				echo "缓存 $type remove测试 失败";
			}
		}
		
		//var_dump($this->model('cache')->select());
		
		
		/* $test = new test(array(
		'username' => 'jin123',
		'password' => '111',
		'age' => 18,
		'money' => '-1',
		'telephone' => 15868481019,
		'ip' => '255.255.255.4/24',
		'email' => '326550324@qq.com',
		'time' => '2017-05-06 12:12:12',
		'sex' => '男',
		'user' => array(
					'name'=>'jin',
				)
		));
		if (!$test->validate())
		{
			//var_dump($test->getError());
		} */
		
		
		
		//return new json(1,2,3);
		return new captcha();
		
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
