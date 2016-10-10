<?php
namespace application\control;
use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\model;
use application\algorithm\algorithm;

/**
 * 内容交付相关接口
 * @author fx
 *
 */
class content extends BaseControl
{
	function initlize()
	{
		parent::initlize();
		return $this->setTime();
	}
	
	/**
	 * 概览
	 */
	function overview()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$category_service = array();
		$category_cache = array();
		$flow = array(
			'total' => array('service'=>0,'cache'=>0),
			'http' => array('service'=>0,'cache'=>0),
			'mobile' => array('service'=>0,'cache'=>0),
			'videoLive' => array('service'=>0,'cache'=>0),
			'videoDemand' => array('service'=>0,'cache'=>0),
		);
		
		$total_operation_stat_service = array();
		$total_operation_stat_cache = array();
		
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$total_operation_stat_cache[$t_time] = 0;
			$total_operation_stat_service[$t_time] = 0;
			
			$result = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->select(array(
				'service_size',
				'cache_size',
				'class',
				'category',
			));
			
			$category_cache['http'][$t_time] = 0;
			$category_cache['mobile'][$t_time] = 0;
			$category_cache['videoLive'][$t_time] = 0;
			$category_cache['videoDemand'][$t_time] = 0;
			$category_service['http'][$t_time] = 0;
			$category_service['mobile'][$t_time] = 0;
			$category_service['videoLive'][$t_time] = 0;
			$category_service['videoDemand'][$t_time] = 0;
			
