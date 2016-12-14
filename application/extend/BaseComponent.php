<?php
namespace application\extend;
use framework\core\component;
use framework\core\http;

class BaseComponent extends component
{
	static function getSnList()
	{
		$url = 'https://cloud.fxdata.cn/fxtv/cds_show.php';
		$response = json_decode(http::get($url));
		if (!empty($response))
		{
			$temp = array();
			foreach ($response as $r)
			{
				if ($r['version'] >= '9.1.0')
				{
					$temp[] = $r['sn'];
				}
			}
			return $temp;
		}
		return array();
	}
	
	function combineSns($sn = array())
	{
		if (empty($sn))
		{
			static $cache = NULL;
			if (empty($cache))
			{
				$cache = self::getSnList();
			}
			return $cache;
		}
		return self::setVariableType($sn,'a');
	}
	
	function formatTimenode($data,$startTime,$endTime,$duration,$defaultValue = 0)
	{
		for ($t_time = $startTime;strtotime($t_time) < strtotime($endTime);$t_time = date('Y-m-d H:i:s',strtotime($t_time)+$duration))
		{
			if (!isset($data[$t_time]))
			{
				$data[$t_time] = $defaultValue;
			}
		}
		return $data;
	}
}