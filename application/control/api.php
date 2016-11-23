<?php
namespace application\control;
use application\extend\apiControl;
use framework\core\response\json;
use application\algorithm\algorithm;
use framework\core\model;
use application\algorithm\ratio;
use framework\core\request;
use application\extend\cache;

class api extends apiControl
{
	function initlize()
	{
		$response = parent::initlize();
		if (!is_null($response))
		{
			return $response;
		}
		
		if (request::param('a','','trim|strtolower') != strtolower('saveSnInCache'))
		{
			$response = $this->setTime();
			if (!is_null($response))
			{
				return $response;
			}
		}
	}
	
	function saveSnInCache()
	{
		$oldsn = $this->post('oldsn',array(),'explode:",","?"','a');
		$newsn = $this->post('newsn',array(),'explode:",","?"','a');
	
		if (empty($oldsn) && empty($newsn))
		{
			return new json(json::FAILED,'sn不能都为空');
		}
		
		$oldsn_string = implode(',', $oldsn);
		$newsn_string = implode(',', $newsn);
		
		$create_cache_sn = array();
		
		if (empty($oldsn_string))
		{
			$data = $this->model('sn_in_cache')->where('sns=?',array($newsn_string))->find();
			if (empty($data))
			{
				$result = $this->model('sn_in_cache')->insert(array(
					'sns' => $newsn_string,
					'num' => 1,
				));
				$create_cache_sn = $newsn;
			}
			else
			{
				$result = $this->model('sn_in_cache')->where('sns=?',array($newsn_string))->limit(1)->update(array(
					'num+=' => 1
				));
			}
		}
		else
		{
			if (empty($newsn_string))
			{
				$data = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->find();
				if ($data['num'] == 1)
				{
					$result = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->delete();
				}
				else
				{
					$result = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->limit(1)->update(array(
						'num-=' => 1
					));
				}
			}
			else
			{
				$data = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->find();
				if (empty($data))
				{
					//旧的sn居然还没加入到缓存
					$result = $this->model('sn_in_cache')->insert(array(
						'sns' => $oldsn_string,
						'num' => 1,
					));
					$create_cache_sn = $oldsn;
				}
				else
				{
					if ($data['num'] == 1)
					{
						$result = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->delete();
					}
					else
					{
						$result = $this->model('sn_in_cache')->where('sns=?',array($oldsn_string))->limit(1)->update(array(
							'num-=' => 1
						));
					}
					
					$data = $this->model('sn_in_cache')->where('sns=?',array($newsn_string))->find();
					if (empty($data))
					{
						$result = $this->model('sn_in_cache')->insert(array(
							'sns' => $newsn_string,
							'num' => 1,
						));
						$create_cache_sn = $newsn;
					}
					else
					{
						$result = $this->model('sn_in_cache')->where('sns=?',array($newsn_string))->limit(1)->update(array(
							'num+=' => 1
						));
					}
				}
			}
		}
		
		
		if ($result)
		{
			$response = new json(json::OK,'ok');
			$response->getHeader()->sendAll();
			echo $response->getBody();
		}
		
		fastcgi_finish_request();
		
		if (!empty($create_cache_sn))
		{
			foreach ($create_cache_sn as $sn)
			{
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 1 -sn '.$sn;
				exec($string);
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 2 -sn '.$sn;
				exec($string);
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 3 -sn '.$sn;
				exec($string);
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 4 -sn '.$sn;
				exec($string);
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 5 -sn '.$sn;
				exec($string);
				$string = 'php '.ROOT.'/index.php -c api -a detail -duration minutely -timemode 6 -sn '.$sn;
				exec($string);
			}
			
			$create_cache_sn = implode(',', $create_cache_sn);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 1 -sn '.$create_cache_sn;
			exec($string);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 2 -sn '.$create_cache_sn;
			exec($string);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 3 -sn '.$create_cache_sn;
			exec($string);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 4 -sn '.$create_cache_sn;
			exec($string);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 5 -sn '.$create_cache_sn;
			exec($string);
			$string = 'php '.ROOT.'/index.php -c api -a overview -duration minutely -timemode 6 -sn '.$create_cache_sn;
			exec($string);
		}
	}
	
