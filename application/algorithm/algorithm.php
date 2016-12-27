<?php
namespace application\algorithm;

use framework\core\model;
use application\extend\BaseComponent;
use framework\core\database\sql;
use application;

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
		
		switch ($this->_duration)
		{
			case 30*60:
				$time = 'if( date_format(ctime,"%i")<30,date_format(ctime,"%Y-%m-%d %H:00:00"),date_format(ctime,"%Y-%m-%d %H:30:00") )';
			break;
			case 60*60:
				$time = 'date_format(ctime,"%Y-%m-%d %H:00:00")';
			break;
			case 2*60*60:
				$time = 'concat(date_format(ctime,"%Y-%m-%d")," ",floor(date_format(ctime,"%H")/2)*2,":00:00")';
			break;
			case 24*60*60:
				$time = 'date_format(ctime,"%Y-%m-%d 00:00:00")';
			break;
			default:
				$time = '';
		}
		
		
		if (!empty($time))
		{
			$result = $this->model('feedbackHistory')
			->where('ctime >= ? and ctime < ?',array(
				$this->_starttime,$this->_endtime
			))
			->group('time')
			->order('time','asc')
			->in('sn',$sn)
			->select(array(
				'time' => $time,
				'count' => 'count(distinct(sn))'
			));
			foreach ($result as $r)
			{
				$cds_detail[$r['time']] = $r['count'];
			}
			$cds_max = empty($cds_detail)?0:max($cds_detail);
		}
		else
		{
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
		}
		
		return array(
			'max' => $cds_max,
			'detail' => $cds_detail,
		);
	}
	
	/**
	 * 在线用户和服务用户数量
	 */
	public function user($sn = array())
	{
		if (empty($sn))
		{
			$temp = array();
			$tableName = 'user_online_'.$this->_duration;
			$result = $this->model($tableName)->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->order('time','asc')
			->select();
			foreach ($result as $r)
			{
				$temp[$r['time']] = array(
					'online' => $r['online'],
					'hit' => $r['hit'],
				);
			}
			return $temp;
		}
		else if ((is_array($sn) && count($sn) == 1) || is_scalar($sn))
		{
			if (is_array($sn))
			{
				$sn = substr(array_shift($sn), 3);
			}
			else
			{
				$sn = substr($sn, 3);
			}
			$temp = array();
			$tableName = 'user_online_sn_'.$this->_duration;
			$result = $this->model($tableName)->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->order('time','asc')
			->where('sn=?',array($sn))
			->select(array(
				'time',
				'online',
				'hit'
			));
			foreach ($result as $r)
			{
				$temp[$r['time']] = array(
					'online' => $r['online'],
					'hit' => $r['hit'],
				);
			}
			return $temp;
		}
		else 
		{
			$sn = $this->combineSns($sn);
			for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
			{
				$max_online_gourp_sn = $this->model('traffic_stat')
				->in('sn',$sn)
				->where('create_time>=? and create_time<?',array(
					$t_time,
					date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration),
				))
				->group('sn')
				->select('sn,max(online_user) as online,max(hit_user) as hit');

				$user_detail[$t_time] = array(
					'online' => 0,
					'hit' => 0,
				);
				foreach ($max_online_gourp_sn as $online)
				{
					$user_detail[$t_time]['online'] += $online['online'];
					$user_detail[$t_time]['hit'] += $online['hit'];
				}
			}
			return $user_detail;
		}
	}
	
	/**
	 * 在线用户数量
	 */
	public function USEROnlineNum($sn = array())
	{
		$user_detail = array();
		$user = $this->user($sn);
		foreach ($user as $time => $stat)
		{
			$user_detail[$time] = $stat['online'];
		}
		return array(
			'max' => empty($user_detail)?0:max($user_detail),
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
		$service_max_max = empty($service_max_detail)?0:max($service_max_detail);
		
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
		if (empty($sn))
		{
			$tableName = 'operation_stat_'.$this->_duration;
			$service_sum_sum = $this->model($tableName)
			->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->sum('service_size');
			return array(
				'max' => $service_sum_sum,
				'detail' => array(),
			);
		}
		
		$sn = $this->combineSns($sn);
		$sn = array_map(function($s){
			return '%'.substr($s, 4);
		}, $sn);
		
		$service_sum_sum = 1*$this->model('operation_stat')
		->likein('sn',$sn)
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
		$cp_service = array();
		$tableName = 'operation_stat_class_category_'.$this->_duration;
		//取出service累计最大的前n个分类
		$categoryTop = $this->model($tableName)->where('time>=? and time<?',array(
			$this->_starttime,
			$this->_endtime
		))
		->group(array('class','category'))
		->order('service_sum','desc')
		->limit($top)
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
		
		$tableName = 'operation_stat_class_category_'.$this->_duration;
		$result = $this->model($tableName)->where('time>=? and time<?',array(
			$this->_starttime,$this->_endtime
		))
		->select();
		foreach ($result as $r)
		{
			if (in_array(array(
				'category' => $r['category'],
				'class' => $r['class'],
			), $top))
			{
				$classname = $this->getCategoryName($r);
				$cp_service[$classname][$r['time']] = $r['service_size'];
			}
			if (isset($total_operation_stat[$r['time']]))
			{
				$total_operation_stat[$r['time']] += $r['service_size'];
			}
			else
			{
				$total_operation_stat[$r['time']] = $r['service_size'];
			}
		}
		
		reset($sn);
		$service = $this->ServiceMax($sn);
		$service = $service['detail'];

		foreach ($cp_service as $classname => &$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = (isset($service[$time])?$service[$time]:0) * division($value,$total_operation_stat[$time]);
			}
		}
		
		return array(
			'max' => 0,
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
		if (empty($sn))
		{
			$tableName = 'traffic_stat_'.$this->_duration;
			$traffic_stat = $this->model($tableName)
			->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->select();
			$data = array(
				'service' => array(),
				'cache' => array(),
				'monitor' => array(),
				'max_cache' => array(),
				'icache_cache' => array(),
				'vpe_cache' => array(),
			);
			foreach ($traffic_stat as $stat)
			{
				$data['service'][$stat['time']] = $stat['service'];
				$data['cache'][$stat['time']] = $stat['cache'];
				$data['monitor'][$stat['time']] = $stat['monitor'];
				$data['max_cache'][$stat['time']] = $stat['max_cache'];
				$data['icache_cache'][$stat['time']] = $stat['icache_cache'];
				$data['vpe_cache'][$stat['time']] = $stat['vpe_cache'];
			}
			$data['service'] = $this->formatTimenode($data['service'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['cache'] = $this->formatTimenode($data['cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['monitor'] = $this->formatTimenode($data['monitor'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['max_cache'] = $this->formatTimenode($data['max_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['icache_cache'] = $this->formatTimenode($data['icache_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['vpe_cache'] = $this->formatTimenode($data['vpe_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			return $data;
		}
		else if (is_scalar($sn) || (is_array($sn) && count($sn)==1))
		{
			if (is_array($sn))
			{
				$sn = substr(array_shift($sn),3);
			}
			else
			{
				$sn = substr($sn, 3);
			}
			
			$tableName = 'traffic_stat_sn_'.$this->_duration;
			$traffic_stat = $this->model($tableName)
			->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->where('sn=?',array($sn))
			->select();
			$data = array(
				'service' => array(),
				'cache' => array(),
				'monitor' => array(),
				'max_cache' => array(),
				'icache_cache' => array(),
				'vpe_cache' => array(),
			);
			foreach ($traffic_stat as $stat)
			{
				$data['service'][$stat['time']] = $stat['service']*1;
				$data['cache'][$stat['time']] = $stat['cache']*1;
				$data['monitor'][$stat['time']] = $stat['monitor']*1;
				$data['max_cache'][$stat['time']] = $stat['max_cache']*1;
				$data['icache_cache'][$stat['time']] = $stat['icache_cache']*1;
				$data['vpe_cache'][$stat['time']] = $stat['vpe_cache']*1;
			}
			$data['service'] = $this->formatTimenode($data['service'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['cache'] = $this->formatTimenode($data['cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['monitor'] = $this->formatTimenode($data['monitor'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['max_cache'] = $this->formatTimenode($data['max_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['icache_cache'] = $this->formatTimenode($data['icache_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			$data['vpe_cache'] = $this->formatTimenode($data['vpe_cache'], $this->_starttime, $this->_endtime, $this->_duration);
			return $data;
		}
		
		$sn = $this->combineSns($sn);
		
		switch ($this->_duration)
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
				$time = '';
		}
		
		$cache_max_detail = array();
		$service_max_detail = array();
		$monitor_max_detail = array();
		$max_cache_detail = array();
		$icache_cache_detail = array();
		$vpe_cache_detail = array();
		
		if (!empty($time))
		{
			$traffic_stat = new sql();
			$cdn_traffic_stat = new sql();
			$xvirt_traffic_stat = new sql();
				
			$xvirt_traffic_stat->from('cds_v2.xvirt_traffic_stat')
			->in('sn',$sn)
			->where('make_time>=? and make_time<?',array(
				$this->_starttime,$this->_endtime
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
				$this->_starttime,$this->_endtime
			))
			->select(array(
				'time'=>'date_format(create_time,"%Y-%m-%d %H:%i")',
				'service'=>'1024*service',
				'cache' => '1024*cache',
				'monitor'=>'1024*monitor',
				'icache_cache' => '1024*cache',
				'vpe_cache' => 0,
			));
				
			$sn = array_map(function($s){
				return '%'.substr($s, 3);
			}, $sn);
			$cdn_traffic_stat->from('cds_v2.cdn_traffic_stat')
			->likein('sn',$sn)
			->where('make_time>=? and make_time<?',array(
				$this->_starttime,$this->_endtime
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
		}
		else
		{
			for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
			{
				$temp_service = array();
				$temp_cache = array();
				$temp_monitor = array();
				$icache_cache = array();
				$vpe_cache = array();
				
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
					$icache_cache[$r['time']] = $r['sum_cache'];
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
						$temp_cache[$r['time']] += $r['sum_cache'];
					}
					else
					{
						$temp_cache[$r['time']] = $r['sum_cache']*1;
					}
					
					if (isset($temp_monitor[$r['time']]))
					{
						$temp_monitor[$r['time']] += $r['sum_monitor'];
					}
					else
					{
						$temp_monitor[$r['time']] = $r['sum_monitor']*1;
					}
					
					$vpe_cache[$r['time']] = $r['sum_cache'];
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
					if (isset($temp_service[$r['time']]))
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
					$cache_max_detail[$t_time] = isset($temp_cache[$max_time])?$temp_cache[$max_time]:0;
					$monitor_max_detail[$t_time] = isset($temp_monitor[$max_time])?$temp_monitor[$max_time]:0;
					$icache_cache_detail[$t_time] = isset($icache_cache[$max_time])?$icache_cache[$max_time]:0;
					$vpe_cache_detail[$t_time] = isset($vpe_cache[$max_time])?$vpe_cache[$max_time]:0;
				}
				else
				{
					$cache_max_detail[$t_time] = 0;
					$monitor_max_detail[$t_time] = 0;
					$icache_cache_detail[$t_time] = 0;
					$vpe_cache_detail[$t_time] = 0;
				}
				$max_cache_detail[$t_time] = empty($temp_cache)?0:max($temp_cache);
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
	 * 计算独立的服务流速和缓存流速
	 * service和cache互不依赖
	 */
	function traffic_stat_alone($sn = NULL)
	{
		$traffic_stat = $this->traffic_stat($sn);
		return array(
			'service' => $traffic_stat['service'],
			'cache' => $traffic_stat['max_cache'],
		);
	}
	
	/**
	 * 计算服务，缓存回源，代理缓存回源流速
	 */
	function traffic_stat_service_cache_proxy($sn = array())
	{
		$traffic_stat = $this->traffic_stat($sn);
		return array(
			'service' => $traffic_stat['service'],
			'cache' => $traffic_stat['icache_cache'],
			'proxy' => $traffic_stat['vpe_cache'],
		);
	}
	
	/**
	 * 分时间段，累计流量
	 * @return number
	 */
	function operation_stat($sn = array())
	{
		if (empty($sn))
		{
			$operation_stat = array(
				'service' => array(),
				'cache' => array(),
			);
			$tableName = 'operation_stat_'.$this->_duration;
			$result = $this->model($tableName)->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->select();
			foreach ($result as $r)
			{
				$operation_stat['service'][$r['time']] = $r['service_size'] * 1;
				$operation_stat['cache'][$r['time']] = $r['cache_size'] + $r['proxy_cache_size'];
			}
			$operation_stat['service'] = $this->formatTimenode($operation_stat['service'], $this->_starttime, $this->_endtime, $this->_duration);
			$operation_stat['cache'] = $this->formatTimenode($operation_stat['cache'], $this->_starttime, $this->_endtime, $this->_duration);
			return $operation_stat;
		}
		else if ((is_array($sn) && count($sn) == 1) || is_scalar($sn))
		{
			if (is_array($sn))
			{
				$sn = substr(array_shift($sn),3);
			}
			else
			{
				$sn = substr($sn, 3);
			}
			$tableName = 'operation_stat_sn_'.$this->_duration;
			$result = $this->model($tableName)->where('time>=? and time<?',array(
				$this->_starttime,$this->_endtime
			))
			->where('sn=?',array(substr($sn, 3)))
			->select(array(
				'time',
				'service_size',
				'cache_size',
				'proxy_cache_size',
			));
			$operation_stat = array(
				'service' => array(),
				'cache' => array(),
			);
			foreach ($result as $r)
			{
				if (!isset($operation_stat['service'][$r['time']]))
				{
					$operation_stat['service'][$r['time']] = 0;
				}
				$operation_stat['service'][$r['time']] += $r['service_size'];
				if (!isset($operation_stat['cache'][$r['time']]))
				{
					$operation_stat['cache'][$r['time']] = 0;
				}
				$operation_stat['cache'][$r['time']] += $r['cache_size'];
				$operation_stat['cache'][$r['time']] += $r['proxy_cache_size'];
			}
			return $operation_stat;
		}
		else
		{
			$sn = $this->combineSns($sn);
			$sn = array_map(function($s){
				return '%'.substr($s,3);
			}, $sn);
			$operation_stat = array(
				'service' => array(),
				'cache' => array(),
			);
			for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
			{
				$result = $this->model('operation_stat')
				->likein('sn',$sn)
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
			return $operation_stat;
		}
	}
}