<?php
namespace application\extend;

class cache extends \framework\core\cache
{

	/**
	 * 当数据get出来的时候，把结果进行一次函数
	 *
	 * @param string $name        	
	 * @param mixed $value        	
	 * @return mixed
	 */
	static function set($name, $value, $expires = 0)
	{
		$value = json_encode($value, true);
		return parent::set($name, $value, $expires);
	}

	/**
	 * 当数据set的时候，吧数据进行一次函数
	 *
	 * @param unknown $name        	
	 * @return string
	 */
	static function get($name)
	{
		$value = parent::get($name);
		return json_decode($value, true);
	}
}