	function overview()
	{
		$sn = $this->post('sn',array(),'explode:",","?"','a');
		if (empty($sn))
		{
			return new json(json::FAILED,'sn不能为空');
		}

		if (!empty($this->_timemode))
		{
			$cache_key = 'api_overview_'.$this->_timemode.'_'.implode(',', $sn);
			if (request::php_sapi_name()=='web')
			{
				$response = cache::get($cache_key);
				if (!empty($response))
				{
					return new json(json::OK,NULL,$response);
				}
			}
		}
		
		$algorithm = new algorithm($this->_startTime,$this->_endTime,$this->_duration_second);
		$cds = $algorithm->CDSOnlineNum($sn);
		$online = $algorithm->USEROnlineNum($sn);
		$serviceMax = $algorithm->ServiceMax($sn);
		$serviceSum = $algorithm->ServiceSum($sn);
		
		$ratio = new ratio($this->_timemode);
		$cds_ratio = $ratio->cds($sn);
		$online_ratio = $ratio->user($sn);
		$serviceMax_ratio = $ratio->service_max($sn);
		$serviceSum_ratio = $ratio->service_sum($sn);
		
		$detail = array();
		foreach ($sn as $s)
		{
			$serviceMax_detail = $algorithm->ServiceMax($s);
			$online_detail = $algorithm->USEROnlineNum($s);
			$detail[] = array(
				'sn' => $s,
				'name' => $this->model('user_info')->where('sn=?',array($s))->scalar('company'),
				'max_service' => $serviceMax_detail['max'],
				'max_online' => $online_detail['max'],
			);
		}
		
		$data = array(
			'cds' => array(
				'max' => $cds['max'],
				'link' => $cds_ratio['link']===NULL?NULL:1*number_format(division($cds['max'] - $cds_ratio['link'],$cds_ratio['link']),2,'.',''),
				'same' => $cds_ratio['same']===NULL?NULL:1*number_format(division($cds['max'] - $cds_ratio['same'],$cds_ratio['same']),2,'.',''),
			),
			'online' => array(
				'max' => $online['max'],
				'link' => $online_ratio['link']===NULL?NULL:1*number_format(division($online['max'] - $online_ratio['link'],$online_ratio['link']),2,'.',''),
				'same' => $online_ratio['same']===NULL?NULL:1*number_format(division($online['max'] - $online_ratio['same'],$online_ratio['same']),2,'.',''),
			),
			'serviceMax' => array(
				'max' => $serviceMax['max'],
				'link' => $serviceMax_ratio['link']===NULL?NULL:1*number_format(division($serviceMax['max'] - $serviceMax_ratio['link'],$serviceMax_ratio['link']),2,'.',''),
				'same' => $serviceMax_ratio['same']===NULL?NULL:1*number_format(division($serviceMax['max'] - $serviceMax_ratio['same'],$serviceMax_ratio['same']),2,'.',''),
			),
			'serviceSum' => array(
				'max' => $serviceSum['max'],
				'link' => $serviceSum_ratio['link']===NULL?NULL:1*number_format(division($serviceSum['max'] - $serviceSum_ratio['link'],$serviceSum_ratio['link']),2,'.',''),
				'same' => $serviceSum_ratio['same']===NULL?NULL:1*number_format(division($serviceSum['max'] - $serviceSum_ratio['same'],$serviceSum_ratio['same']),2,'.',''),
			),
			'detail' => $detail,
		);
		
		if (!empty($this->_timemode))
		{
			cache::set($cache_key, $data);
		}
		
		return new json(json::OK,'ok',$data);
	}
	
