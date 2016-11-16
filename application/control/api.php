<?php
namespace application\control;
use application\extend\apiControl;
use framework\core\response\json;
use application\algorithm\algorithm;
use framework\core\model;

class api extends apiControl
{
	function overview()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$duration = $this->post('duration',5*60,'int','i');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'duration' => $duration,
			'sn' => $sn,
		),'sn_duration');
		if (!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$algorithm = new algorithm($starttime,$endtime,$duration);
		$cds_online_num = $algorithm->CDSOnlineNum($sn);
		$user_online_num = $algorithm->USEROnlineNum($sn);
		$traffic_stat = $algorithm->traffic_stat($sn);
		$operation_stat = $algorithm->operation_stat($sn);
		$data = array(
			'cds_online_num' => $cds_online_num['max'],
			'user_online_num' => $user_online_num['max'],
			'service' => !empty($traffic_stat['service'])?max($traffic_stat['service']):0,
			'service_sum' => !empty($traffic_stat['service'])?max($operation_stat['service']):0,
		);
		
		
		$result = array();
		if (is_array($sn))
		{
			foreach ($sn as $s)
			{
				$traffic_stat = $algorithm->traffic_stat($s);
				$result[] = array(
					'sn' => $s,
					'name' => $this->model('user_info')->where('sn=?',array($s))->scalar('company'),
					'max_service' => 1*empty($traffic_stat)?0:max($traffic_stat['service']),
					'max_online' => 1*$this->model('traffic_stat')
					->where('sn=?',array($s))
					->where('create_time>=? and create_time<?',array(
						$starttime,$endtime
					))
					->max('online_user'),
				);
			}
		}
		
		$data['detail'] = $result;
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 服务流速，缓存流速
	 */
	function traffic_stat()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$duration = $this->post('duration',5*60,'int','i');
		$sn = $this->post('sn');
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'duration' => $duration,
			'sn' => $sn,
		),'sn_duration');
		if (!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$algorithm = new algorithm($starttime,$endtime,$duration);
		$result = $algorithm->traffic_stat($sn);
		return new json(json::OK,NULL,$result);
	}
	
	/**
	 * 分CP流速
	 * @return \framework\core\response\json
	 */
	function cp_traffic_stat()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$duration = $this->post('duration',5*60,'int','i');
		$sn = $this->post('sn');
		$top = $this->post('top',5);
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'duration' => $duration,
			'sn' => $sn,
		),'sn_duration');
		if (!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$category = $this->getConfig('category');
		
		$getCategoryName = function($category,$r){
			if (isset($r['class']) && isset($r['category']))
			{
				switch ($r['class'])
				{
					case '0':
						return isset($category['http'][$r['category']])?$category['http'][$r['category']]:'其它';
					case '1':
						return isset($category['mobile'][$r['category']])?$category['http'][$r['category']]:'其它';
					case '2':
						if ($r['category'] >= 128)
						{
							return isset($category['videoLive'][$r['category'] - 128])?$category['videoLive'][$r['category'] - 128]:'其它';
						}
						else
						{
							return isset($category['videoDemand'][$r['category']])?$category['videoDemand'][$r['category']]:'其它';
						}
				}
			}
		};
		
		
		
		$data = array();
		$topCategory = array();
		
		$result = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime,
		))
		->limit($top)
		->where('sn=?',array($sn))
		->group(array('class','category'))
		->order('sum_service','desc')
		->select(array(
			'sum_service'=>'sum(service_size)',
			'class',
			'category',
		));
		foreach ($result as $r)
		{
			$topCategory[] = array(
				'class' => $r['class'],
				'category' => $r['category']
			);
			$data[$getCategoryName($category,$r)] = array();
		}
		$data['其它'] = array();
		
		for($t_time = $starttime;strtotime($t_time) < strtotime($endtime); $t_time = date('Y-m-d H:i:s',strtotime($t_time) + $duration))
		{
			foreach ($data as &$v)
			{
				$v[$t_time] = 0;
			}
			
			$result = $this->model('operation_stat')
			->where('sn=?',array($sn))
			->where('make_time>=? and make_time<?',array(
				$t_time,
				date('Y-m-d H:i:s',strtotime($t_time) + $duration)
			))
			->select('class,category,service_size');
			foreach ($result as $r)
			{
				if (in_array(array(
					'class' => $r['class'],
					'category' => $r['category']
				), $topCategory))
				{
					$categoryName = $getCategoryName($category,$r);
				}
				else
				{
					$categoryName = '其它';
				}
				$data[$categoryName][$t_time] += $r['service_size'];
			}
		}
		
		return new json(json::OK,NULL,$data);
	}
	
	/**
	 * 用户趋势图
	 */
	function user_online_detail()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$duration = $this->post('duration');
		$sn = $this->post('sn');
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
			'duration' => $duration
		),'sn_duration');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$algorithm = new algorithm($starttime,$endtime,$duration);
		$data = $algorithm->USEROnlineNum($sn);
		return new json(json::OK,NULL,$data);
	}
	
	/**
	 * 常规资源 - 资源流量
	 */
	function resource_http()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$data = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->In('sn',$sn)
		->where('class=?',array(0))
		->group('category')
		->order('service','desc')
		->select(array(
			'category',
			'service' => 'sum(service_size)',
			'cache' => 'sum(cache_size)',
		));
		
		$category = $this->getConfig('category');
		$temp = array();
		foreach ($data as $r)
		{
			$temp[$category['http'][$r['category']]] = array(
				'service' => $r['service']*1,
				'cache' => $r['cache']*1,
			);
		}
		
		return new json(json::OK,'ok',$temp);
	}
	
	/**
	 * 常规资源 - 移动应用
	 */
	function resource_mobile()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
	
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
	
		$data = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->In('sn',$sn)
		->where('class=?',array(1))
		->group('category')
		->order('service','desc')
		->select(array(
			'category',
			'service' => 'sum(service_size)',
			'cache' => 'sum(cache_size)',
		));
	
		$category = $this->getConfig('category');
		$temp = array();
		foreach ($data as $r)
		{
			$temp[$category['mobile'][$r['category']]] = array(
				'service' => $r['service']*1,
				'cache' => $r['cache']*1,
			);
		}
	
		return new json(json::OK,'ok',$temp);
	}
	
	/**
	 * 常规资源 - 视频点播
	 */
	function resource_videoDemand()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
	
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
	
		$data = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->In('sn',$sn)
		->where('class=? and category<?',array(2,128))
		->group('category')
		->order('service','desc')
		->select(array(
			'category',
			'service' => 'sum(service_size)',
			'cache' => 'sum(cache_size)',
		));
	
		$category = $this->getConfig('category');
		$temp = array();
		foreach ($data as $r)
		{
			$temp[$category['videoDemand'][$r['category']]] = array(
				'service' => $r['service']*1,
				'cache' => $r['cache']*1,
			);
		}
	
		return new json(json::OK,'ok',$temp);
	}
	
	/**
	 * 常规资源 - 视频直播
	 */
	function resource_videoLive()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
	
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
	
		$data = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->In('sn',$sn)
		->where('class=? and category>=?',array(2,128))
		->group('category')
		->order('service','desc')
		->select(array(
			'category',
			'service' => 'sum(service_size)',
			'cache' => 'sum(proxy_cache_size)',
		));
	
		$category = $this->getConfig('category');
		$temp = array();
		foreach ($data as $r)
		{
			$temp[$category['videoLive'][$r['category']]] = array(
				'service' => $r['service']*1,
				'cache' => $r['cache']*1,
			);
		}
	
		return new json(json::OK,'ok',$temp);
	}
	
	/**
	 * 资源流量
	 */
	function resource()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn',array(),'explode:",","?"','a');
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		
		$result['total'] = $this->model('operation_stat')
		->In('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->find(array(
			'sum_cache'=>'sum(cache_size)',
			'sum_proxy'=>'sum(proxy_cache_size)',
			'sum_service'=>'sum(service_size)',
		));
		
		$result['videoDemand'] = $this->model('operation_stat')
		->In('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->where('class=? and category<?',array(2,128))
		->find(array(
			'sum_cache'=>'sum(cache_size)',
			'sum_proxy'=>'sum(proxy_cache_size)',
			'sum_service'=>'sum(service_size)',
		));
		
		$result['videoLive'] = $this->model('operation_stat')
		->In('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->where('class=? and category>=?',array(2,128))
		->find(array(
			'sum_cache'=>'sum(cache_size)',
			'sum_proxy'=>'sum(proxy_cache_size)',
			'sum_service'=>'sum(service_size)',
		));
		
		$result['mobile'] = $this->model('operation_stat')
		->In('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->where('class=?',array(1))
		->find(array(
			'sum_cache'=>'sum(cache_size)',
			'sum_proxy'=>'sum(proxy_cache_size)',
			'sum_service'=>'sum(service_size)',
		));
		
		$result['http'] = $this->model('operation_stat')
		->In('sn',$sn)
		->where('make_time>=? and make_time<?',array(
			$starttime,
			$endtime
		))
		->where('class=?',array(0))
		->find(array(
			'sum_cache'=>'sum(cache_size)',
			'sum_proxy'=>'sum(proxy_cache_size)',
			'sum_service'=>'sum(service_size)',
		));
		
		foreach ($result as $index => &$v)
		{
			foreach ($v as &$value)
			{
				$value = $value*1;
			}
		}
		
		return new json(json::OK,NULL,$result);
	}
	
	/**
	 * 资源热榜
	 * @return \framework\core\response\json
	 */
	function topfile()
	{
		$starttime = $this->post('starttime');
		$endtime = $this->post('endtime');
		$sn = $this->post('sn');
		
		$api = new \application\entity\api(array(
			'starttime' => $starttime,
			'endtime' => $endtime,
			'sn' => $sn,
		),'sn');
		if(!$api->validate())
		{
			return new json(json::FAILED,$api->getError());
		}
		
		$type = $this->post('type','');
		$top = $this->post('top',10);
		
		$fields = array(
			'max(cache_size) as cache_size',
			'sum(service_size) as sum_service',
			'host',
			'filename',
			'class',
			'category',
		);
		
		switch ($type)
		{
			case 'http':
				$this->model('top_stat')->where('class=?',array(0));
				break;
			case 'mobile':
				$this->model('top_stat')->where('class=?',array(1));
				break;
			case 'videoLive':
				$this->model('top_stat')->where('class=? and category>=?',array(2,128));
				$fields = array(
					'sum(cache_size) as cache_size',
					'sum(service_size) as sum_service',
					'host',
					'filename',
					'class',
					'category',
				);
				break;
			case 'videoDemand':
				$this->model('top_stat')->where('class=? and category<?',array(2,128));
				break;
		}
		
		$top_category = array();
		$topfile_category = $this->model('top_stat')
		->where('sn=?',array($sn))
		->where('create_time >= ? and create_time<?',array(
			$starttime,
			$endtime
		))
		->group('category')
		->limit(5)
		->order('sum_service','desc')
		->select(array(
			'sum_service' => 'sum(service_size)',
			'category',
			'class'
		));
		
		foreach ($topfile_category as $category)
		{
			$top_category[] = array(
				'class'=>$category['class'],
				'category' => $category['category'],
			);
		}
		
		$category_config = self::getConfig('category');
		
		$getCategoryName = function($config,$category)
		{
			if (isset($category['class']) && isset($category['category']))
			{
				switch ($category['class'])
				{
					case '0':return $config['http'][$category['category']];
					case '1':return $config['mobile'][$category['category']];
					case '2':
						if ($category['category']>=128)
						{
								
							return $config['videoLive'][$category['category']-128];
						}
						return $config['videoDemand'][$category['category']];
				}
			}
		};
		
		$topfile = array();
		$selected_class = 0;
		$selected_category = array();
		foreach ($top_category as $category)
		{
			$selected_category[] = $category['category'];
			$selected_class = $category['class'];
			$categoryName = $getCategoryName($category_config,$category);
			$topfile[$categoryName] = $this->model('top_stat')
			->where('create_time >= ? and create_time<?',array(
				$starttime,
				$endtime
			))
			->where('class=? and category=?',array($category['class'],$category['category']))
			->where('sn=?',array($sn))
			->group(array('hash'))
			->order('sum_service','desc')
			->limit($top)
			->select($fields);
		}
		
		if ($type == 'videoLive')
		{
			$this->model('top_stat')
			->where('category>=?',array(128));
		}
		else
		{
			$this->model('top_stat')
			->where('category<?',array(128));
		}
		
		$topfile['其它'] = $this->model('top_stat')
		->where('create_time >= ? and create_time<?',array(
			$starttime,
			$endtime
		))
		->where('class=?',array($selected_class))
		->notIn('category',$selected_category)
		->where('sn=?',array($sn))
		->group(array('hash'))
		->order('sum_service','desc')
		->limit($top)
		->select($fields);
		
		return new json(json::OK,NULL,$topfile);
	}
	
}