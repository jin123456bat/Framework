<?php
namespace application\extend;

use framework\core\component;
use framework\core\http;

class BaseComponent extends component
{

	static function getSnList()
	{
		$url = 'https://cloud.fxdata.cn/fxtv/cds_show.php';
		$response = json_decode(http::get($url, array(), false), true);
		if (! empty($response))
		{
			$temp = array();
			foreach ($response as $r)
			{
				if (version_compare($r['version'], '9.1.0') != - 1)
				{
					$temp[] = $r['sn'];
				}
			}
			return $temp;
		}
		return array();
	}

	/**
	 * 从缓存钟获取sn列表
	 * 
	 * @return mixed[]|string
	 */
	function getSnListFromCache()
	{
		$sn = \application\extend\cache::get('cacheSnList');
		if (empty($sn))
		{
			return self::getSnList();
		}
		return $sn;
	}

	/**
	 * 获取所有允许的sn列表
	 * 
	 * @param array $sn        
	 * @return string|mixed[]|string|boolean|number|\framework\core\StdClass|\framework\core\unknown
	 */
	function combineSns($sn = array())
	{
		if (empty($sn))
		{
			static $cache = null;
			if (empty($cache))
			{
				$cache = $this->getSnListFromCache();
			}
			return $cache;
		}
		return self::setVariableType($sn, 'a');
	}

	/**
	 * 时间节点补全
	 * 
	 * @param unknown $data        
	 * @param unknown $startTime        
	 * @param unknown $endTime        
	 * @param unknown $duration        
	 * @param number $defaultValue        
	 * @return number
	 */
	function formatTimenode($data, $startTime, $endTime, $duration, $defaultValue = 0)
	{
		for ($t_time = $startTime; strtotime($t_time) < strtotime($endTime); $t_time = date('Y-m-d H:i:s', strtotime($t_time) + $duration))
		{
			if (! isset($data[$t_time]))
			{
				$data[$t_time] = $defaultValue;
			}
		}
		ksort($data);
		return $data;
	}

	/**
	 * 根据duration获取上一个时间点
	 * 
	 * @param datetime $time        
	 * @param int $duration        
	 */
	function getFloorTime($time, $duration)
	{
		switch ($duration)
		{
			case 300:
				return date('Y-m-d H:', strtotime($time)) . (floor(date('i', strtotime($time)) / 5) * 5) . ':00';
			case 1800:
				return date('Y-m-d H:', strtotime($time)) . (floor(date('i', strtotime($time)) / 30) * 30) . ':00';
			case 3600:
				return date('Y-m-d H:00:00', strtotime($time));
			case 7200:
				return date('Y-m-d ', strtotime($time)) . (floor(date('H', strtotime($time)) / 2) * 2) . ':00:00';
			case 86400:
				return date('Y-m-d', strtotime($time)) . ' 00:00:00';
		}
	}

	/**
	 * 根据duration获取下一个时间点
	 * 
	 * @param datetime $time        
	 * @param int $duration        
	 */
	function getCeilTime($time, $duration)
	{
		switch ($duration)
		{
			case 300:
				return date('Y-m-d H:', strtotime($time)) . (ceil(date('i', strtotime($time)) / 5) * 5) . ':00';
			case 1800:
				return date('Y-m-d H:', strtotime($time)) . (ceil(date('i', strtotime($time)) / 30) * 30) . ':00';
			case 3600:
				return date('Y-m-d H:00:00', strtotime('+1 hour', strtotime($time)));
			case 7200:
				return date('Y-m-d ', strtotime($time)) . (ceil(date('H', strtotime($time)) / 2) * 2) . ':00:00';
			case 86400:
				return date('Y-m-d', strtotime('+1 day', strtotime($time))) . ' 00:00:00';
		}
	}
}