	/**
	 * 移动端CDS详情接口
	 * @return \framework\core\response\json
	 */
	function detail()
	{
		$sn = $this->post('sn',array(),'explode:",","?"','a');
		if (empty($sn))
		{
			return new json(json::FAILED,'sn不能为空');
		}
		
		if (!empty($this->_timemode))
		{
			$cache_key = 'api_detail_'.$this->_timemode.'_'.implode(',', $sn);
			if (request::php_sapi_name()=='web')
			{
				$response = cache::get($cache_key);
				if (!empty($response))
				{
					return new json(json::OK,NULL,$response);
				}
			}
		}
		
		$algorithm = new algorithm($this->_startTime,$this->_endTime,$this->_duration_second);
		$ratio = new ratio($this->_timemode);
		
		$user = $algorithm->USEROnlineNum($sn);
		$user_ratio = $ratio->user($sn);
		
		$service = $algorithm->ServiceMax($sn);
		$service_ratio = $ratio->service_max($sn);
		
		$cds_velocity_gain = array();
		$traffic_stat = $algorithm->traffic_stat($sn);
		foreach ($traffic_stat['service'] as $time => $stat)
		{
			$cds_velocity_gain[$time] = $stat - $traffic_stat['cache'][$time];
		}
		
		$cp_service = $algorithm->CPService($sn,5);
		
		$online = $algorithm->USEROnlineNum($sn);
		
		//resource
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
		$operation_stat = $this->model('operation_stat')
		->where('make_time>=? and make_time<?',array(
			$this->_startTime,
			$this->_endTime,
		))
		->order('service','desc')
		->group('class,category')
		->select(array(
			'class',
			'category',
			'cache' => 'sum(cache_size)',
			'proxy_cache' => 'sum(proxy_cache_size)',
			'service' => 'sum(service_size)',
		));
		$resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0,
		);
		$http_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0,
		);
		$mobile_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0,
		);
		$videoDemand_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0,
		);
		$videoLive_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0,
		);
		foreach ($operation_stat as $stat)
		{
			$resource['cache'] += $stat['cache'];
			$resource['proxy_cache'] += $stat['proxy_cache'];
			$resource['service'] += $stat['service'];
			if ($stat['class']==0)
			{
				$http_resource['cache'] += $stat['cache'];
				$http_resource['proxy_cache'] += $stat['proxy_cache'];
				$http_resource['service'] += $stat['service'];
			}
			else if ($stat['class']==1)
			{
				$mobile_resource['cache'] += $stat['cache'];
				$mobile_resource['proxy_cache'] += $stat['proxy_cache'];
				$mobile_resource['service'] += $stat['service'];
			}
			else if ($stat['class']==2)
			{
				if ($stat['category']>=128)
				{
					$videoLive_resource['cache'] += $stat['cache'];
					$videoLive_resource['proxy_cache'] += $stat['proxy_cache'];
					$videoLive_resource['service'] += $stat['service'];
				}
				else
				{
					$videoDemand_resource['cache'] += $stat['cache'];
					$videoDemand_resource['proxy_cache'] += $stat['proxy_cache'];
					$videoDemand_resource['service'] += $stat['service'];
				}
			}
		}
	
		
		$class_resource = array(
			'http' => $http_resource,
			'mobile' => $mobile_resource,
			'videoDemand' => $videoDemand_resource,
			'videoLive' => $videoLive_resource,
		);
		
		
		$cp_resource = array();
		foreach ($operation_stat as $stat)
		{
			$categoryName = $this->getCategory($stat);
			if ($stat['class'] == 0)
			{
				$className = 'class';
			}
			else if ($stat['class']==1)
			{
				$className = 'mobile';
			}
			else if ($stat['class']==2)
			{
				if ($stat['category']>=128)
				{
					$className = 'videoLive';
				}
				else 
				{
					$className = 'videoDemand';
				}
			}
			
			if (isset($cp_resource[$className][$categoryName]['cache']))
			{
				$cp_resource[$className][$categoryName]['cache'] += $stat['cache'];
			}
			else
			{
				$cp_resource[$className][$categoryName]['cache'] = $stat['cache'];
			}
			if (isset($cp_resource[$className][$categoryName]['service']))
			{
				$cp_resource[$className][$categoryName]['service'] += $stat['service'];
			}
			else
			{
				$cp_resource[$className][$categoryName]['service'] = $stat['service'];
			}
			if (isset($cp_resource[$className][$categoryName]['proxy_cache']))
			{
				$cp_resource[$className][$categoryName]['proxy_cache'] += $stat['proxy_cache'];
			}
			else
			{
				$cp_resource[$className][$categoryName]['proxy_cache'] = $stat['proxy_cache'];
			}
		}
		
		
		$data = array(
			'main' => array(
				'user' => array(
					'max' => $user['max'],
					'link' => $user_ratio['link']===NULL?NULL:1*number_format(division($user['max'] - $user_ratio['link'],$user_ratio['link']),2,'.',''),
					'same' => $user_ratio['same']===NULL?NULL:1*number_format(division($user['max'] - $user_ratio['same'],$user_ratio['same']),2,'.',''),
				),
				'service' => array(
					'max' => $service['max'],
					'link' => $service_ratio['link']===NULL?NULL:1*number_format(division($service['max'] - $service_ratio['link'],$service_ratio['link']),2,'.',''),
					'same' => $service_ratio['same']===NULL?NULL:1*number_format(division($service['max'] - $service_ratio['same'],$service_ratio['same']),2,'.',''),
				)
			),
			'cds_service_cache' => $cds_velocity_gain,//流速增益
			'cds_traffic_stat' => $traffic_stat,//流速详细
			'cds_cp_traffic_stat' => $cp_service,//分cp服务流速
			'online' => $online['detail'],//用户趋势图
			'resource' => $resource,//资源引入情况
			'class_resource' => $class_resource,//分类型资源引入  分类型服务流量
			'cp_resource' => $cp_resource,//分cp资源引入详情
		);
		
		if (!empty($this->_timemode))
		{
			cache::set($cache_key, $data);
		}
		
		return new json(json::OK,'ok',$data);
	}
}