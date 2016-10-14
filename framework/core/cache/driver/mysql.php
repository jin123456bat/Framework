<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\base;

class mysql extends base implements cache
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::set()
	 */
	public function set($name, $value, $expires = 5)
	{
		// TODO Auto-generated method stub
		return $this->model('cache')->duplicate(array(
			'createtime' => time(),
			'expires' => $expires,
			'value' => $value,
		))->insert(array(
			'unique_key' => $name,
			'createtime' => time(),
			'expires' => $expires,
			'value' => $value
		));
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::get()
	 */
	public function get($name)
	{
		// TODO Auto-generated method stub
		$value = $this->model('cache')->where('unique_key=? and createtime+expires>UNIX_TIMESTAMP(now())',array($name))->scalar('value');
		return $value;
	}	
}