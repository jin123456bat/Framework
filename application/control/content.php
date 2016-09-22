<?php
namespace application\control;
use application\extend\BaseControl;
use framework\core\response\json;

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
			'videoDemand' => array('service'=>0,'cache'=>0),
			'videoLive' => array('service'=>0,'cache'=>0),
		);
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
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
				
				if (isset($category_service[$classname][$t_time]))
				{
					$category_service[$classname][$t_time] += $r['service_size'] * 1;
				}
				else
				{
					$category_service[$classname][$t_time] = 0;
				}
				
				if (isset($category_cache[$classname][$t_time]))
				{	
					$category_cache[$classname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$category_cache[$classname][$t_time] = 0;
				}
				
				$flow[$classname]['service'] += $r['service_size'] * 1;
				$flow[$classname]['cache'] += $r['cache_size']*1;
				$flow['total']['service'] += $r['service_size']*1;
				$flow['total']['cache'] += $r['cache_size']*1;
			}
		}
		
		$topfile = array();
		$topfile['http'] = $this->model('top_stat')
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
		));
		
		$topfile['mobile'] = $this->model('top_stat')
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
		));
		
		$topfile['videoDemand'] = $this->model('top_stat')
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
		$topfile['videoLive'] = $this->model('top_stat')
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
		
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			)
		);
		
		foreach ($category['videoDemand'] as $key => $name)
		{
			$cp_cache_service_sum[$name] = array('service'=>0,'cache'=>0);
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
			
			foreach ($result as $r)
			{
				$categoryname = $category['videoDemand'][$r['category']];
				
				if (isset($cp_service_flow[$categoryname][$t_time]))
				{
					$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				}
				else
				{
					$cp_service_flow[$categoryname][$t_time] = 0;
				}
		
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = 0;
				}
		
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
		}
		
		
		$topfile = array();
		foreach ($category['videoDemand'] as $key=>$name)
		{
			$topfile[$name] = $this->model('top_stat')
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
	
	/**
	 * 视频直播
	 */
	function videoLive()
	{
		$start_time = date('Y-m-d H:00:00',strtotime($this->_startTime));
		$end_time = date('Y-m-d H:00:00',strtotime($this->_endTime));
		
		$category = $this->getConfig('category');
		
		$cp_service_flow = array();
		$cp_cache_flow = array();
		$cp_cache_service_sum = array(
			'总流量'=>array(
				'service' => 0,
				'cache' => 0,
			)
		);
		
		foreach ($category['videoLive'] as $key => $name)
		{
			$cp_cache_service_sum[$name] = array('service'=>0,'cache'=>0);
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
	
			foreach ($result as $r)
			{
				$categoryname = $category['videoLive'][$r['category']];
		
				if (isset($cp_service_flow[$categoryname][$t_time]))
				{
					$cp_service_flow[$categoryname][$t_time] += $r['service_size'] * 1;
				}
				else
				{
					$cp_service_flow[$categoryname][$t_time] = 0;
				}
		
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = 0;
				}
		
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
			}
		}
		

		$topfile = array();
		foreach ($category['videoLive'] as $key=>$name)
		{
			$topfile[$name] = $this->model('top_stat')
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
			'android' => array(
				'service' => 0,
				'cache' => 0,
			),
			'ios' => array(
				'service' => 0,
				'cache' => 0,
			),
			'winphone' => array(
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
					$cp_service_flow[$categoryname][$t_time] = 0;
				}
		
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = 0;
				}
		
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
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
		
		$category = $this->getConfig('category');
		for($t_time = $start_time;strtotime($t_time)<strtotime($end_time);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$this->_duration_second))
		{
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
					$cp_service_flow[$categoryname][$t_time] = 0;
				}
				
				if (isset($cp_cache_flow[$categoryname][$t_time]))
				{
					$cp_cache_flow[$categoryname][$t_time] += $r['cache_size'] * 1;
				}
				else
				{
					$cp_cache_flow[$categoryname][$t_time] = 0;
				}
				
				$cp_cache_service_sum[$categoryname]['service'] += $r['service_size']*1;
				$cp_cache_service_sum[$categoryname]['cache'] += $r['cache_size']*1;
				$cp_cache_service_sum['总流量']['service'] += $r['service_size']*1;
				$cp_cache_service_sum['总流量']['cache'] += $r['cache_size']*1;
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
}