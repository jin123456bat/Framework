<?php
namespace application\algorithm;

use framework\core\component;
use framework\core\model;
use application\extend\cache;

class algorithm extends component
{
	private $_duration = 0;
	
	private $_starttime = '';
	
	private $_endtime = '';
	
	/**
	 * constructor
	 * @param unknown $starttime 开始时间点
	 * @param unknown $endtime 结束时间点
	 * @param unknown $duration 时间间隔，默认5分钟
	 */
	function __construct($starttime = '',$endtime = '',$duration = 300)
	{
		$this->_starttime = $starttime;
		$this->_endtime = $endtime;
		$this->_duration = $duration;
	}
	
	/**
	 * 设置时间间隔
	 * @param unknown $duration
	 */
	public function setDuration($duration)
	{
		$this->_duration = $duration;
	}
	
	/**
	 * 设置开始时间和结束时间
	 * @param unknown $starttime
	 * @param unknown $endtime
	 */
	public function setTime($starttime,$endtime)
	{
		$this->_starttime = $starttime;
		$this->_endtime = $endtime;
	}
	
	/**
	 * 计算CDS分时段的在线数量
	 */
	public function CDSOnlineNum()
	{
		$cds_max = 0;
		$cds_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$cds_detail[$t_time] = 1 * $this->model('feedbackHistory')
			->where('ctime >= ? and ctime < ?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->scalar('count(distinct(sn))');
			if ($cds_detail[$t_time] > $cds_max)
			{
				$cds_max = $cds_detail[$t_time];
			}
		}
		
		return array(
			'max' => $cds_max,
			'detail' => $cds_detail,
		);
	}
	
	/**
	 * 在线用户数量
	 */
	public function USEROnlineNum($sn = NULL)
	{
		$user_max = 0;
		$user_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			if (!empty($sn))
			{
				$this->model('feedbackHistory')->where('sn=?',array($sn));
			}
			$max_online_gourp_sn = $this->model('feedbackHistory')
			->group('sn')
			->where('update_time >= ? and update_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration),
			))
			->select('max(online) as online');
				
