<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\filter;

class json extends response
{

	const OK = 1;

	const FAILED = 0;

	/**
	 *
	 * @param unknown $code
	 *        信息代码
	 * @param unknown $result
	 *        信息代码的描述
	 * @param unknown $data
	 *        附加数据
	 * @param number $cache
	 *        缓存时间，默认不缓存
	 * @param string $encode
	 *        json中的汉字是否编码
	 */
	function __construct($code, $result = null, $data = null, $cache = 0, $encode = false)
	{
		parent::__construct();
		if (! (is_array($code) || is_object($code)))
		{
			$code = array(
				'code' => $code,
				'result' => $result
			);
			
			if ($data !== null)
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
			$content_string = self::json_encode_ex($code);
		}
		$this->setBody($content_string);
		
		$this->setContentType('application/json');
		
		$this->setHeader('Expires', date('D, d M Y H:i:s ', time() + filter::int($cache)) . 'GMT');
		$this->setHeader('Cache-Control', 'max-age=' . filter::int($cache));
	}

	/**
	 * 对变量进行 JSON 编码
	 * 
	 * @param
	 *        mixed value 待编码的 value ，除了resource 类型之外，可以为任何数据类型，该函数只能接受 UTF-8 编码的数据
	 * @return string 返回 value 值的 JSON 形式
	 */
	public static function json_encode_ex($value)
	{
		if (version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			$str = json_encode($value);
			$str = preg_replace_callback("#\\\u([0-9a-f]{4})#i", function ($matchs) {
				return iconv('UCS-2BE', 'UTF-8', pack('H4', $matchs[1]));
			}, $str);
			return $str;
		}
		else
		{
			return json_encode($value, JSON_UNESCAPED_UNICODE);
		}
	}
}
