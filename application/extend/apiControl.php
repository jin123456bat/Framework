<?php
namespace application\extend;
use framework\core\control;
use framework\core\request;
use framework\core\response\json;
use framework\core\application;

/**
 * api请求基础control
 * @author fx
 *
 */
class apiControl extends control
{
	private $_partner = array(
		'wechat' => '8da3ab6644ab91f059d1706bb69d0080c8184706'
	);
	
	function initlize()
	{
		$partner = request::post('partner',NULL,'trim');
		$key = isset($this->_partner[$partner])?$this->_partner[$partner]:'';
		$timestamp = request::post('timestamp',time(),'trim|int','i');
		$sign = request::post('sign',NULL,'trim');
		$data = request::post('data',array(),'','a');
		$signed = $this->sign($partner, $timestamp, $data, $key);
		if (!in_array($sign,$signed))
		{
			return new json(json::FAILED,'签名失败');
		}
	}
	
	function sign($partner,$timestamp,$data,$key)
	{
		if (is_array($data) && !empty($data))
		{
			ksort($data);
			reset($data);
			$data1 = strtolower(http_build_query($data,NULL,'&',PHP_QUERY_RFC3986));
			$data2 = strtolower(http_build_query($data,NULL,'&',PHP_QUERY_RFC1738));
		}
		else
		{
			$data1 = $data2 = '';
		}
		$result1 = strtoupper(md5($timestamp.$data1.$partner.$key));
		$result2 = strtoupper(md5($timestamp.$data2.$partner.$key));
		return array($result1,$result2);
	}
	
	/**
	 * 获取通过接口传递的内容
	 * @param unknown $name
	 * @param unknown $default
	 * @param unknown $filter
	 * @param string $type
	 * @return mixed|string|boolean|number|\core\StdClass|\core\unknown
	 */
	function post($name,$default = NULL,$filter = NULL,$type = 's')
	{
		$requestData = request::post('data',$default,NULL,'a');
		if (isset($requestData[$name]))
		{
			$data = self::setVariableType($requestData[$name],$type);
				
			if (is_string($filter))
			{
				$filters = explode('|', $filter);
				foreach ($filters as $filter_t)
				{
					if (is_callable($filter_t))
					{
						$data = call_user_func($filter_t, $data);
					}
					else 
					{
						$filterClass = application::load('filter');
						if (is_callable(array($filterClass,$filter_t)))
						{
							$data = call_user_func(array($filterClass,$filter_t),$data);
						}
					}
				}
				return $data;
			}
			else
			{
				if (is_callable($filter))
				{
					return call_user_func($filter, $data);
				}
				return $data;
			}
		}
		else
		{
			return $default;
		}
	}
}