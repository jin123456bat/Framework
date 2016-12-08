<?php
namespace application\algorithm;
use framework\core\component;
use framework\core\debugger;

class cache extends component
{
	function initlize()
	{
		
	}
	
	/**
	 * 创建流速缓存数据
	 */
	function traffic_stat($duration)
	{
		$startTime = $this->model('build_data_log')->scalar('max(data_endtime)');
		if (empty($startTime))
		{
			$startTime = date('Y-m-d H:i:s',strtotime('-5 minute'));
		}
		
		$endTime = date('Y-m-d H:i:s');
		
		
		$time_traffic_stat = $this->model('traffic_stat')->where('add_time >=? and add_time<?',array(
			$startTime,$endTime
		))->find('max(create_time) as max,min(create_time) as min');
		$time_cdn_traffic_stat = $this->model('cdn_traffic_stat')->where('create_time>=? and create_time<?',array(
			$startTime,$endTime
		))->find('max(make_time) as max,min(make_time) as min');
		$time_xvirt_traffic_stat = $this->model('xvirt_traffic_stat')->where('create_time>=? and create_time<?',array(
			$startTime,$endTime
		))->find('max(make_time) as max,min(make_time) as min');
		
		$max_time = max($time_cdn_traffic_stat['max'],$time_cdn_traffic_stat['max'],$time_xvirt_traffic_stat['max']);
		$min_time = min($time_cdn_traffic_stat['min'],$time_cdn_traffic_stat['min'],$time_xvirt_traffic_stat['min']);
		
		$min_time = date('Y-m-d H:i:s',floor(strtotime($min_time)/$duration)*$duration);
		$max_time = date('Y-m-d H:i:s',ceil(strtotime($max_time)/$duration)*$duration);
		$algorithm = new algorithm($min_time,$max_time,$duration);
		
		switch ($duration)
		{
			case 300:$tableName = 'traffic_stat_5_minute';break;
			case 60*60:$tableName = 'traffic_stat_1_hour';break;
			case 2*60*60:$tableName = 'traffic_stat_2_hour';break;
			case 24*60*60:$tableName = 'traffic_stat_1_day';break;
		}
		
		$sn = $this->combineSns();
		$this->model($tableName)->startCompress();
		foreach ($sn as $s)
		{
			$traffic_stat = $algorithm->traffic_stat($s);
			foreach ($traffic_stat['service'] as $time => $service)
			{
				$this->model($tableName)
				->insert(array(
					'time' => $time,
					'sn' => $s,
					'service' => $service,
					'cache' => $traffic_stat['cache'][$time],
					'monitor' => $traffic_stat['monitor'][$time],
				));
			}
		}
		$this->model($tableName)->duplicate(array(
			'service','cache','monitor'
		));
		$this->model($tableName)->commitCompress();
		
		return array(
			'starttime' => $startTime,
			'endtime' => $endTime,
		);
	}
}