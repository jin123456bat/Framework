<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\filter;

class json extends response
{
	const OK = 1;
	
	const FAILED = 0;
	
	/**
	 * @param unknown $code 信息代码
	 * @param unknown $result 信息代码的描述
	 * @param unknown $data 附加数据
	 * @param number $cache 缓存时间，默认不缓存
	 * @param string $encode json中的汉字是否编码
	 */
	function __construct($code,$result = NULL,$data = NULL,$cache = 0,$encode = false)
	{
		parent::__construct();
		if (!(is_array($code) || is_object($code)))
		{
			$code = array(
				'code' => $code,
				'result' => $result
			);
			
			if ($data !== NULL)
			{
				$code['data'] = $data;
			}
		}
		if ($encode)
		{
			$content_string = json_encode($code);
		}
		else
		{
			$content_string = urldecode(json_encode($this->urlencode($code)));
		}
		$this->setBody($content_string);
		
		$this->setHeader('Content-Type','application/json');
		
		$this->setHeader('Expires',date('D, d M Y H:i:s ',time()+filter::int($cache)).'GMT');
		$this->setHeader('Cache-Control','max-age='.filter::int($cache));	
	}
	
	private function urlencode($data)
	{
		if (is_array($data))
		{
			foreach ($data as $index=>$value)
			{
				$data[$index] = $this->urlencode($value);
			}
		}
		else
		{
			return urlencode($data);
		}
	}
}