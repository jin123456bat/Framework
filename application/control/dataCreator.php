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
		'CAS0530000159',	
	);
	
	/**
	 * 创建首页CDS曲线图
	 */
	function main_overview_cds()
	{
		$response = $this->setTime();
		if ($response!==NULL)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':$this->_duration_second = 30*60;break;
			case 'hourly':$this->_duration_second = 2*60*60;break;
			case 'daily':$this->_duration_second = 24*60*60;break;
		}
		
		$num = 0;
		$duration = $this->_duration_second;
		
		$mode = request::param('mode');
		$i = 0;
		
		for ($t_time = $this->_startTime;strtotime($t_time)<strtotime($this->_endTime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			$j = rand(1,count($this->_sn));
			foreach ($this->_sn as $sn)
			{
				$i++;
				if($this->model('feedbackHistory')->insert(array(
					'ctime' => $t_time,
					'sn' => $sn,
				)))
				{
					$num++;
				}
				if ($mode == 0 && $i == $j)
				{
					$i = 0;
					break;
				}
			}
		}
		
		return new json(json::OK,NULL,$num);
	}
	
	/**
	 * 创建首页在线人数曲线图
	 * @return unknown|\framework\core\response\json
	 */
	function main_overview_online()
	{
		$response = $this->setTime();
		if ($response!==NULL)
		{
			return $response;
		}
	
		switch ($this->_duration)
		{
			case 'minutely':$this->_duration_second = 30*60;break;
			case 'hourly':$this->_duration_second = 2*60*60;break;
			case 'daily':$this->_duration_second = 24*60*60;break;
		}
	
		$num = 0;
		$duration = $this->_duration_second;
	
		$mode = request::param('mode');
		
		for ($t_time = $this->_startTime;strtotime($t_time)<strtotime($this->_endTime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			foreach ($this->_sn as $sn)
			{
				if($this->model('feedbackHistory')->insert(array(
					'ctime' => $t_time,
					'sn' => $sn,
					'online' => $mode==0?rand(100,1000):1000,
				)))
				{
					$num++;
				}
			}
		}
	
		return new json(json::OK,NULL,$num);
	}
	
	/**
	 * 创建首页在服务流速
	 * @return unknown|\framework\core\response\json
	 */
	function main_overview_service_max()
	{
		$response = $this->setTime();
		if ($response!==NULL)
		{
			return $response;
		}
		
		switch ($this->_duration)
		{
			case 'minutely':$this->_duration_second = 5*60;break;
			case 'hourly':$this->_duration_second = 2*60*60;break;
			case 'daily':$this->_duration_second = 24*60*60;break;
		}
	
		$num = 0;
		$duration = $this->_duration_second;
	
		$mode = request::param('mode');
	
		for ($t_time = $this->_startTime;strtotime($t_time)<strtotime($this->_endTime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			foreach ($this->_sn as $sn)
			{
				$num += $this->model('traffic_stat')->insert(array(
					'create_time' => $t_time,
					'service' => 2000,
					'sn' => $sn,
				));
				$num += $this->model('cdn_traffic_stat')->insert(array(
					'make_time' => $t_time,
					'service' => 2000,
					'sn' => $sn,
				));
			}
		}
	
		return new json(json::OK,NULL,$num);
	}
	
	function clean()
	{
		$data = array(
			$this->model('feedbackHistory')->truncate(),
			$this->model('cdn_traffic_stat')->truncate(),
			$this->model('traffic_stat')->truncate(),
		);
		return new json(json::OK,NULL,array_sum($data));
	}
	
	function index()
	{
		return new view('dataCreator/index.php');
	}
}