<?php
namespace framework\core\cache\driver;

use framework\core\cache\cache;
use framework\core\cache\cacheBase;

class mysql extends cacheBase implements cache
{

	private $_model;

	public function __construct()
	{
		$table = $this->table('cache');
		if(!$table->exist())
		{
			$table->field('unique_key')->varchar(32)->comment('唯一键');
			$table->field('createtime')->datetime()->comment('创建时间');
			$table->field('expires')->int()->comment('有效期，0不限制');
			$table->field('value')->longtext()->comment('存储的值，seralize后');
			$table->primary()->add('unique_key');
			$table->index('createtime')->add(array('createtime','expires'));
		}
		
		$this->_model = $this->model('cache');
		//删除过期的缓存
		$this->_model->where('expires!=0 and (UNIX_TIMESTAMP(createtime)+expires<UNIX_TIMESTAMP(now()))')->delete();
	}

	/**
	 * {@inheritDoc}
	 * @see \framework\core\cache\cache::add()
	 */
	public function add($name, $value, $expires = 0)
	{
		if (!$this->has($name))
		{
			if($this->_model->insert(array(
				'unique_key' => md5($name),
				'createtime' => date('Y-m-d H:i:s'),
				'expires' => $expires,
				'value' => serialize($value)
			)))
			{
				return true;
			}
		}
		return false;
	}

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
		if($this->_model->duplicate(array(
			'createtime' => date('Y-m-d H:i:s'),
			'expires' => $expires,
			'value' => serialize($value)
		))->insert(array(
			'unique_key' => md5($name),
			'createtime' => date('Y-m-d H:i:s'),
			'expires' => $expires,
			'value' => serialize($value)
		)))
		{
			return true;
		}
		return false;
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
		$value = $this->_model->where('unique_key=? and (UNIX_TIMESTAMP(createtime)+expires>UNIX_TIMESTAMP(now()) or expires=?)', array(
			md5($name),
			0
		))->scalar('value');
		if ($value === NULL)
		{
			return NULL;
		}
		return unserialize($value);
	}

	/**
	 * 自增
	 * 
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::increase()
	 */
	public function increase($name, $amount = 1)
	{
		$value = $this->get($name);
		$value += $amount;
		return $this->set($name, $value);
	}

	/**
	 * 自减
	 * 
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::decrease()
	 */
	public function decrease($name, $amount = 1)
	{
		$value = $this->get($name);
		$value -= $amount;
		return $this->set($name, $value);
	}

	/**
	 * 判断是否存在
	 * 
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::has()
	 */
	public function has($name)
	{
		return ! empty($this->_model->where('unique_key=?', array(
			md5($name)
		))
			->limit(1)
			->find());
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::remove()
	 */
	public function remove($name)
	{
		if($this->_model->where('unique_key=?', array(
			md5($name)
		))->limit(1)->delete())
		{
			return true;
		}
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\cache\cache::flush()
	 */
	public function flush()
	{
		$this->_model->truncate();
		return true;
	}
}
