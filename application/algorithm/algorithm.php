<?php
namespace application\algorithm;

use framework\core\model;
use application\extend\cache;
use application\extend\BaseComponent;

class algorithm extends BaseComponent
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
	public function CDSOnlineNum($sn = array())
	{
		$sn = $this->combineSns($sn);
		
		$cds_max = 0;
		$cds_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			if (!empty($sn))
			{
				if (is_array($sn))
				{
					$this->model('feedbackHistory')->In('sn',$sn);
				}
				else if (is_scalar($sn))
				{
					$this->model('feedbackHistory')->where('sn=?',array($sn));
				}
			}
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
	public function USEROnlineNum($sn = array())
	{
		$sn = $this->combineSns($sn);
		
		$user_max = 0;
		$user_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			if (!empty($sn))
			{
				if (is_array($sn))
				{
					$this->model('feedbackHistory')->In('sn',$sn);
				}
				else if (is_scalar($sn))
				{
					$this->model('feedbackHistory')->where('sn=?',array($sn));
				}
			}
			$max_online_gourp_sn = $this->model('feedbackHistory')
			->group('sn')
			->where('update_time >= ? and update_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration),
			))
			->select('max(online) as online,sn,ctime');
			
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
	public function ServiceMax($sn = array())
	{
		$traffic_stat = $this->traffic_stat($sn);
		$service_max_detail = $traffic_stat['service'];
		$service_max_max = max($service_max_detail);
		
		return array(
			'max' => $service_max_max,
			'detail' => $service_max_detail,
		);
	}
	
	/**
	 * 计算累计流量，不分时间段
	 * @param array $sn
	 * @return number[]
	 */
	public function ServiceSum($sn = array())
	{
		$sn = $this->combineSns($sn);
		
		if (!empty($sn))
		{
			if (is_array($sn))
			{
				$sql = '';
				$s = array_shift($sn);
				while ($s)
				{
					$sql .= 'sn like ? or ';
					$param[] = '%'.substr($s,3);
					$s = array_shift($sn);
				}
				$sql = substr($sql, 0,-4);
				$this->model('operation_stat')->where($sql,$param);
			}
			else if(is_scalar($sn))
			{
				$this->model('operation_stat')->where('sn like ?',array('%'.substr($sn, 3)));
			}
		}
		$service_sum_sum = 1*$this->model('operation_stat')
		->where('make_time >= ? and make_time < ?',array(
			$this->_starttime,
			$this->_endtime
		))
		->sum('service_size');
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
	
	/**
	 * 分CP服务流速
	 * @param number $top
	 * @return NULL[]|unknown[]
	 */
	public function CPService($sn = array(),$top = 9)
	{
		$sn = $this->combineSns($sn);
		
		$cp_service = array();
		if (!empty($sn))
		{
			if (is_array($sn))
			{
				$sql = '';
				$s = array_shift($sn);
				while ($s)
				{
					$sql .= 'sn like ? or ';
					$param[] = '%'.substr($s,3);
					$s = array_shift($sn);
				}
				$sql = substr($sql, 0,-4);
				$this->model('operation_stat')->where($sql,$param);
			}
			else if(is_scalar($sn))
			{
				$this->model('operation_stat')->where('sn like ?',array('%'.substr($sn, 3)));
			}
		}
		//取出service累计最大的前9个分类
		$categoryTop = $this->model('operation_stat')->where('make_time>=? and make_time<?',array(
			$this->_starttime,
			$this->_endtime
		))
		->group(array('class','category'))
		->order('service_sum','desc')
		->limit($top)
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
			if (!empty($sn))
			{
				if (is_array($sn))
				{
					$sql = '';
					$s = array_shift($sn);
					while ($s)
					{
						$sql .= 'sn like ? or ';
						$param[] = '%'.substr($s,3);
						$s = array_shift($sn);
					}
					$sql = substr($sql, 0,-4);
					$this->model('operation_stat')->where($sql,$param);
				}
				else if(is_scalar($sn))
				{
					$this->model('operation_stat')->where('sn like ?',array('%'.substr($sn, 3)));
				}
			}
			$result = $this->model('operation_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->group('class,category')
			->select(array(
				'class',
				'category',
				'service_size' => 'sum(service_size)',
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
		
		$service = $this->ServiceMax($sn);
		$service = $service['detail'];
		
		foreach ($cp_service as $classname => &$v)
		{
			
			foreach ($v as $time => &$value)
			{
				$value = $service[$time] * division($value,$total_operation_stat[$time]);
			}
		}
		
		$max = array();
		foreach ($cp_service as $classname => $v_t)
		{
			$max[$classname] = max($v_t);
		}
		
		return array(
			'max' => $max,
			'detail' => $cp_service
		);
	}
	
	
	
	/**
	 * 网卡流速
	 * 计算最大Service和对应的cache
	 * @return number[][]|number[]|boolean[]
	 */
	public function traffic_stat($sn = array())
	{
		$key = md5($this->_starttime.$this->_endtime.$this->_duration.(is_array($sn)?implode(',', $sn):$sn));
		
		static $cacheContainer = array();
		if (isset($cacheContainer[$key]) && !empty($cacheContainer[$key]))
		{
			return $cacheContainer[$key];
		}
		
		$sn = $this->combineSns($sn);
		
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
				if (is_array($sn))
				{
					$traffic_stat_model->In('sn',$sn);
				}
				else if (is_scalar($sn))
				{
					$traffic_stat_model->where('sn=?',array($sn));
				}
			}
			$traffic_stat = $traffic_stat_model->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(create_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service) * 1024',
				'sum_cache' => 'sum(cache) * 1024',
				'sum_monitor' => 'sum(monitor) * 1024',
			));
			
			foreach ($traffic_stat as $r)
			{
				$temp_service[$r['time']] = $r['sum_service'];
				$temp_cache[$r['time']] = $r['sum_cache'];
				$temp_monitor[$r['time']] = $r['sum_monitor'];
			}
			
			$cdn_traffic_stat_model = $this->model('cdn_traffic_stat');
			if (!empty($sn))
			{
				if (is_scalar($sn))
				{
					$cdn_traffic_stat_model->where('sn like ?',array('%'.substr($sn, 3)));
				}
				else if (is_array($sn))
				{
					$where = '';
					$param = array();
					foreach ($sn as $s)
					{
						$where .= 'sn like ? or ';
						$param[] = '%'.substr($s, 3);
					}
					$where = substr($where,0, -4);
					$cdn_traffic_stat_model->where($where,$param);
				}
			}
			$cdn_traffic_stat = $cdn_traffic_stat_model
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service' => 'sum(service)',
				'sum_cache' => 'sum(cache)',
				'sum_monitor' => 'sum(monitor)',
			));
			
			foreach ($cdn_traffic_stat as $r)
			{
				if (isset($temp_service[$r['time']]))
				{
					$temp_service[$r['time']] += $r['sum_service'];
				}
				else
				{
					$temp_service[$r['time']] = $r['sum_service']*1;
				}
				
				if (isset($temp_cache[$r['time']]))
				{
					$temp_cache[$r['time']] += $r['sum_service'];
				}
				else
				{
					$temp_cache[$r['time']] = $r['sum_service']*1;
				}
				
				if (isset($temp_monitor[$r['time']]))
				{
					$temp_monitor[$r['time']] += $r['sum_monitor'];
				}
				else
				{
					$temp_monitor[$r['time']] = $r['sum_service']*1;
				}
			}
			
			$xvirt_traffic_stat_model = $this->model('xvirt_traffic_stat');
			if (!empty($sn))
			{
				if(is_scalar($sn))
				{
					$xvirt_traffic_stat_model->where('sn like ?',array('%'.substr($sn, 3)));
				}
				else
				{
					$where = '';
					$param = array();
					foreach ($sn as $s)
					{
						$where .= 'sn like ? or ';
						$param[] = '%'.substr($s, 3);
					}
					$where = substr($where,0, -4);
					$xvirt_traffic_stat_model->where($where,$param);
				}
			}
			//traffic_stat + cdn_traffic_stat - xvirt_traffic_stat
			$xvirt = $xvirt_traffic_stat_model->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache'=>'sum(cache)',
			));
			foreach ($xvirt as $r)
			{
				if (isset($temp_cache[$r['time']]) && $temp_cache[$r['time']] > $r['sum_cache'])
				{
					$temp_cache[$r['time']] -= $r['sum_cache'];
				}
				if (isset($temp_service[$r['time']]) && $temp_service[$r['time']] > $r['sum_service'])
				{
					$temp_service[$r['time']] -= $r['sum_service'];
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
		$cacheContainer[$key] = array(
			'service' => $service_max_detail,
			'cache' => $cache_max_detail,
			'monitor' => $monitor_max_detail
		);
		return $cacheContainer[$key];
	}
	
	/**
	 * 计算独立的服务流速和缓存流速
	 * service和cache互不依赖
	 */
	function traffic_stat_alone($sn = NULL)
	{
		$sn = $this->combineSns($sn);
		
		$service = array();
		$cache = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$temp_service = array();
			$temp_cache = array();
			
			if (!empty($sn))
			{
				if (is_array($sn))
				{
					$this->model('traffic_stat')->In('sn',$sn);
				}
				else if (is_scalar($sn))
				{
					$this->model('traffic_stat')->where('sn=?',array($sn));
				}
			}
			$traffic_stat = $this->model('traffic_stat')
			->where('create_time>=? and create_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(create_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service) * 1024',
				'sum_cache' => 'sum(cache) * 1024',
			));
				
			foreach ($traffic_stat as $stat)
			{
				if (isset($temp_service[$stat['time']]))
				{
					$temp_service[$stat['time']] += $stat['sum_service'];
				}
				else
				{
					$temp_service[$stat['time']] = $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['time']]))
				{
					$temp_cache[$stat['time']] += $stat['sum_cache'];
				}
				else
				{
					$temp_cache[$stat['time']] = $stat['sum_cache'];
				}
			}
				
			if (!empty($sn))
			{
				if (is_scalar($sn))
				{
					$this->model('cdn_traffic_stat')->where('sn like ?',array('%'.substr($sn, 3)));
				}
				else if (is_array($sn))
				{
					$where = '';
					$param = array();
					foreach ($sn as $s)
					{
						$where .= 'sn like ? or ';
						$param[] = '%'.substr($s, 3);
					}
					$where = substr($where,0, -4);
					$this->model('cdn_traffic_stat')->where($where,$param);
				}
			}
			$cdn_traffic_stat = $this->model('cdn_traffic_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service' => 'sum(service)',
				'sum_cache' => 'sum(cache)',
			));
			foreach ($cdn_traffic_stat as $stat)
			{
				if (isset($temp_service[$stat['time']]))
				{
					$temp_service[$stat['time']] += $stat['sum_service'];
				}
				else
				{
					$temp_service[$stat['time']] = $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['time']]))
				{
					$temp_cache[$stat['time']] += $stat['sum_cache'];
				}
				else
				{
					$temp_cache[$stat['time']] = $stat['sum_cache'];
				}
			}
			
			if (!empty($sn))
			{
				if(is_scalar($sn))
				{
					$this->model('xvirt_traffic_stat')->where('sn like ?',array('%'.substr($sn, 3)));
				}
				else
				{
					$where = '';
					$param = array();
					foreach ($sn as $s)
					{
						$where .= 'sn like ? or ';
						$param[] = '%'.substr($s, 3);
					}
					$where = substr($where,0, -4);
					$this->model('xvirt_traffic_stat')->where($where,$param);
				}
			}
			$xvirt = $this->model('xvirt_traffic_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			))
			->order('time','asc')
			->group('time')
			->select(array(
				'time'=>'DATE_FORMAT(make_time,"%Y-%m-%d %H:%i:00")',
				'sum_service'=>'sum(service)',
				'sum_cache'=>'sum(cache)',
			));
			foreach ($xvirt as $stat)
			{
				if (isset($temp_service[$stat['time']]))
				{
					$temp_service[$stat['time']] -= $stat['sum_service'];
				}
				if (isset($temp_cache[$stat['time']]))
				{
					$temp_cache[$stat['time']] -= $stat['sum_cache'];
				}
			}

			$service[$t_time] = empty($temp_service)?0:max($temp_service);
			$cache[$t_time] = empty($temp_cache)?0:max($temp_cache);
		}
		return array('service' => $service,'cache' => $cache);
	}
	
	/**
	 * 获取流量
	 * @return number
	 */
	function operation_stat($sn = array())
	{
		$key = md5($this->_starttime.$this->_endtime.$this->_duration.(is_array($sn)?implode(',', $sn):$sn));
		
		static $cacheContainer = array();
		if (isset($cacheContainer[$key]) && !empty($cacheContainer[$key]))
		{
			return $cacheContainer[$key];
		}
		
		$sn = $this->combineSns($sn);
		
		$operation_stat = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			if (!empty($sn))
			{
				if (is_array($sn))
				{
					$sql = '';
					while ($s = array_shift($sn))
					{
						$sql .= 'sn like ? or ';
						$param[] = '%'.substr($s,3);
					}
					$sql = substr($sql, 0,-4);
					$this->model('operation_stat')->where($sql,$param);
				}
				else if(is_scalar($sn))
				{
					$this->model('operation_stat')->where('sn like ?',array('%'.substr($sn, 3)));
				}
			}
			$result = $this->model('operation_stat')
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration),
			))
			->find(array(
				'sum_service'=>'sum(service_size)',
				'sum_cache'=>'sum(cache_size+proxy_cache_size)'
			));
			$operation_stat['service'][$t_time] = $result['sum_service']*1;
			$operation_stat['cache'][$t_time] = $result['sum_cache']*1;
		}
		$cacheContainer[$key] = $operation_stat;
		return $cacheContainer[$key];
	}
}