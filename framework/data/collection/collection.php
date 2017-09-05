<?php
namespace framework\data\collection;

use framework\data\data;
use framework\data\line\chain;

/**
 * 集合
 * 无序性，唯一性
 * @author jin
 *
 */
class collection extends data implements \Iterator
{
	public $_chain;
	
	/**
	 * 参数不能限制array
	 * @param array $array
	 */
	function __construct($array = array())
	{
		foreach ($array as $value)
		{
			$this->push($value);
		}
		$this->_chain=new chain();
	}
	
	/**
	 * 往集合中添加元素
	 * 已经存在的元素无法添加
	 * @param mixed $value
	 */
	function push($value)
	{
		if ($this->_chain->exist($value))
		{
			$this->_chain->push($value);
		}
	}
	
	/**
	 * 删除集合中的元素
	 * @param mixed $value
	 */
	function remove($value)
	{
		$this->_chain->remove($value,1);
	}
	
	/**
	 * 判断是否和集合相同
	 * 内容相同即可
	 * @param collection $collection
	 * @return boolean
	 */
	function equal(collection $collection)
	{
		foreach ($collection as $value)
		{
			if (!$this->_chain->exist($value))
			{
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 判断数据是否已经存在集合中了
	 * @param unknown $value
	 */
	function exist($value)
	{
		return $this->_chain->exist($value);
	}
	
	/**
	 * 集合长度
	 */
	function length()
	{
		return $this->_chain->length();
	}
	
	/**
	 * 差集
	 * @return collection
	 */
	function diff(collection $collection)
	{
		$result = array();
		foreach ($collection as $value)
		{
			if (!$this->exist($value))
			{
				$result[] = $value;
			}
		}
		return new collection($result);
	}
	
	/**
	 * 交集
	 * @param collection $collection
	 * @return collection
	 */
	function intersect(collection $collection)
	{
		$result = array();
		foreach ($collection as $value)
		{
			if ($this->exist($value))
			{
				$result[] = $value;
			}
		}
		return new collection($result);
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::current()
	 */
	public function current()
	{
		// TODO Auto-generated method stub
		return $this->_chain->current();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::next()
	 */
	public function next()
	{
		// TODO Auto-generated method stub
		$this->_chain->next();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::key()
	 */
	public function key()
	{
		// TODO Auto-generated method stub
		return $this->_chain->key();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::valid()
	 */
	public function valid()
	{
		// TODO Auto-generated method stub
		return $this->_chain->valid();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::rewind()
	 */
	public function rewind()
	{
		// TODO Auto-generated method stub
		$this->_chain->rewind();
	}

}