			foreach ($result as $r)
			{
				switch ($r['class'])
				{
					case '0':$classname = 'http';break;
					case '1':$classname = 'mobile';break;
					case '2':
						if ($r['category']>=128)
						{
							$classname = 'videoLive';
						}
						else
						{
							$classname = 'videoDemand';
						}
					break;
				}
				
				$total_operation_stat_cache[$t_time] += $r['cache_size'];
				$total_operation_stat_service[$t_time] += $r['service_size'];
				
				$category_service[$classname][$t_time] += $r['service_size'] * 1;
				$category_cache[$classname][$t_time] += $r['cache_size'] * 1;
				
				$flow[$classname]['service'] += $r['service_size'] * 1;
				$flow[$classname]['cache'] += $r['cache_size']*1;
				$flow['total']['service'] += $r['service_size']*1;
				$flow['total']['cache'] += $r['cache_size']*1;
			}
		}
		
		//获取traffic_stat  做占比
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		$traffic_stat = $algorithm->traffic_stat();
		$service = $traffic_stat['service'];
		$cache = $traffic_stat['cache'];
		
		foreach ($category_service as $classname => &$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = $service[$time] * division($value, $total_operation_stat_service[$time]);
			}
		}
		
		foreach ($category_cache as $classname => &$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = $cache[$time] * division($value, $total_operation_stat_cache[$time]);
			}
		}
		
		$topfile = array();
		if ($this->_duration_second >= 3600)
		{
			$topModel = $this->model('top_stat_hour');
		}
		else
		{
			$topModel = $this->model('top_stat');
		}
		
		$topfile['http'] = $topModel
		->where('create_time >= ? and create_time<?',array(
			$start_time,
			$end_time
		))
		->group(array('hash'))
		->order('sum_service','desc')
		->limit(10)
		->where('class=?',array(0))
		->select(array(
			'max(cache_size) as cache_size',
			'sum(service_size) as sum_service',
			'host',
			'filename',
			'category',
		));
		$category = $this->getConfig('category');
		foreach ($topfile['http'] as &$file)
		{
			$file['category'] = isset($category['http'][$file['category']])?$category['http'][$file['category']]:'其他';
		}
		
		$topfile['mobile'] = $topModel
		->where('create_time >= ? and create_time<?',array(
			$start_time,
			$end_time
		))
		->group(array('hash'))
		->order('sum_service','desc')
		->limit(10)
		->where('class=?',array(1))
		->select(array(
			'max(cache_size) as cache_size',
			'sum(service_size) as sum_service',
			'host',
			'filename',
			'category'
		));
		foreach ($topfile['mobile'] as &$file)
		{
			$file['category'] = isset($category['mobile'][$file['category']])?$category['mobile'][$file['category']]:'其他';
		}
		
		$topfile['videoDemand'] = $topModel
		->where('create_time >= ? and create_time<?',array(
			$start_time,
			$end_time
		))
		->group(array('hash'))
		->order('sum_service','desc')
		->limit(10)
		->where('class=? and category<?',array(2,128))
		->select(array(
			'max(cache_size) as cache_size',
			'sum(service_size) as sum_service',
			'host',
			'filename',
		));
		
		//直播的话文件大小按照累加的cache_size ,其他资源全部取max(cache_size)
		$topfile['videoLive'] = $topModel
		->where('create_time >= ? and create_time<?',array(
			$start_time,
			$end_time
		))
		->group(array('hash'))
		->order('sum_service','desc')
		->limit(10)
		->where('class=? and category>=?',array(2,128))
		->select(array(
			'sum(cache_size) as cache_size',
			'sum(service_size) as sum_service',
			'host',
			'filename',
		));
		
		$data = array(
			//分类型服务流速堆叠
			'category_service' => $category_service,
			//分类型回源流速堆叠
			'category_cache' => $category_cache,
			//流量对比
			'flow' => $flow,
			//资源热榜
			'topfile' => $topfile
		);
		return new json(json::OK,NULL,$data);
	}
	
	/**
	 * 视频点播
	 */
	function videoDemand()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$category = $this->getConfig('category');
		
		//变量声明
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			),
			'其他' => array(
				'service' => 0,
				'cache' => 0,
			)
		);
		foreach ($category['videoDemand'] as $key=>$name)
		{
			$cp_cache_service_sum[$name]=array(
				'service' => 0,
				'cache' => 0,
			);
		}
		
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$result = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->where('class=? and category<?',array(2,128))
			->select(array(
				'category',
				'service_size',
				'cache_size'
			));
			
			foreach ($category['videoDemand'] as $key=>$name)
			{
				$cp_service_flow[$name][$t_time] = 0;
				$cp_cache_flow[$name][$t_time] = 0;
			}
			
			foreach ($result as $r)
			{
				if(isset($category['videoDemand'][$r['category']]))
				{
					$categoryname = $category['videoDemand'][$r['category']];
				}
				else
				{
					$categoryname = '其他';
				}
				
				$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
		}
		
		uasort($cp_cache_service_sum, function($a,$b){
			if ($a['service'] + $a['cache'] > $b['service'] + $b['cache'])
			{
				return -1;
			}
			else if ($a['service'] + $a['cache'] < $b['service'] + $b['cache'])
			{
				return 1;
			}
			return 0;
		});
		
		$i = 0;
		
		$top5_category = array();
		foreach ($cp_cache_service_sum as $categoryname =>$service_cache)
		{
			if ($i>=5 && $categoryname !== '其他' && $categoryname!=='总流量')
			{
				$cp_cache_service_sum['其他']['service'] += $service_cache['service'];
				$cp_cache_service_sum['其他']['cache'] += $service_cache['cache'];
				unset($cp_cache_service_sum[$categoryname]);
			}
			else if ($categoryname !=='其他' && $categoryname!=='总流量')
			{
				$top5_category[] = $categoryname;
				$i++;
			}
		}
		
		//去掉不是top5的分类，并把他们聚合为其他分类
		foreach ($cp_service_flow as $cate => $v)
		{
			if (!in_array($cate, $top5_category,true) && $cate!=='其他')
			{
				foreach ($v as $timenode=>$value)
				{
					if (isset($cp_service_flow['其他'][$timenode]))
					{
						$cp_service_flow['其他'][$timenode] += $value;
					}
					else
					{
						$cp_service_flow['其他'][$timenode] = $value;
					}
				}
				unset($cp_service_flow[$cate]);
			}
		}
		
		//去掉不是top5的分类，并把它们聚合为其他分类
		foreach ($cp_cache_flow as $cate => $v)
		{
			if (!in_array($cate, $top5_category,true) && $cate!=='其他')
			{
				foreach ($v as $timenode=>$value)
				{
					if (isset($cp_cache_flow['其他'][$timenode]))
					{
						$cp_cache_flow['其他'][$timenode] += $value;
					}
					else
					{
						$cp_cache_flow['其他'][$timenode] = 0;
					}
				}
				unset($cp_cache_flow[$cate]);
			}
		}
		
		//进行占比计算
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		$traffic_stat = $algorithm->traffic_stat();
		$operation_stat = $algorithm->operation_stat();	
		foreach ($cp_cache_flow as $classname => &$v)
		{
			foreach ($v as $time=>&$value)
			{
				$value = division($value, $operation_stat['cache'][$time]) * $traffic_stat['cache'][$time];
			}
		}
		foreach ($cp_service_flow as $classname => &$v)
		{
			foreach ($v as $time=>&$value)
			{
				$value = division($value, $operation_stat['service'][$time]) * $traffic_stat['service'][$time];
			}
		}
		
		$topfile = array();
		$other_key = array();
		if ($this->_duration_second >= 3600)
		{
			$topModel = $this->model('top_stat_hour');
		}
		else
		{
			$topModel = $this->model('top_stat');
		}
		
		foreach ($category['videoDemand'] as $key=>$name)
		{
			if (in_array($name, $top5_category))
			{
				$topfile[$name] = $topModel
				->where('create_time>=? and create_time<?',array(
					$start_time,
					$end_time,
				))
				->where('class=? and category=?',array(2,$key))
				->group('hash')
				->order('sum_service','desc')
				->limit(10)
				->select(array(
					'host',
					'filename',
					'cache_size as sum_cache',
					'sum(service_size) as sum_service',
				));
				$other_key[] = $key;
			}
		}
		
		$topfile['其他'] = $topModel
		->where('create_time>=? and create_time<?',array(
			$start_time,
			$end_time,
		))
		->where('class=?',array(2))
		->notIn('category',$other_key)
		->group('hash')
		->order('sum_service','desc')
		->limit(10)
		->select(array(
			'host',
			'filename',
			'cache_size as sum_cache',
			'sum(service_size) as sum_service',
		));
		
		$data = array(
			//分CP服务流速堆叠
			'cp_service_flow' => $cp_service_flow,
			//分CP缓存服务堆叠
			'cp_cache_flow' => $cp_cache_flow,
			//流量对比
			'cp_cache_service_sum' => $cp_cache_service_sum,
			//资源热榜
			'topfile' => $topfile,
		);
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 视频直播
	 */
	function videoLive()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$category = $this->getConfig('category');
		
		//变量声明
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			),
			'其他'=>array(
				'service' => 0,
				'cache' => 0,
			)
		);
		
		foreach ($category['videoLive'] as $key => $name)
		{
			$cp_cache_service_sum[$name] = array(
				'service' => 0,
				'cache' => 0,
			);
		}
		
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$result = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->where('class=? and category>=?',array(2,128))
			->select(array(
				'category'=>'category - 128',
				'service_size',
				'cache_size'
			));
			
			
			//变量初始化
			$cp_service_flow['其他'][$t_time] = 0;
			$cp_cache_flow['其他'][$t_time] = 0;
			foreach ($category['videoLive'] as $key => $name)
			{
				$cp_service_flow[$name][$t_time] = 0;
				$cp_cache_flow[$name][$t_time] = 0;
			}
			
			
			foreach ($result as $r)
			{
				if (isset($category['videoLive'][$r['category']]))
				{
					$categoryname = $category['videoLive'][$r['category']];
				}
				else
				{
					$categoryname = '其他';
				}
				
				
				$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
		}
		
		//排序
		uasort($cp_cache_service_sum, function($a,$b){
			if ($a['service'] + $a['cache'] > $b['service'] + $b['cache'])
			{
				return -1;
			}
			else if ($a['service'] + $a['cache'] < $b['service'] + $b['cache'])
			{
				return 1;
			}
			return 0;
		});
		
		//聚合分类 保留前5
		$i = 0;
		$top5_category = array();
		foreach ($cp_cache_service_sum as $categoryname => $service_cache)
		{
			if ($categoryname !== '总流量' && $categoryname!=='其他' && $i>=5)
			{
				$cp_cache_service_sum['其他']['service'] += $cp_cache_service_sum[$categoryname]['service'];
				$cp_cache_service_sum['其他']['cache'] += $cp_cache_service_sum[$categoryname]['cache'];
				unset($cp_cache_service_sum[$categoryname]);
			}
			else if ($categoryname !=='其他' && $categoryname!=='总流量')
			{
				$top5_category[] = $categoryname;
				$i++;
			}
		}
		
		foreach ($cp_cache_flow as $cate => $v)
		{
			if (!in_array($cate, $top5_category,true) && $cate !== '其他')
			{
				foreach ($v as $timenode=>$value)
				{
					$cp_cache_flow['其他'][$timenode] += $value;
				}
				unset($cp_cache_flow[$cate]);
			}
		}
		
		foreach ($cp_service_flow as $cate => $v)
		{
			if (!in_array($cate, $top5_category,true) && $cate !== '其他')
			{
				foreach ($v as $timenode=>$value)
				{
					$cp_service_flow['其他'][$timenode] += $value;
				}
				unset($cp_service_flow[$cate]);
			}
		}
		
		
		//占比计算
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		$traffic_stat = $algorithm->traffic_stat();
		$operation_stat = $algorithm->operation_stat();
		foreach ($cp_cache_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['cache'][$time]) * $traffic_stat['cache'][$time];
			}
		}
		foreach ($cp_service_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['service'][$time]) * $traffic_stat['service'][$time];
			}
		}
		

		$topfile = array();
		if ($this->_duration_second >= 3600)
		{
			$topModel = $this->model('top_stat_hour');
		}
		else
		{
			$topModel = $this->model('top_stat');
		}
		$selected_key = array();
		foreach ($category['videoLive'] as $key=>$name)
		{
			$topfile[$name] = $topModel
			->where('create_time>=? and create_time<?',array(
				$start_time,
				$end_time,
			))
			->where('class=? and category=?',array(2,$key + 128))
			->group('hash')
			->order('sum_service','desc')
			->limit(10)
			->select(array(
				'host',
				'filename',
				'sum(cache_size) as sum_cache',
				'sum(service_size) as sum_service',
			));
			
			$selected_key[] = $key+128;
		}
		
		$topfile['其他'] = $topModel
		->where('create_time>=? and create_time<?',array(
			$start_time,
			$end_time,
		))
		->where('class=? and category>?',array(2,128))
		->notIn('category',$selected_key)
		->group('hash')
		->order('sum_service','desc')
		->limit(10)
		->select(array(
			'host',
			'filename',
			'sum(cache_size) as sum_cache',
			'sum(service_size) as sum_service',
		));
		
		$data = array(
			//分CP服务流速堆叠
			'cp_service_flow' => $cp_service_flow,
			//分CP缓存服务堆叠
			'cp_cache_flow' => $cp_cache_flow,
			//流量对比
			'cp_cache_service_sum' => $cp_cache_service_sum,
			//资源热榜
			'topfile' => $topfile,
		);
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 移动应用
	 */
	function mobile()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'Android' => array(
				'service' => 0,
				'cache' => 0,
			),
			'IOS' => array(
				'service' => 0,
				'cache' => 0,
			),
			'WP' => array(
				'service' => 0,
				'cache' => 0,
			),
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			)
		);
		
		$category = $this->getConfig('category');
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			$result = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->where('class=?',array(1))
			->select(array(
				'category',
				'service_size',
				'cache_size'
			));
				
			foreach ($result as $r)
			{
				$categoryname = $category['mobile'][$r['category']];
		
				if (isset($cp_service_flow[$categoryname][$t_time]))
				{
					$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				}
				else
				{
					$cp_service_flow[$categoryname][$t_time] = $r['service_size'] * 1;
				}
		
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = $r['service_size'] * 1;
				}
		
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
		}
		
		//占比计算
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		$traffic_stat = $algorithm->traffic_stat();
		$operation_stat = $algorithm->operation_stat();
		foreach ($cp_cache_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['cache'][$time]) * $traffic_stat['cache'][$time];
			}
		}
		foreach ($cp_service_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['service'][$time]) * $traffic_stat['service'][$time];
			}
		}
		
		$topfile = array();
		foreach ($category['mobile'] as $key=>$name)
		{
			$topfile[$name] = $this->model('top_stat')
			->where('create_time>=? and create_time<?',array(
				$start_time,
				$end_time,
			))
			->where('class=? and category=?',array(1,$key))
			->group('hash')
			->order('sum_service','desc')
			->limit(10)
			->select(array(
				'host',
				'filename',
				'cache_size',
				'sum(service_size) as sum_service',
			));
		}
		
		$data = array(
			//分CP服务流速堆叠
			'cp_service_flow' => $cp_service_flow,
			//分CP缓存服务堆叠
			'cp_cache_flow' => $cp_cache_flow,
			//流量对比
			'cp_cache_service_sum' => $cp_cache_service_sum,
			//资源热榜
			'topfile' => $topfile
		);
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 常规资源
	 */
	function http()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'未归类' => array(
				'service' => 0,
				'cache' => 0,
			),
			'软件升级' => array(
				'service' => 0,
				'cache' => 0,
			),
			'压缩文档' => array(
				'service' => 0,
				'cache' => 0,
			),
			'文档管理' => array(
				'service' => 0,
				'cache' => 0,
			),
			'图片' => array(
				'service' => 0,
				'cache' => 0,
			),
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			)
		);
		
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		//计算网卡总流速
		$traffic_stat = $algorithm->traffic_stat();
		
		$category = $this->getConfig('category');
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
			//当前时间段的业务总流量
			$operation_stat = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->find(array(
				'sum_service'=>'sum(service_size)',
				'sum_cache'=>'sum(cache_size)'
			));
			
			//计算当前时间段内各个类型的业务流量
			$result = $this->model('operation_stat')
			->where('make_time >=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second)
			))
			->where('class=?',array(0))
			->select(array(
				'category',
				'service_size',
				'cache_size'
			));
			
			foreach ($result as $r)
			{
				$categoryname = $category['http'][$r['category']];
				
				if (isset($cp_service_flow[$categoryname][$t_time]))
				{
					$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				}
				else
				{
					$cp_service_flow[$categoryname][$t_time] = $r['service_size'] * 1;
				}
				
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = $r['service_size'] * 1;
				}
				
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
			
			foreach ($cp_service_flow as $categoryname => &$v)
			{
				foreach ($v as $time => &$flow)
				{
					$flow = division($flow, $operation_stat['sum_service']) * $traffic_stat['service'][$t_time];
				}
			}
			
			foreach ($cp_cache_flow as $categoryname => &$v)
			{
				foreach ($v as $time => &$flow)
				{
					$flow = division($flow, $operation_stat['sum_cache']) * $traffic_stat['cache'][$t_time];
				}
			}
		}
		
		//占比计算
		$algorithm = new algorithm($start_time,$end_time,$this->_duration_second);
		$traffic_stat = $algorithm->traffic_stat();
		$operation_stat = $algorithm->operation_stat();
		foreach ($cp_cache_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['cache'][$time]) * $traffic_stat['cache'][$time];
			}
		}
		foreach ($cp_service_flow as $classname=>&$v)
		{
			foreach ($v as $time => &$value)
			{
				$value = division($value, $operation_stat['service'][$time]) * $traffic_stat['service'][$time];
			}
		}
		
		$topfile = array();
		foreach ($category['http'] as $key=>$name)
		{
			$topfile[$name] = $this->model('top_stat')
			->where('create_time>=? and create_time<?',array(
				$start_time,
				$end_time,
			))
			->where('class=? and category=?',array(0,$key))
			->group('hash')
			->order('sum_service','desc')
			->limit(10)
			->select(array(
				'host',
				'filename',
				'cache_size',
				'sum(service_size) as sum_service',
			));
		}
		
		$data = array(
			//分CP服务流速堆叠
			'cp_service_flow' => $cp_service_flow,
			//分CP缓存服务堆叠
			'cp_cache_flow' => $cp_cache_flow,
			//流量对比
			'cp_cache_service_sum' => $cp_cache_service_sum,
			//资源热榜
			'topfile' => $topfile,
		);
		return new json(json::OK,'ok',$data);
	}
	
	function __access()
	{
		return array(
			array(
				'deny',
				'actions' => '*',
				'express' => \application\entity\user::getLoginUserId()===NULL,
				'message' => new json(array('code'=>2,'result'=>'尚未登陆')),
			)
		);
	}
}