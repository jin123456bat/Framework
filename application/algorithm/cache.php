<?php
namespace application\algorithm;
use application\extend\BaseComponent;
use framework\core\database\sql;
use framework\core\model;

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
	 * 网卡流速
	 * 计算最大Service和对应的cache
	 * @return number[][]|number[]|boolean[]
	 */
	public function traffic_stat_algorithm($duration,$startTime,$endTime,$sn = array())
	{
		$sn = $this->combineSns($sn);
		
		switch ($duration)
		{
			case 5*60:
				$time = 'concat(date_format(time,"%Y-%m-%d %H"),":",LPAD(floor(date_format(time,"%i")/5)*5,2,0),":00")';
				break;
			case 30*60:
				$time = 'if( date_format(time,"%i")<30,date_format(time,"%Y-%m-%d %H:00:00"),date_format(time,"%Y-%m-%d %H:30:00") )';
				break;
			case 60*60:
				$time = 'date_format(time,"%Y-%m-%d %H:00:00")';
				break;
			case 2*60*60:
				$time = 'concat(date_format(time,"%Y-%m-%d")," ",LPAD(floor(date_format(time,"%H")/2)*2,2,0),":00:00")';
				break;
			case 24*60*60:
				$time = 'date_format(time,"%Y-%m-%d 00:00:00")';
				break;
			default:
				echo "traffic_stat_algorithm中duration错误";
		}
		
		$cache_max_detail = array();
		$service_max_detail = array();
		$monitor_max_detail = array();
		$max_cache_detail = array();
		$icache_cache_detail = array();
		$vpe_cache_detail = array();
		
		$traffic_stat = new sql();
		$cdn_traffic_stat = new sql();
		$xvirt_traffic_stat = new sql();
			
		$xvirt_traffic_stat->from('cds_v2.xvirt_traffic_stat')
		->in('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$startTime,$endTime
		))
		->select(array(
			'time' => 'date_format(make_time,"%Y-%m-%d %H:%i")',
			'service' => '-1*service',
			'cache' => 0,
			'monitor' => 0,
			'icache_cache' => 0,
			'vpe_cache' => 0,
		));
			
		$traffic_stat->from('ordoac.traffic_stat')
		->in('sn',$sn)
		->where('create_time>=? and create_time<?',array(
			$startTime,$endTime
		))
		->select(array(
			'time'=>'date_format(create_time,"%Y-%m-%d %H:%i")',
			'service'=>'1024*service',
			'cache' => '1024*cache',
			'monitor'=>'1024*monitor',
			'icache_cahce' => '1024*cache',
			'vpe_cache' => 0,
		));
			
		$sn = array_map(function($s){
			return '%'.substr($s, 3);
		}, $sn);
		$cdn_traffic_stat->from('cds_v2.cdn_traffic_stat')
		->likein('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$startTime,$endTime
		))
		->select(array(
			'time' => 'date_format(make_time,"%Y-%m-%d %H:%i")',
			'service',
			'cache',
			'monitor',
			'icache_cache' => 0,
			'vpe_cache' => 'cache',
		));
	
		$xvirt_traffic_stat->union(true, $cdn_traffic_stat, $traffic_stat);
	
		$t = new sql();
		$t->setFrom($xvirt_traffic_stat,'t');
		$t->group('time');
		$t->select(array(
			'time',
			'service' => 'sum(service)',
			'cache' => 'sum(cache)',
			'monitor' => 'sum(monitor)',
			'icache_cache' => 'sum(icache_cache)',
			'vpe_cache' => 'sum(vpe_cache)',
		));
	
		$result = $this->model('traffic_stat')
		->setFrom($t,'a')
		->group('timenode')
		->select(array(
			'timenode'=>$time,
			'line' => 'max(concat(lpad(service,20,0),"-",lpad(cache,20,0),"-",lpad(monitor,20,0)))',
			'max_cache' => 'max(cache)',
			'icache_cache' => 'max(concat(lpad(service,20,0),"-",lpad(icache_cache,20,0)))',
			'vpe_cache' => 'max(concat(lpad(service,20,0),"-",lpad(vpe_cache,20,0)))',
		));
		
		//重置from
		$this->model('traffic_stat')->setFrom('traffic_stat');
		
		foreach ($result as $r)
		{
			list($service,$cache,$monitor) = explode('-', $r['line']);
			list($icache_service,$icache_cache) = explode('-', $r['icache_cache']);
			list($vpe_service,$vpe_cache) = explode('-', $r['vpe_cache']);
			$service_max_detail[$r['timenode']] = $service*1;
			$cache_max_detail[$r['timenode']] = $cache*1;
			$monitor_max_detail[$r['timenode']] = $monitor*1;
			$max_cache_detail[$r['timenode']] = $r['max_cache']*1;
			$icache_cache_detail[$r['timenode']] = $icache_cache*1;
			$vpe_cache_detail[$r['timenode']] = $vpe_cache*1;
		}
		
		for($t_time = $startTime;strtotime($t_time)<strtotime($endTime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			if (!isset($service_max_detail[$t_time]))
			{
				$service_max_detail[$t_time] = 0;
			}
			if (!isset($cache_max_detail[$t_time]))
			{
				$cache_max_detail[$t_time] = 0;
			}
			if (!isset($monitor_max_detail[$t_time]))
			{
				$monitor_max_detail[$t_time] = 0;
			}
			if (!isset($max_cache_detail[$t_time]))
			{
				$max_cache_detail[$t_time] = 0;
			}
			if (!isset($icache_cache_detail[$t_time]))
			{
				$icache_cache_detail[$t_time] = 0;
			}
			if (!isset($vpe_cache_detail[$t_time]))
			{
				$vpe_cache_detail[$t_time] = 0;
			}
		}
		
		$data = array(
			'service' => $service_max_detail,
			'cache' => $cache_max_detail,
			'monitor' => $monitor_max_detail,
			'max_cache' => $max_cache_detail,
			'icache_cache' => $icache_cache_detail,
			'vpe_cache' => $vpe_cache_detail,
		);
		return $data;
	}
	
	/**
	 * 分时间段，累计流量
	 * @return number
	 */
	function operation_stat_algorithm($duration,$startTime,$endTime,$sn = array())
	{
		$sn = $this->combineSns($sn);
		$sn = array_map(function($s){
			return '%'.substr($s,3);
		}, $sn);
		
		switch ($duration)
		{
			case 300:$time = 'concat(date_format(make_time,"%Y-%m-%d %H"),":",LPAD(floor(date_format(make_time,"%i")/5)*5,2,0),":00")';break;
			case 30*60:$time = 'if( date_format(make_time,"%i")<30,date_format(make_time,"%Y-%m-%d %H:00:00"),date_format(make_time,"%Y-%m-%d %H:30:00") )';break;
			case 60*60:$time = 'date_format(make_time,"%Y-%m-%d %H:00:00")';break;
			case 24*60*60:$time = 'date_format(make_time,"%Y-%m-%d 00:00:00")';break;
			default:echo "operation_stat_algorithm中duration错误";
		}
		
		$operation_stat = $this->model('operation_stat')
		->likein('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$startTime,$endTime
		))
		->group(array('time','sn','class','category'))
		->select(array(
			'time' => $time,
			'sn',
			'class',
			'category',
			'service_size' => 'sum(service_size)',
			'cache_size' => 'sum(cache_size)',
			'proxy_cache_size' => 'sum(proxy_cache_size)',
		));
		return $operation_stat;
	}
	
	/**
	 * 获取流速的相关信息
	 * @param unknown $startTime
	 * @param unknown $endTime
	 * @param number $duration
	 */
	function get_traffic_stat($startTime,$endTime,$duration = 300)
	{
		switch ($duration)
		{
			case 300:$tableName = 'traffic_stat_5_minute';break;
			case 60*60:$tableName = 'traffic_stat_1_hour';break;
			case 2*60*60:$tableName = 'traffic_stat_2_hour';break;
			case 24*60*60:$tableName = 'traffic_stat_1_day';break;
		}
		return $this->model($tableName)->where('time>=? and time<?',array(
			$startTime,$endTime
		))->select();
	}
	
	
	/**
	 * 创建sn的流速数据
	 * @param unknown $duration
	 * @param unknown $sn
	 * @param unknown $startTime
	 * @param unknown $endTime
	 * @return string[]|string[][]
	 */
	function traffic_stat_sn($duration,$startTime = NULL,$endTime = NULL)
	{
		if (empty($startTime) && empty($endTime))
		{
			list($startTime,$endTime) = $this->getDataTime(__FUNCTION__, $duration);
			
			$time_traffic_stat = $this->model('traffic_stat')
			->where('add_time >=? and add_time<?',array(
				$startTime,$endTime
			))
			->find('max(create_time) as max,min(create_time) as min');
			
			$time_cdn_traffic_stat = $this->model('cdn_traffic_stat')
			->where('create_time>=? and create_time<?',array(
				$startTime,$endTime
			))
			->find('max(make_time) as max,min(make_time) as min');
			
			$time_xvirt_traffic_stat = $this->model('xvirt_traffic_stat')
			->where('create_time>=? and create_time<?',array(
				$startTime,$endTime
			))->find('max(make_time) as max,min(make_time) as min');
				
			$max_time = max($time_cdn_traffic_stat['max'],$time_cdn_traffic_stat['max'],$time_xvirt_traffic_stat['max']);
			$min_time = min($time_cdn_traffic_stat['min'],$time_cdn_traffic_stat['min'],$time_xvirt_traffic_stat['min']);
				
			$min_time = date('Y-m-d H:i:s',floor(strtotime($min_time)/$duration)*$duration);
			$max_time = date('Y-m-d H:i:s',ceil(strtotime($max_time)/$duration)*$duration);
		}
		else
		{
			$min_time = $startTime;
			$max_time = $endTime;
		}
		
		$tableName = __FUNCTION__.'_'.$duration;
	
		$sn = $this->combineSns();
		$this->model($tableName)->startCompress();
		foreach ($sn as $s)
		{
			$traffic_stat = $this->traffic_stat_algorithm($duration,$min_time,$max_time,$s);
				
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
			'service','cache','monitor','max_cache','icache_cache','vpe_cache',
		));
		$this->model($tableName)->commitCompress();
	
		return array(
			'starttime' => $startTime,
			'endtime' => $endTime,
		);
	}
	
	/**
	 * 创建总流速缓存数据
	 */
	function traffic_stat($duration,$startTime = NULL,$endTime = NULL)
	{
		if (empty($startTime) && empty($endTime))
		{
			list($startTime,$endTime) = $this->getDataTime(__FUNCTION__, $duration);
			
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
		}
		else
		{
			$min_time = $startTime;
			$max_time = $endTime;
		}
		
		$tableName = __FUNCTION__.'_'.$duration;
		
		$traffic_stat = $this->traffic_stat_algorithm($duration,$min_time,$max_time);
		
		$this->model($tableName)->startCompress();
		foreach ($traffic_stat['service'] as $time => $service)
		{
			$this->model($tableName)
			->insert(array(
				'time' => $time,
				'service' => $service,
				'cache' => $traffic_stat['cache'][$time],
				'monitor' => $traffic_stat['monitor'][$time],
				'max_cache' => $traffic_stat['max_cache'][$time],
				'icache_cache' => $traffic_stat['icache_cache'][$time],
				'vpe_cache' => $traffic_stat['vpe_cache'][$time],
			));
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
	
	function operation_stat($duration,$startTime = NULL,$endTime = NULL)
	{
		if (empty($startTime) && empty($endTime))
		{
			list($startTime,$endTime) = $this->getDataTime(__FUNCTION__, $duration);
			
			$time = $this->model('operation_stat')->where('create_time>=? and create_time<?',array(
				$startTime,$endTime
			))
			->find(array(
				'max' => 'max(make_time)',
				'min' => 'min(make_time)',
			));
			
			$max_time = date('Y-m-d H:i:s',ceil(strtotime($time['max'])/$duration)*$duration);
			$min_time = date('Y-m-d H:i:s',floor(strtotime($time['max'])/$duration)*$duration);
		}
		else
		{
			$min_time = $startTime;
			$max_time = $endTime;
		}
			
		$operation_stat = $this->operation_stat_algorithm($duration, $min_time, $max_time);
		
		if ($duration == 300 || $duration==3600 || $duration == 86400)
		{
			$tableName = 'operation_stat_'.$duration;
			$operation_stat_info = array();
			foreach ($operation_stat as $stat)
			{
				if (isset($operation_stat_info[$stat['time']]['service_size']))
				{
					$operation_stat_info[$stat['time']]['service_size'] += $stat['service_size'];
				}
				else
				{
					$operation_stat_info[$stat['time']]['service_size'] = $stat['service_size'];
				}
				
				if (isset($operation_stat_info[$stat['time']]['cache_size']))
				{
					$operation_stat_info[$stat['time']]['cache_size'] += $stat['cache_size'];
				}
				else
				{
					$operation_stat_info[$stat['time']]['cache_size'] = $stat['cache_size'];
				}
				if (isset($operation_stat_info[$stat['time']]['proxy_cache_size']))
				{
					$operation_stat_info[$stat['time']]['proxy_cache_size'] += $stat['proxy_cache_size'];
				}
			}
			$this->model($tableName)->startCompress();
			foreach ($operation_stat_info as $time => $st)
			{
				$data = array(
					'time' => $time,
					'service_size' => $st['service_size'],
					'cache_size' => $st['cache_size'],
					'proxy_cache_size' => $st['proxy_cache_size'],
				);
				$this->model($tableName)->insert($data);
			}
			$this->model($tableName)->duplicate(array(
				'service_size','cache_size','proxy_cache_size'
			));
			$this->model($tableName)->commitCompress();
			unset($operation_stat_info);
			unset($st);
			unset($stat);
		}
		
		
		if ($duration == 1800 || $duration == 7200 || $duration == 86400)
		{
			$operation_stat_class_category = array();
			$tableName_operation_stat_class_category = 'operation_stat_class_category_'.$duration;
			foreach ($operation_stat as $stat)
			{
				if (isset($operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['service_size']))
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['service_size'] += $stat['service_size'];
				}
				else
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['service_size'] = $stat['service_size'];
				}
				if (isset($operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['cache_size']))
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['cache_size'] += $stat['cache_size'];
				}
				else
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['cache_size'] = $stat['cache_size'];
				}
				if (isset($operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['proxy_cache_size']))
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['proxy_cache_size'] += $stat['proxy_cache_size'];
				}
				else
				{
					$operation_stat_class_category[$stat['time']][$stat['class']][$stat['category']]['proxy_cache_size'] = $stat['proxy_cache_size'];
				}
			}
			unset($stat);
			$this->model($tableName_operation_stat_class_category)->startCompress();
			foreach ($operation_stat_class_category as $time => $v)
			{
				foreach ($v as $class => $vv)
				{
					foreach ($vv as $category => $st)
					{
						$data = array(
							'time' => $time,
							'class' => $class,
							'category' => $category,
							'service_size' => $st['service_size'],
							'cache_size' => $st['cache_size'],
							'proxy_cache_size' => $st['proxy_cache_size'],
						);
						$this->model($tableName_operation_stat_class_category)->insert($data);
					}
				}
			}
			unset($v);
			unset($vv);
			unset($st);
			$this->model($tableName_operation_stat_class_category)->duplicate(array(
				'service_size','cache_size','proxy_cache_size'
			));
			$this->model($tableName_operation_stat_class_category)->commitCompress();
		}
		return array(
			'starttime' => $startTime,
			'endtime' => $endTime,
		);
	}
}