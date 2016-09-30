<?php
namespace application\algorithm;

use framework\core\component;

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
	function __construct($starttime = '',$endtime = '',$duration = 5*60)
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
			$cds_detail[$t_time] = 1 * $this->model('feedbackHistory')->where('ctime >= ? and ctime < ?',array(date('Y-m-d H:i:s',strtotime($t_time)-30*60),$t_time))->scalar('count(distinct(sn))');
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
	public function USEROnlineNum()
	{
		$user_max = 0;
		$user_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			$max_online_gourp_sn = $this->model('feedbackHistory')
			->group('sn')
			->where('ctime >= ? and ctime<?',array(
				date('Y-m-d H:i:s',strtotime($t_time)-30*60),
				$t_time
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
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			//先把所有的sn的service都累加起来  然后计算其中最大的 那个
			$sql = 'select max(sum_service) as max_service from (select create_time,sum(service) as sum_service from traffic_stat where create_time >=? and create_time<? group by create_time) as t';
			$result = $this->model('traffic_stat')
			->query($sql,array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			));
				
			$service_max_detail[$t_time] = isset($result[0]['max_service'])?$result[0]['max_service']*1:0;
			
			$sql = 'select max(sum_service) as max_service from (select sum(service) as sum_service from cdn_traffic_stat where make_time>=? and make_time<? GROUP by make_time) as t';
			$result = $this->model('cdn_traffic_stat')->query($sql,array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			));
			
			$service_max_detail[$t_time] += isset($result[0]['max_service'])?$result[0]['max_service']*1:0;
			
			if ($service_max_detail[$t_time] > $service_max_max)
			{
				$service_max_max = $service_max_detail[$t_time];
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
		->group('category')
		->order('service_sum','desc')
		->limit(9)
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
	
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			//var_dump(date('Y-m-d H:i:s',strtotime($t_time) + $this->_duration));
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
			
			foreach ($categoryTop as $r)
			{
				$classname = $this->getCategoryName($r);
				$cp_service[$classname][$t_time] = 0;
			}
			$cp_service['其他'][$t_time] = 0;
			
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
				
				$cp_service[$classname][$t_time] += $r['service_size'];
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
	public function traffic_stat()
	{
		$cache_max_detail = array();
		$service_max_detail = array();
		for($t_time = $this->_starttime;strtotime($t_time)<strtotime($this->_endtime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration))
		{
			//先把所有的sn的service都累加起来  然后计算其中最大的 那个
			$sql = 'select max(concat(lpad(sum_service,20,0),"-",lpad(sum_cache,20,0))) as l from (select sum(service) as sum_service,sum(cache) as sum_cache from traffic_stat where create_time >=? and create_time<? group by create_time) as t';
			$result = $this->model('traffic_stat')
			->query($sql,array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			));
			
			if(isset($result[0]['l']))
			{
				list($service,$cache) = explode('-', $result[0]['l']);
				$service_max_detail[$t_time] = $service*1;
				$cache_max_detail[$t_time] = $cache*1;
			}
			else
			{
				$service_max_detail[$t_time] = 0;
				$cache_max_detail[$t_time] = 0;
			}
			
			$sql = 'select max(concat(lpad(sum_service,20,0),"-",lpad(sum_cache,20,0))) as l from (select sum(service) as sum_service,sum(cache) as sum_cache from cdn_traffic_stat where make_time>=? and make_time<? GROUP by make_time) as t';
			$result = $this->model('cdn_traffic_stat')->query($sql,array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration)
			));
			
			if (isset($result[0]['l']))
			{
				list($service,$cache) = explode('-', $result[0]['l']);
				$service_max_detail[$t_time] += $service*1;
				$cache_max_detail[$t_time] += $cache*1;
			}
		}
		return array(
			'service' => $service_max_detail,
			'cache' => $cache_max_detail,
		);
	}
}