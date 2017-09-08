<?php
namespace application\control;

use framework\core\view;
use framework\core\webControl;
use framework\data\tree\bstree;
use framework\data\line\stack;
use framework\data\tree\btree;
use framework\core\cache\driver\redis;

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
		$tree->push('a');
		$tree->push('b');
		$tree->push('d');
		$tree->push();
		$tree->push('e');
		$tree->push();
		$tree->push();
		$tree->push('f');
		$tree->push('g');
		$tree->push();
		$tree->push();
		$tree->push();
		$tree->push('c');
		
		if(implode('',$tree->postIterator()) != 'edgfbca')
		{
			echo "后序遍历失败";
		}
		
		/* if (implode('', $tree->inIterator()) != 'debgfac') 
		{
			echo "中序遍历失败";
		}
		
		if (implode('', $tree->preIterator()) != 'abdefgc')
		{
			echo "中序遍历失败";
		} */
	}
	
	/**
	 * 共享内存作为队列的话 一个小小的测试
	 */
	function sm()
	{
	
		$redis = new redis($config);
		$redis->
		
		$key = 0x4337b700;
		$size = 4096;
		
		$start = time();
		for($i=0;$i<1000000;$i++)
		{
			$shmid = @shmop_open($key, 'c', 0644, $size);
			if($shmid === FALSE){
				exit('shmop_open error!');
			}
			
			$data = '世界，你好！我将写入很多的数据，你能罩得住么？';
			
			$length = shmop_write($shmid, pack('a*',$data), 0);
			if($length === FALSE){
				exit('shmop_write error!');
			}
			
			@shmop_close($shmid);
		}
		echo time() - $start;
		
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
