<?php
namespace framework\vendor;

class encryption
{

	/**
	 * 获取一个唯一的ID，长度32位
	 * 
	 * @param string $prefix        
	 * @return string
	 */
	public static function unique_id($prefix = '')
	{
		return $prefix . md5(uniqid(mt_rand(), true));
	}
	
	/**
	 * guid
	 * @return string
	 */
	public static function guid()
	{
		mt_srand((double) microtime() * 10000); // optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);
		$uuid = chr(123) . substr($charid, 0, 8) . $hyphen . substr($charid, 8, 4) . $hyphen . substr($charid, 12, 4) . $hyphen . substr($charid, 16, 4) . $hyphen . substr($charid, 20, 12) . chr(125);
		return $uuid;
	}
}