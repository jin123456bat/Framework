<?php
namespace application\extend;
use framework\core\request;
use framework\core\response\json;
use framework\core\application;

/**
 * api请求基础control
 * @author fx
 *
 */
class apiControl extends BaseControl
{
	private $_partner = array(
		'wechat' => '8da3ab6644ab91f059d1706bb69d0080c8184706'
	);
	
	function initlize()
	{
		if (request::php_sapi_name() == 'web')
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
		parent::initlize();
	}
	
	
	
	function sign($partner,$timestamp,$data,$key)
	{
		if (is_array($data) && !empty($data))
		{
			ksort($data);
			reset($data);
			//$data1 = strtolower(http_build_query($data,NULL,'&',PHP_QUERY_RFC3986));
			//$data2 = strtolower(http_build_query($data,NULL,'&',PHP_QUERY_RFC1738));
			$data1 = '';
			foreach ($data as $index=>$value)
			{
				$data1 .= $index.'='.($value).'&';
			}
			$data1 = strtolower(rtrim($data1,'&'));
			
			$data2 = '';
			foreach ($data as $index=>$value)
			{
				$data2 .= $index.'='.($value).'&';
			}
			$data2 = strtolower(rtrim($data1,'&'));
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
		if (request::php_sapi_name() == 'cli')
		{
			return request::param($name,$default,$filter,$type);
		}
		else
		{
			$requestData = request::post('data',$default,NULL,'a');
			if (isset($requestData[$name]))
			{
				$data = $requestData[$name];
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
							else
							{
								list($func,$param) = explode(':', $filter);
								if (is_callable($func))
								{
									$pattern = '$["\'].["\']$';
									if (preg_match_all($pattern, $param,$matches))
									{
										$params = array_map(function($param) use($data){
											if (trim($param,'\'"') == '?')
											{
												return $data;
											}
											return trim($param,'\'"');
										}, $matches[0]);
										$data = call_user_func_array($func, $params);
									}
								}
							}
						}
					}
					return self::setVariableType($data,$type);
				}
				else
				{
					if (is_callable($filter))
					{
						return call_user_func($filter, $data);
					}
					return self::setVariableType($requestData[$name],$type);
				}
			}
			else
			{
				return $default;
			}
		}
	}
	
	protected function setTime()
	{
		$this->_timemode = $this->post('timemode');
	
		switch ($this->_timemode)
		{
			case '1':
				//最近24小时
				$timestamp = (floor(time() / (5*60)) - 1) * 5*60;
				$this->_endTime = date('Y-m-d H:i:s',$timestamp);
				$this->_startTime = date('Y-m-d H:i:s',strtotime('-24 hour',strtotime($this->_endTime)));
				break;
			case '2':
				//昨天
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-1 day'));
				$this->_endTime = date('Y-m-d 00:00:00');
				break;
			case '3':
				//上周
				$this->_startTime = date('Y-m-d 00:00:00', strtotime('last week'));
				$this->_endTime = date('Y-m-d 00:00:00',strtotime('this week'));
				break;
				//近7天
			case '4':
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-7 day'));
				$this->_endTime = date('Y-m-d 00:00:00');
				break;
			case '5':
				//最近30天
				$this->_endTime = date('Y-m-d 00:00:00');
				$this->_startTime = date('Y-m-d 00:00:00',strtotime('-30 day'));
				break;
			case '6':
				//上月
				$this->_endTime = date('Y-m-1 00:00:00');
				$this->_startTime = date('Y-m-1 00:00:00',strtotime('last month'));
				break;
			default:
				//自定义时间
				$this->_timemode = NULL;
				$this->_startTime = request::param('starttime');
				$this->_endTime = request::param('endtime');
				break;
		}
	
		if (strtotime($this->_startTime) === false)
		{
			return new json(json::FAILED,'开始时间错误');
		}
		if (strtotime($this->_endTime) ===  false)
		{
			return new json(json::FAILED,'结束时间错误');
		}
		if (strtotime($this->_startTime) >= strtotime($this->_endTime))
		{
			return new json(json::FAILED,'开始时间不能大于等于结束时间');
		}
		$this->_duration = $this->post('duration');
		switch ($this->_duration)
		{
			case 'minutely':$this->_duration_second = 60*5;break;
			case 'hourly':$this->_duration_second = 60*60;break;
			case 'daily':$this->_duration_second = 60*60*24;break;
			default:
				return new json(json::FAILED,'duration参数错误');
		}
	}
}