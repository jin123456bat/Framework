<?php
namespace application\control;
use framework\core\control;
class index extends control
{
	function index()
	{
		//ini_set('max_execution_time', 0);
		
		$this->createOperationStatData();
	}
	
	/**
	 * 创建operation_stat表的数据
	 */
	function createOperationStatData()
	{
		$start_time = '2016-08-01 00:00:00';
		$end_time = '2016-10-01 00:00:00';
	
		$sn = array(
			'CAS0530000002',
			'CAS0530000003',
			'CAS0530000004',
			'CAS0530000005',
			'CAS0530000006',
			'CAS0530000007',
			'CAS0530000008',
			'CAS0530000009',
		);
	
		foreach ($sn as $sns)
		{
			for($i=$start_time;strtotime($i)<strtotime($end_time);$i = date("Y-m-d H:i:s",strtotime($i) + 5*60))
			{
				$random_time = $i;
					
				$class = rand(0,2);
				if ($class==2)
				{
					$live = rand(0,1) * 128;
					if ($live)
					{
						$category = $live + rand(0,28);
					}
					else
					{
						$category = rand(0,22);
					}
				}
				else if ($class==1)
				{
					$category = rand(0,2);
				}
				else if ($class == 0)
				{
					$category = rand(0,4);
				}
					
				if($this->model('operation_stat')->insert(array(
					'create_time' => $random_time,
					'sn' => $sns,
					'class' => $class,
					'category' => $category,
					'mode' => rand(0,3),
					'cache_size' => rand(0,100000),
					'proxy_cache_size' => rand(0,100000),
					'cache_hit_size' => rand(0,100000),
					'service_size' => rand(0,100000),
					'service_time' => rand(0,100000),
					'task_size' => rand(0,100000),
					'play_cnt' => rand(10,30),
					'hit_cnt' => rand(10,3000),
					'service_cnt' => rand(100,200),
					'make_time' => $random_time
				)))
				{
					echo "ok<br>";
				}
			}
		}
	}
}