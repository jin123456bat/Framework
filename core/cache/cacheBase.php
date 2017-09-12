<?php
namespace framework\core\cache;

use framework\core\base;

class cacheBase extends base
{

	/**
	 * 根据key计算服务器的序号
	 * 
	 * @param unknown $name        
	 * @param int $max_num        
	 */
	function getServerNo($name, $max_num)
	{
		$checksum = crc32(md5($name));
		$value = intval(sprintf("%u\n", $checksum));
		return $value % $max_num;
	}
}