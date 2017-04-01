<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\view;
use framework\core\request;
use framework\core\model;

class dataCreator extends BaseControl
{

	private $_sn = array(
		'CAS0530000150',
		'CAS0530000151',
		'CAS0530000152',
		'CAS0530000153',
		'CAS0530000154',
		'CAS0530000155',
		'CAS0530000156',
		'CAS0530000157',
		'CAS0530000158',
		'CAS0530000159'
	);

	/**
	 * 创建首页CDS曲线图
	 */
	function main_overview_cds()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':
				$this->_duration_second = 30 * 60;
			break;
			case 'hourly':
				$this->_duration_second = 2 * 60 * 60;
			break;
			case 'daily':
				$this->_duration_second = 24 * 60 * 60;
			break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		$i = 0;
		$this->model('feedbackHistory')->startCompress();
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			$j = rand(1, count($this->_sn));
			foreach ($this->_sn as $sn)
			{
				$i ++;
				if ($this->model('feedbackHistory')->insert(array(
					'ctime' => $t_time,
					'sn' => $sn
				)))
				{
					$num ++;
				}
				if ($mode == 0 && $i == $j)
				{
					$i = 0;
					break;
				}
			}
		}
		$this->model('feedbackHistory')->commitCompress();
		return new json(json::OK, null, $num);
	}

	/**
	 * 创建首页在线人数曲线图
	 *
	 * @return unknown|\framework\core\response\json
	 */
	function main_overview_online()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':
				$this->_duration_second = 30 * 60;
			break;
			case 'hourly':
				$this->_duration_second = 2 * 60 * 60;
			break;
			case 'daily':
				$this->_duration_second = 24 * 60 * 60;
			break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		
		$this->model('feedbackHistory')->startCompress();
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			foreach ($this->_sn as $sn)
			{
				if ($this->model('feedbackHistory')->insert(array(
					'ctime' => $t_time,
					'sn' => $sn,
					'online' => $mode == 0 ? rand(100, 1000) : 1000
				)))
				{
					$num ++;
				}
			}
		}
		$this->model('feedbackHistory')->commitCompress();
		
		return new json(json::OK, null, $num);
	}

	/**
	 * 创建首页在服务流速
	 *
	 * @return unknown|\framework\core\response\json
	 */
	function main_overview_service_max()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':
				$this->_duration_second = 5 * 60;
			break;
			case 'hourly':
				$this->_duration_second = 2 * 60 * 60;
			break;
			case 'daily':
				$this->_duration_second = 24 * 60 * 60;
			break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		
		$this->model('traffic_stat')->startCompress();
		$this->model('cdn_traffic_stat')->startCompress();
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			foreach ($this->_sn as $sn)
			{
				$num += $this->model('traffic_stat')->insert(array(
					'create_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'sn' => $sn
				));
				$num += $this->model('cdn_traffic_stat')->insert(array(
					'make_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'sn' => $sn
				));
			}
		}
		
		$this->model('traffic_stat')->commitCompress();
		$this->model('cdn_traffic_stat')->commitCompress();
		
		return new json(json::OK, null, $num);
	}

	/**
	 * 添加首页分CP流速
	 *
	 * @return unknown|\framework\core\response\json
	 */
	function main_overview_cp_service()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':
				$this->_duration_second = 30 * 60;
			break;
			case 'hourly':
				$this->_duration_second = 2 * 60 * 60;
			break;
			case 'daily':
				$this->_duration_second = 24 * 60 * 60;
			break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		
		$categoryConfig = $this->getConfig('category');
		
		$this->model('traffic_stat')->startCompress();
		$this->model('cdn_traffic_stat')->startCompress();
		$this->model('operation_stat')->startCompress();
		
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			foreach ($this->_sn as $sn)
			{
				foreach ($categoryConfig as $classname => $categorys)
				{
					switch ($classname)
					{
						case 'http':
							$class = 0;
						break;
						case 'mobile':
							$class = 1;
						break;
						default:
							$class = 2;
						break;
					}
					foreach ($categorys as $category => $categoryname)
					{
						if ($class == 2 && $category >= 23)
						{
							$category += 128;
						}
						$num += $this->model('operation_stat')->insert(array(
							'class' => $class,
							'category' => $category,
							'sn' => $sn,
							'make_time' => $t_time,
							'service_size' => $mode == 1 ? 500 : rand(0, 100000)
						));
					}
				}
				
				$num += $this->model('traffic_stat')->insert(array(
					'create_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'sn' => $sn
				));
				$num += $this->model('cdn_traffic_stat')->insert(array(
					'make_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'sn' => $sn
				));
			}
		}
		
		$this->model('traffic_stat')->commitCompress();
		$this->model('cdn_traffic_stat')->commitCompress();
		$this->model('operation_stat')->commitCompress();
		
		return new json(json::OK, null, $num);
	}

	/**
	 * 内容交付的回源流速和服务流速
	 */
	function content_cache_service()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':
				$this->_duration_second = 5 * 60;
			break;
			case 'hourly':
				$this->_duration_second = 2 * 60 * 60;
			break;
			case 'daily':
				$this->_duration_second = 24 * 60 * 60;
			break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		
		$categoryConfig = $this->getConfig('category');
		
		$this->model('traffic_stat')->startCompress();
		$this->model('cdn_traffic_stat')->startCompress();
		$this->model('operation_stat')->startCompress();
		
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			foreach ($this->_sn as $sn)
			{
				foreach ($categoryConfig as $classname => $categorys)
				{
					switch ($classname)
					{
						case 'http':
							$class = 0;
						break;
						case 'mobile':
							$class = 1;
						break;
						default:
							$class = 2;
						break;
					}
					foreach ($categorys as $category => $categoryname)
					{
						if ($class == 2 && $category >= 23)
						{
							$category += 128;
						}
						$num += $this->model('operation_stat')->insert(array(
							'class' => $class,
							'category' => $category,
							'sn' => $sn,
							'make_time' => $t_time,
							'service_size' => $mode == 1 ? 1000 : rand(0, 100000),
							'cache_size' => $mode == 1 ? 500 : rand(0, 100000)
						));
					}
				}
				
				$num += $this->model('traffic_stat')->insert(array(
					'create_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'cache' => $mode == 1 ? 1000 : rand(0, 100000),
					'sn' => $sn
				));
				$num += $this->model('cdn_traffic_stat')->insert(array(
					'make_time' => $t_time,
					'service' => $mode == 1 ? 2000 : rand(0, 100000),
					'cache' => $mode == 1 ? 1000 : rand(0, 100000),
					'sn' => $sn
				));
			}
		}
		
		$this->model('traffic_stat')->commitCompress();
		$this->model('cdn_traffic_stat')->commitCompress();
		$this->model('operation_stat')->commitCompress();
		
		return new json(json::OK, null, $num);
	}

	/**
	 * 生成节点详情数据
	 */
	function node_detail()
	{
		$response = $this->setTime();
		if ($response !== null)
		{
			return $response;
		}
		
		$sn = 'CAS0530000150';
		
		$this->model('feedback')->insert(array(
			'sn' => $sn,
			'version' => '8.0',
			'cpu_type' => 'cpu_type',
			'mem_size' => 100 * 1024,
			'sys_disk_size' => 100 * 1024 * 1024 * 1024,
			'data_disk_size' => 100 * 1024 * 1024 * 1024 * 1024
		));
		
		$this->model('user_info')->insert(array(
			'sn' => $sn,
			'company' => '测试用数据'
		));
		
		$num = 0;
		
		$mode = request::param('mode');
		
		$this->_startTime = date('Y-m-d H:00:00', strtotime('-30 day'));
		$this->_endTime = date('Y-m-d H:00:00');
		
		$this->model('feedbackHistory')->startCompress();
		// 最近24小时的在线用户数据和磁盘数据
		$duration = 30 * 60;
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			$num += $this->model('feedbackHistory')->insert(array(
				'ctime' => $t_time,
				'sn' => $sn,
				'online' => $mode == 0 ? rand(0, 1000) : 1000,
				'sys_disk_used' => $mode == 0 ? rand(0, 100) : 50,
				'data_disk_used' => $mode == 0 ? rand(0, 100) : 50
			));
		}
		$this->model('feedbackHistory')->commitCompress();
		
		$this->model('cdn_traffic_stat')->startCompress();
		$this->model('traffic_stat')->startCompress();
		$duration = 5 * 60;
		for ($t_time = $this->_startTime; strtotime($t_time) < strtotime($this->_endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			$num += $this->model('cdn_traffic_stat')->insert(array(
				'make_time' => $t_time,
				'sn' => $sn,
				'service' => $mode == 0 ? rand(0, 100000) : 50000,
				'cache' => $mode == 0 ? rand(0, 100000) : 50000,
				'monitor' => $mode == 0 ? rand(0, 100000) : 50000,
				'cpu' => $mode == 0 ? rand(0, 100) : 50,
				'mem' => $mode == 0 ? rand(0, 100) : 50
			));
			$num += $this->model('traffic_stat')->insert(array(
				'create_time' => $t_time,
				'sn' => $sn,
				'service' => $mode == 0 ? rand(0, 100000) : 50000,
				'cache' => $mode == 0 ? rand(0, 100000) : 50000,
				'monitor' => $mode == 0 ? rand(0, 100000) : 50000,
				'cpu' => $mode == 0 ? rand(0, 100) : 50,
				'mem' => $mode == 0 ? rand(0, 100) : 50,
				'hit_user' => $mode == 0 ? rand(0, 10000) : 5000,
				'online_user' => $mode == 0 ? rand(0, 10000) : 10000
			));
		}
		$this->model('cdn_traffic_stat')->commitCompress();
		$this->model('traffic_stat')->commitCompress();
		
		return new json(json::OK, null, $num);
	}

	function clean()
	{
		$data = array(
			$this->model('feedbackHistory')->truncate(),
			$this->model('cdn_traffic_stat')->truncate(),
			$this->model('traffic_stat')->truncate(),
			$this->model('operation_stat')->truncate(),
			$this->model('feedback')->truncate(),
			$this->model('user_info')->truncate(),
			$this->model('cache')->truncate()
		);
		return new json(json::OK, null, array_sum($data));
	}

	function index()
	{
		return new view('dataCreator/index.php');
	}
}
