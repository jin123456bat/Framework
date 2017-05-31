<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\base;
use framework\core\model;

class mysql extends base implements cache
{

	/**
	 * 设置缓存数据，假如数据的key已经存在了则更新
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::set()
	 */
	public function set($name, $value, $expires = 0)
	{
		// TODO Auto-generated method stub
		$result = $this->model('cache')
			->duplicate(array(
			'createtime' => date('Y-m-d H:i:s'),
			'expires' => $expires,
			'value' => $value
		))
			->insert(array(
			'unique_key' => $name,
			'createtime' => date('Y-m-d H:i:s'),
			'expires' => $expires,
			'value' => $value
		));
		return $result;
	}

	/**
	 * 获取缓存数据，自动判断数据是否有效
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::get()
	 */
	public function get($name)
	{
		// TODO Auto-generated method stub
		$value = $this->model('cache')
			->where('unique_key=? and (UNIX_TIMESTAMP(createtime)+expires>UNIX_TIMESTAMP(now()) or expires=?)', array(
			$name,
			0
		))
			->scalar('value');
		return $value;
	}

	/**
	 * 获取key对应的数据，不判断数据是否有效
	 *
	 * @param unknown $name        	
	 * @return unknown
	 */
	public function find($name)
	{
		$value = $this->model('cache')
			->where('unique_key=?', array(
			$name
		))
			->find();
		return $value;
	}
	
	/**
	 * 自增
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name,$amount = 1)
	{
		return $this->model('cache')->where('unique_key=?',array($name))->limit(1)->update('value+=',$amount);
	}
	
	/**
	 * 自减
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name,$amount = 1)
	{
		return $this->model('cache')->where('unique_key=?',array($name))->limit(1)->update('value-=',$amount);
	}
	
	/**
	 * 判断是否存在
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::has()
	 */
	public function has($name)
	{
		return !empty($this->model('cache')->where('unique_key=?',array($name))->limit(1)->find());
	}
}
