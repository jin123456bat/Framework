<?php
namespace application\algorithm;
use application\extend\BaseComponent;

class cache extends BaseComponent
{
	function initlize()
	{
		
	}
	
	private function getDataTime($name,$duration)
	{
		$startTime = $this->model('build_data_log')
		->where('name=? and duration=?',array($name,$duration))
		->scalar('max(data_endtime)');
		if (empty($startTime))
		{
			$startTime = date('Y-m-d H:i:s',strtotime('-7 day'));
		}
		
		$endTime = date('Y-m-d H:i:s');
		return array($startTime,$endTime);
	}
	
	/**
	 * 创建流速缓存数据
	 */
	function traffic_stat($duration)
	{
		list($startTime,$endTime) = $this->getDataTime('traffic_stat', $duration);
		
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
					'max_cache' => $traffic_stat['max_cache'][$time],
					'icache_cache' => $traffic_stat['icache_cache'][$time],
					'vpe_cache' => $traffic_stat['vpe_cache'][$time],
				));
			}
		}
		$this->model($tableName)->duplicate(array(
			'service','cache','monitor','max_cache','icache_cache','vpe_cache'
		));
		$this->model($tableName)->commitCompress();
		
		return array(
			'starttime' => $startTime,
			'endtime' => $endTime,
		);
	}
	
	function operation_stat($duration)
	{
		list($startTime,$endTime) = $this->getDataTime('operation_stat', $duration);
		
		$time = $this->model('operation_stat')->where('create_time>=? and create_time<?',array(
			$startTime,$endTime
		))
		->find(array(
			'max' => 'max(make_time)',
			'min' => 'min(make_time)',
		));
		
		$max_time = date('Y-m-d H:i:s',ceil(strtotime($time['max'])/$duration)*$duration);
		$min_time = date('Y-m-d H:i:s',floor(strtotime($time['max'])/$duration)*$duration);
		
		$operation_stat = array();
		$sn = $this->combineSns();
		for($t_time = $min_time;strtotime($t_time)<strtotime($max_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			foreach ($sn as $s)
			{
				$result = $this->model('operation_stat')
				->where('sn like ?',array('%'.substr($s,3)))
				->where('make_time>=? and make_time<?',array(
					$t_time,
					date('Y-m-d H:i:s',strtotime($t_time) + $duration),
				))
				->group(array('class','category'))
				->find(array(
					'class',
					'category',
					'service_size'=>'sum(service_size)',
					'cache_size'=>'sum(cache_size)',
					'proxy_cache_size' => 'sum(proxy_cache_size)'
				));
				$operation_stat[] = array(
					'time' => $t_time,
					'sn' => $s,
					'class' => $result['class'],
					'category' => $result['category'],
					'service_size' => 'service_size',
					'cache_size' => 'cache_size',
					'proxy_cache_size' => 'proxy_cache_size',
				);
			}
		}
		
		switch ($duration)
		{
			case 24*3600:$tableName = 'operation_stat_1_day';break;
			case 3600:$tableName = 'operation_stat_1_hour';break;
			case 300:$tableName = 'operation_stat_5_minute';break;
			case 30*60:$tableName = 'operation_stat_30_minute';break;
		}
			
		$this->model($tableName)->startCompress();
		foreach ($operation_stat as $stat)
		{
			$this->model($tableName)->insert($stat);
		}
		$this->model($tableName)->duplicate(array(
			'service_size','cache_size','proxy_cache_size'
		));
		
		$this->model($tableName)->commitCompress();
		return array(
			'starttime' => $startTime,
			'endtime' => $endTime,
		);
	}
}