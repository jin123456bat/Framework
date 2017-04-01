<?php
namespace application\control;

use application\extend\apiControl;
use framework\core\response\json;
use application\algorithm\algorithm;
use framework\core\model;
use application\algorithm\ratio;
use framework\core\request;
use application\extend\cache;
use framework\core\debugger;
use application\algorithm\api_overview;
use application\algorithm\api_overview_ratio;

class api extends apiControl
{

	function initlize()
	{
		$response = parent::initlize();
		if (! is_null($response))
		{
			return $response;
		}
		
		if (request::param('a', '', 'trim|strtolower') != strtolower('saveSnInCache'))
		{
			$response = $this->setTime();
			if (! is_null($response))
			{
				return $response;
			}
		}
	}

	function saveSnInCache()
	{
		$oldsn = $this->post('oldsn', array(), 'explode:",","?"', 'a');
		$newsn = $this->post('newsn', array(), 'explode:",","?"', 'a');
		sort($oldsn);
		sort($newsn);
		
		if (empty($oldsn) && empty($newsn))
		{
			return new json(json::FAILED, 'sn不能都为空');
		}
		
		$oldsn_string = implode(',', $oldsn);
		$newsn_string = implode(',', $newsn);
		
		// $create_cache_sn = array();
		$overview_sn = '';
		$detail_sn = array();
		
		if (empty($oldsn_string))
		{
			if (! empty($newsn_string))
			{
				$data = $this->model('sn_in_cache')
					->where('sns=?', array(
					$newsn_string
				))
					->find();
				if (empty($data))
				{
					$result = $this->model('sn_in_cache')->insert(array(
						'sns' => $newsn_string,
						'num' => 1
					));
					// $create_cache_sn = $newsn;
					$overview_sn = $newsn_string;
					$detail_sn = $newsn;
				}
				else
				{
					$result = $this->model('sn_in_cache')
						->where('sns=?', array(
						$newsn_string
					))
						->limit(1)
						->update(array(
						'num+=' => 1
					));
				}
			}
		}
		else
		{
			if (empty($newsn_string))
			{
				if (! empty($oldsn_string))
				{
					$data = $this->model('sn_in_cache')
						->where('sns=?', array(
						$oldsn_string
					))
						->find();
					if ($data['num'] == 1)
					{
						$result = $this->model('sn_in_cache')
							->where('sns=?', array(
							$oldsn_string
						))
							->delete();
					}
					else
					{
						$result = $this->model('sn_in_cache')
							->where('sns=?', array(
							$oldsn_string
						))
							->limit(1)
							->update(array(
							'num-=' => 1
						));
					}
				}
			}
			else
			{
				$data = $this->model('sn_in_cache')
					->where('sns=?', array(
					$oldsn_string
				))
					->find();
				if (empty($data))
				{
					if (! empty($oldsn_string))
					{
						// 旧的sn居然还没加入到缓存
						$result = $this->model('sn_in_cache')->insert(array(
							'sns' => $oldsn_string,
							'num' => 1
						));
						// $create_cache_sn = $oldsn;
						$overview_sn = $oldsn_string;
						$detail_sn = $oldsn;
					}
					
					if (! empty($newsn_string))
					{
						// 把新的也加进去
						$result = $this->model('sn_in_cache')->insert(array(
							'sns' => $newsn_string,
							'num' => 1
						));
						
						$overview_sn = empty($overview_sn) ? $oldsn_string : ($overview_sn . ',' . $oldsn_string);
						$detail_sn = array_merge($oldsn, $newsn);
						// $create_cache_sn = $newsn;
					}
				}
				else
				{
					if ($data['num'] == 1)
					{
						$result = $this->model('sn_in_cache')
							->where('sns=?', array(
							$oldsn_string
						))
							->delete();
					}
					else
					{
						$result = $this->model('sn_in_cache')
							->where('sns=?', array(
							$oldsn_string
						))
							->limit(1)
							->update(array(
							'num-=' => 1
						));
					}
					
					$data = $this->model('sn_in_cache')
						->where('sns=?', array(
						$newsn_string
					))
						->find();
					if (empty($data))
					{
						$result = $this->model('sn_in_cache')->insert(array(
							'sns' => $newsn_string,
							'num' => 1
						));
						$overview_sn = $newsn_string;
						$detail_sn = array_diff($newsn, $oldsn);
					}
					else
					{
						$result = $this->model('sn_in_cache')
							->where('sns=?', array(
							$newsn_string
						))
							->limit(1)
							->update(array(
							'num+=' => 1
						));
					}
				}
			}
		}
		
		if ($result)
		{
			$response = new json(json::OK, 'ok');
			$response->getHeader()->sendAll();
			echo $response->getBody();
		}
		
		if (\fastcgi_finish_request())
		{
			// 关闭session,防止阻塞
			session_write_close();
			
			// 立即创建缓存
			if (! empty($overview_sn))
			{
				$cache = new \application\algorithm\cache();
				$cache->api_cds_online(3600, date('Y-m-d H:i:s', strtotime('-14 day')), date('Y-m-d H:i:s'), $overview_sn);
				$cache->api_user_online_traffic_stat(3600, date('Y-m-d H:i:s', strtotime('-14 day')), date('Y-m-d H:i:s'), $overview_sn);
				$cache->api_cds_online(86400, date('Y-m-d H:i:s', strtotime('-2 month')), date('Y-m-d H:i:s'), $overview_sn);
				$cache->api_user_online_traffic_stat(86400, date('Y-m-d H:i:s', strtotime('-2 month')), date('Y-m-d H:i:s'), $overview_sn);
				// 生成概览页的缓存
				// $create_cache_sn = implode(',', $create_cache_sn);
				$commands['api_overview_hourly_1_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration hourly -timemode 1 -sn ' . $overview_sn;
				$commands['api_overview_hourly_2_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration hourly -timemode 2 -sn ' . $overview_sn;
				$commands['api_overview_daily_3_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration daily -timemode 3 -sn ' . $overview_sn;
				$commands['api_overview_daily_4_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration daily -timemode 4 -sn ' . $overview_sn;
				$commands['api_overview_daily_5_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration daily -timemode 5 -sn ' . $overview_sn;
				$commands['api_overview_daily_6_' . $overview_sn] = 'php ' . ROOT . '/index.php -c api -a overview -duration daily -timemode 6 -sn ' . $overview_sn;
				$this->runTask($commands);
			}
		}
	}

	/**
	 * 运行单条命令
	 *
	 * @param unknown $command        	
	 * @param unknown $name        	
	 */
	function runTask($command, $name = '')
	{
		if (is_string($command))
		{
			// 把原来的删除掉
			// $close_shell = "ps -ef | grep '{$command}' | grep -v grep | cut -c 9-15 | xargs kill -9";
			// exec($close_shell);
			
			$createtime = date('Y-m-d H:i:s', time());
			$debugger = new debugger();
			$response = exec($command, $output);
			$output = implode('', $output);
			$debugger->stop();
			$this->model('task_detail')->insert(array(
				'createtime' => $createtime,
				'endtime' => date('Y-m-d H:i:s', time()),
				'time' => $debugger->getTime(),
				'name' => $name,
				'response' => $output,
				'command' => $command
			));
		}
		else if (is_array($command))
		{
			foreach ($command as $name => $com)
			{
				$this->runTask($com, $name);
			}
		}
	}

	function overview()
	{
		$sn = $this->post('sn', array(), 'explode:",","?"', 'a');
		if (empty($sn))
		{
			return new json(json::FAILED, 'sn不能为空');
		}
		sort($sn);
		
		if (! empty($this->_timemode))
		{
			$cache_key = 'api_overview_' . $this->_duration . '_' . $this->_timemode . '_' . implode(',', $sn);
			if (request::php_sapi_name() == 'web')
			{
				/*
				 * $response = cache::get($cache_key);
				 * if (!empty($response))
				 * {
				 * return new json(json::OK,NULL,$response);
				 * }
				 * else
				 * {
				 * return new json(3,'生成报表大约需要10分钟,请稍后...');
				 * }
				 */
			}
		}
		
		$algorithm = new algorithm($this->_startTime, $this->_endTime, $this->_duration_second);
		
		if (count($sn) > 1)
		{
			$api_overview = new api_overview($this->_duration_second, $this->_startTime, $this->_endTime);
			$cds = $api_overview->cds($sn);
			$cds_max = empty($cds) ? 0 : max($cds);
			$user = $api_overview->user($sn);
			$user_max = empty($user) ? 0 : max($user);
			$traffic_stat_service = $api_overview->traffic_stat_service($sn);
			$traffic_stat_service_max = empty($traffic_stat_service) ? 0 : max($traffic_stat_service);
			$operation_stat_sum = $api_overview->operation_stat_sum($sn);
			
			$ratio = new api_overview_ratio($this->_timemode);
			$ratio->setDuration($this->_duration_second);
			$cds_ratio = $ratio->cds($sn);
			$online_ratio = $ratio->user($sn);
			$serviceMax_ratio = $ratio->service_max($sn);
			$serviceSum_ratio = $ratio->service_sum($sn);
		}
		else if (count($sn) == 1)
		{
			$cds = $algorithm->CDSOnlineNum($sn);
			$cds_max = $cds['max'];
			$user = $algorithm->USEROnlineNum($sn);
			$user_max = $user['max'];
			$traffic_stat_service = $algorithm->ServiceMax($sn);
			$traffic_stat_service_max = $traffic_stat_service['max'];
			$operation_stat_sum = $algorithm->ServiceSum($sn);
			$operation_stat_sum = $operation_stat_sum['max'];
			
			$ratio = new ratio($this->_timemode);
			$ratio->setDuration($this->_duration_second);
			$cds_ratio = $ratio->cds($sn);
			$online_ratio = $ratio->user($sn);
			$serviceMax_ratio = $ratio->service_max($sn);
			$serviceSum_ratio = $ratio->service_sum($sn);
		}
		
		$detail = array();
		foreach ($sn as $s)
		{
			$serviceMax_detail = $algorithm->ServiceMax($s);
			$online_detail = $algorithm->USEROnlineNum($s);
			$detail[] = array(
				'sn' => $s,
				'name' => $this->model('user_info')
					->where('sn=?', array(
					$s
				))
					->scalar('company'),
				'max_service' => $serviceMax_detail['max'],
				'max_online' => $online_detail['max']
			);
		}
		
		$data = array(
			'cds' => array(
				'max' => $cds_max,
				'link' => $cds_ratio['link'] === null ? null : 1 * number_format(division($cds_max - $cds_ratio['link'], $cds_ratio['link']), 4, '.', ''),
				'same' => $cds_ratio['same'] === null ? null : 1 * number_format(division($cds_max - $cds_ratio['same'], $cds_ratio['same']), 4, '.', '')
			),
			'online' => array(
				'max' => $user_max,
				'link' => $online_ratio['link'] === null ? null : 1 * number_format(division($user_max - $online_ratio['link'], $online_ratio['link']), 4, '.', ''),
				'same' => $online_ratio['same'] === null ? null : 1 * number_format(division($user_max - $online_ratio['same'], $online_ratio['same']), 4, '.', '')
			),
			'serviceMax' => array(
				'max' => $traffic_stat_service_max,
				'link' => $serviceMax_ratio['link'] === null ? null : 1 * number_format(division($traffic_stat_service_max - $serviceMax_ratio['link'], $serviceMax_ratio['link']), 4, '.', ''),
				'same' => $serviceMax_ratio['same'] === null ? null : 1 * number_format(division($traffic_stat_service_max - $serviceMax_ratio['same'], $serviceMax_ratio['same']), 4, '.', '')
			),
			'serviceSum' => array(
				'max' => $operation_stat_sum,
				'link' => $serviceSum_ratio['link'] === null ? null : 1 * number_format(division($operation_stat_sum - $serviceSum_ratio['link'], $serviceSum_ratio['link']), 4, '.', ''),
				'same' => $serviceSum_ratio['same'] === null ? null : 1 * number_format(division($operation_stat_sum - $serviceSum_ratio['same'], $serviceSum_ratio['same']), 4, '.', '')
			),
			'detail' => $detail
		);
		
		if (! empty($this->_timemode))
		{
			switch ($this->_timemode)
			{
				case 1:
					$expires = 0;
				break;
				case 2:
				case 3:
				case 5:
					$expires = strtotime(date('Y-m-d', strtotime('+1 day'))) - time();
				break;
				case 4:
					$expires = strtotime(date('Y-m-d', strtotime('next Monday'))) - time();
				break;
				case 6:
					$expires = strtotime(date('Y-m-01', strtotime('+1 month'))) - time();
				break;
			}
			cache::set($cache_key, $data, $expires);
		}
		
		return new json(json::OK, 'ok', $data);
	}

	/**
	 * 移动端CDS详情接口
	 *
	 * @return \framework\core\response\json
	 */
	function detail()
	{
		$sn = $this->post('sn', array(), 'explode:",","?"', 'a');
		if (empty($sn))
		{
			return new json(json::FAILED, 'sn不能为空');
		}
		
		$sn = array_shift($sn);
		
		if (! empty($this->_timemode))
		{
			$cache_key = 'api_detail_' . $this->_duration . '_' . $this->_timemode . '_' . $sn;
			if (request::php_sapi_name() == 'web')
			{
				$response = cache::get($cache_key);
				if (! empty($response))
				{
					if ($this->post('debug') != 1)
					{
						return new json(json::OK, null, $response);
					}
				}
				else
				{
					return new json(3, '生成报表大约需要10分钟,请稍后...');
				}
			}
		}
		
		$algorithm = new algorithm($this->_startTime, $this->_endTime, $this->_duration_second);
		$ratio = new ratio($this->_timemode);
		$ratio->setDuration($this->_duration_second);
		
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
		
		// 服务流速，缓存回源，代理缓存回源
		$traffic_stat_cache_proxy = $algorithm->traffic_stat_service_cache_proxy($sn);
		
		$cp_service = $algorithm->CPService($sn, 5);
		
		$tableName = 'operation_stat_sn_class_category_' . $this->_duration_second;
		$operation_stat = $this->model($tableName)
			->where('time>=? and time<?', array(
			$this->_startTime,
			$this->_endTime
		))
			->where('sn=?', array(
			substr($sn, 3)
		))
			->order('service', 'desc')
			->group('class,category')
			->select(array(
			'class',
			'category',
			'cache' => 'sum(cache_size)',
			'proxy_cache' => 'sum(proxy_cache_size)',
			'service' => 'sum(service_size)'
		));
		$resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0
		);
		$http_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0
		);
		$mobile_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0
		);
		$videoDemand_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0
		);
		$videoLive_resource = array(
			'cache' => 0,
			'proxy_cache' => 0,
			'service' => 0
		);
		foreach ($operation_stat as $stat)
		{
			$resource['cache'] += $stat['cache'];
			$resource['proxy_cache'] += $stat['proxy_cache'];
			$resource['service'] += $stat['service'];
			if ($stat['class'] == 0)
			{
				$http_resource['cache'] += $stat['cache'];
				$http_resource['proxy_cache'] += $stat['proxy_cache'];
				$http_resource['service'] += $stat['service'];
			}
			else if ($stat['class'] == 1)
			{
				$mobile_resource['cache'] += $stat['cache'];
				$mobile_resource['proxy_cache'] += $stat['proxy_cache'];
				$mobile_resource['service'] += $stat['service'];
			}
			else if ($stat['class'] == 2)
			{
				if ($stat['category'] >= 128)
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
			'videoLive' => $videoLive_resource
		);
		
		$cp_resource = array();
		foreach ($operation_stat as $stat)
		{
			$categoryName = $this->getCategory($stat);
			if ($stat['class'] == 0)
			{
				$className = 'http';
			}
			else if ($stat['class'] == 1)
			{
				$className = 'mobile';
			}
			else if ($stat['class'] == 2)
			{
				if ($stat['category'] >= 128)
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
				$cp_resource[$className][$categoryName]['cache'] = $stat['cache'] * 1;
			}
			
			if (isset($cp_resource[$className][$categoryName]['service']))
			{
				$cp_resource[$className][$categoryName]['service'] += $stat['service'];
			}
			else
			{
				$cp_resource[$className][$categoryName]['service'] = $stat['service'] * 1;
			}
			
			if (isset($cp_resource[$className][$categoryName]['proxy_cache']))
			{
				$cp_resource[$className][$categoryName]['proxy_cache'] += $stat['proxy_cache'];
			}
			else
			{
				$cp_resource[$className][$categoryName]['proxy_cache'] = $stat['proxy_cache'] * 1;
			}
		}
		
		foreach ($cp_resource as &$v)
		{
			uasort($v, function ($a, $b)
			{
				if ($a['service'] == $b['service'])
				{
					return 0;
				}
				return $a['service'] > $b['service'] ? - 1 : 1;
			});
		}
		unset($v);
		
		foreach ($cp_resource as $className => &$v)
		{
			if (count($v) > 5)
			{
				$other = array(
					'cache' => 0,
					'service' => 0,
					'proxy_cache' => 0
				);
				while (count($v) > 5)
				{
					$flag = array_pop($v);
					$other['cache'] += $flag['cache'];
					$other['service'] += $flag['service'];
					$other['proxy_cache'] += $flag['proxy_cache'];
				}
				$v['其它'] = $other;
			}
		}
		
		$data = array(
			'main' => array(
				'user' => array(
					'max' => $user['max'],
					'link' => $user_ratio['link'] === null ? null : 1 * number_format(division($user['max'] - $user_ratio['link'], $user_ratio['link']), 4, '.', ''),
					'same' => $user_ratio['same'] === null ? null : 1 * number_format(division($user['max'] - $user_ratio['same'], $user_ratio['same']), 4, '.', '')
				),
				'service' => array(
					'max' => $service['max'],
					'link' => $service_ratio['link'] === null ? null : 1 * number_format(division($service['max'] - $service_ratio['link'], $service_ratio['link']), 4, '.', ''),
					'same' => $service_ratio['same'] === null ? null : 1 * number_format(division($service['max'] - $service_ratio['same'], $service_ratio['same']), 4, '.', '')
				)
			),
			'cds_service_cache' => $cds_velocity_gain, // 流速增益
			'cds_traffic_stat' => $traffic_stat_cache_proxy, // 流速详细
			'cds_cp_traffic_stat' => $cp_service, // 分cp服务流速
			'online' => $user['detail'], // 用户趋势图
			'resource' => $resource, // 资源引入情况
			'class_resource' => $class_resource, // 分类型资源引入 分类型服务流量
			'cp_resource' => $cp_resource
		); // 分cp资源引入详情

		
		if (! empty($cache_key))
		{
			switch ($this->_timemode)
			{
				case 1:
					$expires = 0;
				break;
				case 2:
				case 3:
				case 5:
					$expires = strtotime(date('Y-m-d', strtotime('+1 day'))) - time();
				break;
				case 4:
					$expires = strtotime(date('Y-m-d', strtotime('next Monday'))) - time();
				break;
				case 6:
					$expires = strtotime(date('Y-m-01', strtotime('+1 month'))) - time();
				break;
			}
			cache::set($cache_key, $data, $expires);
		}
		
		return new json(json::OK, 'ok', $data);
	}
}