			$user_detail[$t_time] = 0;
			foreach ($max_online_gourp_sn as $online)
			{
				$user_detail[$t_time] += $online['online'];
			}
			if ($user_detail[$t_time] > $user_max)
			{
				$user_max = $user_detail[$t_time];
			}
		}
		return array(
			'max' => $user_max,
			'detail' => $user_detail,
		);
	}
	
	/**
	 * 服务流速
	 * @return number[]|number[][]
	 */
	public function ServiceMax()
	{
		$service_max_max = 0;
		$service_max_detail = array();
		
		$traffic_stat = $this->traffic_stat();
		$service_max_detail = $traffic_stat['service'];
		
		foreach ($service_max_detail as $time => $value)
		{
			if ($value > $service_max_max)
			{
				$service_max_max = $value;
			}
		}
		
		return array(
			'max' => $service_max_max,
			'detail' => $service_max_detail,
		);
	}
	
	public function ServiceSum()
	{
		$service_sum_sum = 1*$this->model('operation_stat')->where('make_time >= ? and make_time < ?',array($this->_starttime,$this->_endtime))->sum('service_size');
		return array(
			'max' => $service_sum_sum,
			'detail' => array(),
		);
	}
	
	/**
	 * 获取分类名称
	 * @param array $r 一个包含class和category的数组
	 * @return string
	 */
	private function getCategoryName($r)
	{
		$category = $this->getConfig('category');
		
		switch ($r['class'])
		{
			case 0:$classname = isset($category['http'][$r['category']])?$category['http'][$r['category']]:'其他';break;
			case 1:$classname = isset($category['mobile'][$r['category']])?$category['mobile'][$r['category']]:'其他';break;
			case 2:
				if ($r['category']>=128)
				{
					$classname = isset($category['videoLive'][$r['category']-128])?$category['videoLive'][$r['category']-128]:'其他';break;
				}
				else
				{
					$classname = isset($category['videoDemand'][$r['category']])?$category['videoDemand'][$r['category']]:'其他';break;
				}
		}
		return $classname;
	}
	
	public function CPService()
	{
		$cp_service = array();
		
		//取出service累计最大的前9个分类
		$categoryTop = $this->model('operation_stat')->where('make_time>=? and make_time<?',array(
			$this->_starttime,
			$this->_endtime
		))
		->group(array('class','category'))
		->order('service_sum','desc')
		->limit(9)
		->forceIndex('primary')//强制索引
		->select(array(
			'category',
			'class',
			'sum(service_size) as service_sum',
		));
		
		
		$top = array();
		foreach ($categoryTop as $r)
		{
			$top[] = array(
				'category' => $r['category'],
				'class' => $r['class'],
			);
		}
	
		$total_operation_stat = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$result = $this->model('operation_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->select(array(
				'category',
				'class',
				'service_size',
			));
			
			foreach ($top as $r)
			{
				$classname = $this->getCategoryName($r);
				$cp_service[$classname][$t_time] = 0;
			}
			$cp_service['其他'][$t_time] = 0;
			
			$total_operation_stat[$t_time] = 0;
			foreach ($result as $r)
			{
				if (in_array(array(
					'category'=>$r['category'],
					'class' => $r['class']
				), $top,true))
				{
					$classname = $this->getCategoryName($r);
				}
				else
				{
					$classname = '其他';
				}
				
				$total_operation_stat[$t_time] += $r['service_size'];
				$cp_service[$classname][$t_time] += $r['service_size'];
			}
		}
		
		$service = $this->ServiceMax();
		$service = $service['detail'];
		
		foreach ($cp_service as $classname => &$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = $service[$time] * division($value,$total_operation_stat[$time]);
			}
		}
		
		return array(
			'max' => NULL,
			'detail' => $cp_service
		);
	}
	
	
	
	/**
	 * 网卡流速
	 * @return number[][]|number[]|boolean[]
	 */
	public function traffic_stat($sn = NULL)
	{
		$cache_max_detail = array();
		$service_max_detail = array();
		$monitor_max_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$temp_service = array();
			$temp_cache = array();
			$temp_monitor = array();
			
			$traffic_stat_model = $this->model('traffic_stat');
			if (!empty($sn))
			{
				$traffic_stat_model->where('sn=?',array($sn));
			}
			$traffic_stat = $traffic_stat_model->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->group('create_time')
			->select(array(
				'create_time'=>'DATE_FORMAT(create_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache' => 'sum(cache)',
				'sum_monitor' => 'sum(monitor)',
			));
			
			foreach ($traffic_stat as $r)
			{
				$temp_service[$r['create_time']] = $r['sum_service'];
				$temp_cache[$r['create_time']] = $r['sum_cache'];
				$temp_monitor[$r['create_time']] = $r['sum_monitor'];
			}
			
			$cdn_traffic_stat_model = $this->model('cdn_traffic_stat');
			if (!empty($sn))
			{
				$cdn_traffic_stat_model->where('sn like ?',array('%'.substr($sn, 3)));
			}
			$cdn_traffic_stat = $cdn_traffic_stat_model
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->group('make_time')
			->select(array(
				'make_time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service' => 'sum(service)',
				'sum_cache' => 'sum(cache)',
				'sum_monitor' => 'sum(monitor)',
			));
			
			foreach ($cdn_traffic_stat as $r)
			{
				if (isset($temp_service[$r['make_time']]))
				{
					$temp_service[$r['make_time']] += $r['sum_service'];
				}
				else
				{
					$temp_service[$r['make_time']] = $r['sum_service']*1;
				}
				
				if (isset($temp_cache[$r['make_time']]))
				{
					$temp_cache[$r['make_time']] += $r['sum_service'];
				}
				else
				{
					$temp_cache[$r['make_time']] = $r['sum_service']*1;
				}
				
				if (isset($temp_monitor[$r['make_time']]))
				{
					$temp_monitor[$r['make_time']] += $r['sum_monitor'];
				}
				else
				{
					$temp_monitor[$r['make_time']] = $r['sum_service']*1;
				}
			}
			
			$xvirt_traffic_stat_model = $this->model('xvirt_traffic_stat');
			if (!empty($sn))
			{
				$xvirt_traffic_stat_model->where('sn like ?',array('%'.substr($sn, 3)));
			}
			//traffic_stat + cdn_traffic_stat - xvirt_traffic_stat
			$xvirt = $xvirt_traffic_stat_model->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->group('make_time')
			->select(array(
				'make_time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache'=>'sum(cache)',
			));
			foreach ($xvirt as $r)
			{
				if (isset($temp_cache[$r['make_time']]) && $temp_cache[$r['make_time']] > $r['sum_cache'])
				{
					$temp_cache[$r['make_time']] -= $r['sum_cache'];
				}
				if (isset($temp_service[$r['make_time']]) && $temp_service[$r['make_time']] > $r['sum_service'])
				{
					$temp_service[$r['make_time']] -= $r['sum_service'];
				}
			}
			
			$max = 0;
			$max_time = '';
			foreach ($temp_service as $time=>$service)
			{
				if ($service>=$max)
				{
					$max = $service * 1;
					$max_time = $time;
				}
			}
			$service_max_detail[$t_time] = $max;
			if (!empty($max_time))
			{
				$cache_max_detail[$t_time] = $temp_cache[$max_time];
				$monitor_max_detail[$t_time] = $temp_monitor[$max_time];
			}
			else
			{
				$cache_max_detail[$t_time] = 0;
				$monitor_max_detail[$t_time] = 0;
			}
		}
		$data = array(
			'service' => $service_max_detail,
			'cache' => $cache_max_detail,
			'monitor' => $monitor_max_detail
		);
		return $data;
	}
	
	/**
	 * 计算独立的服务流速和缓存流速
	 */
	function traffic_stat_alone($sn = NULL)
	{
		$service = array();
		$cache = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$temp_service = array();
			$temp_cache = array();
			
			if (!empty($sn))
			{
				$this->model('traffic_stat')->where('sn=?',array($sn));
			}
			$traffic_stat = $this->model('traffic_stat')
			->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->group('create_time')
			->select(array(
				'create_time'=>'DATE_FORMAT(create_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache' => 'sum(cache)',
			));
				
			foreach ($traffic_stat as $stat)
			{
				if (isset($temp_service[$stat['create_time']]))
				{
					$temp_service[$stat['create_time']] += $stat['sum_service'];
				}
				else
				{
					$temp_service[$stat['create_time']] = $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['create_time']]))
				{
					$temp_cache[$stat['create_time']] += $stat['sum_cache'];
				}
				else
				{
					$temp_cache[$stat['create_time']] = $stat['sum_cache'];
				}
			}
				
			if (!empty($sn))
			{
				$this->model('cdn_traffic_stat')->where('sn like ?',array('%'.substr($sn, 3)));
			}
			$cdn_traffic_stat = $this->model('cdn_traffic_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->group('make_time')
			->select(array(
				'make_time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service' => 'sum(service)',
				'sum_cache' => 'sum(cache)',
			));
			foreach ($cdn_traffic_stat as $stat)
			{
				if (isset($temp_service[$stat['make_time']]))
				{
					$temp_service[$stat['make_time']] += $stat['sum_service'];
				}
				else
				{
					$temp_service[$stat['make_time']] = $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['make_time']]))
				{
					$temp_cache[$stat['make_time']] += $stat['sum_cache'];
				}
				else
				{
					$temp_cache[$stat['make_time']] = $stat['sum_cache'];
				}
			}
			
			if (!empty($sn))
			{
				$this->model('xvirt_traffic_stat')
				->where('sn like ?',array('%'.substr($sn, 3)));
			}
			$xvirt = $this->model('xvirt_traffic_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->group('make_time')
			->select(array(
				'make_time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache'=>'sum(cache)',
			));
			foreach ($xvirt as $stat)
			{
				if (isset($temp_service[$stat['make_time']]))
				{
					$temp_service[$stat['make_time']] -= $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['make_time']]))
				{
					$temp_cache[$stat['make_time']] -= $stat['sum_cache'];
				}
			}

			$service[$t_time] = empty($temp_service)?0:max($temp_service);
			$cache[$t_time] = empty($temp_cache)?0:max($temp_cache);
		}
		return array('service' => $service,'cache' => $cache);
	}
	
	function operation_stat()
	{
		$operation_stat = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$result = $this->model('operation_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration),
			))
			->find(array(
				'sum_service'=>'sum(service_size)',
				'sum_cache'=>'sum(cache_size)'
			));
			$operation_stat['service'][$t_time] = $result['sum_service']*1;
			$operation_stat['cache'][$t_time] = $result['sum_cache']*1;
		}
		return $operation_stat;
	}
}