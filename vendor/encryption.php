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
}