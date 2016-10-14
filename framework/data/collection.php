<?php
namespace framework\data;

/**
 * 集合类基类
 * @author fx
 * @desc
 * (1) 确定性：对于任意一个元素,要么它属于某个指定集合,要么它不属于该集合,二者必居其一.
 * (2) 互异性：同一个集合中的元素是互不相同的.
 * (3) 无序性：任意改变集合中元素的排列次序,它们仍然表示同一个集合.
 */
class collection implements \Iterator,\ArrayAccess 
{
	private $_length = 0;
	
	private $_data = NULL;
	
	private $_tail = NULL;
	
	private $_position = NULL;
	
	private $_null = NULL;
	
	function __construct($array = array())
	{
		foreach ($array as $key => $value)
		{
			$this->append($key, $value);
		}
	}
	
	/**
	 * 集合中元素个数
	 * @return number
	 */
	function count()
	{
		return $this->_length;
	}
	
	/**
	 * 判断集合是否为空
	 * @return boolean
	 */
	function isEmpty()
	{
		return $this->count() === 0;
	}
	
	/**
	 * 按照值判断是否存在
	 * @param unknown $value
	 */
	function isExist($value)
	{
		for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
		{
			if($i->getValue() == $value)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 去除重复元素，保留最后第一个元素的key
	 */
	function unique()
	{
		$result = array();
		for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
		{
			$value = $i->getValue();
			if (isset($result[$value]))
			{
				$this->remove($i->getKey());
			}
			else
			{
				$result[$value] = 1;
			}
		}
	}
	
	/**
	 * 在集合的末尾添加一个元素
	 */
	function append($name,$value)
	{
		if ($this->isEmpty())
		{
			$this->_data = new node($name,$value);
			$this->_tail = &$this->_data;
			$this->_position = $this->_data;
			$this->_length++;
		}
		else
		{
			if (!isset($this[$name]))
			{
				$temp = new node($name,$value);
				$temp->setPrev($this->_tail);
				$this->_tail->setNext($temp);
				$this->_tail = &$temp;
				$this->_length++;
			}
		}
	}
	
	/**
	 * 在集合的前面增加一个元素
	 * @param unknown $name
	 * @param unknown $value
	 */
	function prepend($name,$value)
	{
		if ($this->isEmpty())
		{
			$this->_data = new node($name,$value);
			$this->_tail = &$this->_data;
		}
		else
		{
			$temp = new node($name,$value);
			$temp->setNext($this->_data);
			$this->_data->setPrev($temp);
			$this->_data = &$temp;
		}
		$this->_position = $this->_data;
		$this->_length++;
	}
	
	/**
	 * 清空所有元素
	 */
	function clear()
	{
		$this->_length = 0;
	}
	
	/**
	 * 在集合中删除指定元素
	 * @param unknown $name
	 */
	function remove($name)
	{
		if ($this->count()>1)
		{
			for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
			{
				if ($i->getKey() == $name)
				{
					$prev = $i->getPrev();
					$next = $i->getNext();
					
					if ($prev === NULL)
					{
						$this->_data = $next;
						$this->_position = $this->_data;
						$next->setPrev($this->_null);
					}
					else if ($next === NULL)
					{
						$prev->setNext($this->_null);
						$this->_tail = $prev;
					}
					else
					{
						$prev->setNext($next);
						$next->setPrev($prev);
					}
					$this->_length--;
					break;
				}
			}
		}
		else if ($this->count()===1)
		{
			$this->_length = 0;
			$this->_data = NULL;
			$this->_tail = NULL;
			$this->_position = NULL;
		}
	}
	
	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
		{
			if ($i->getKey() == $offset)
			{
				return true;
			}
		}
		return false;
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		// TODO Auto-generated method stub
		for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
		{
			if ($i->getKey() == $offset)
			{
				return $i->getValue();
			}
		}
		return NULL;
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		// TODO Auto-generated method stub
		$flag = false;
		for($i = &$this->_data;$i !== NULL;$i = $i->getNext())
		{
			if ($i->getKey() == $offset)
			{
				$i->setValue($value);
				$flag = true;
				break;
			}
		}
		if (!$flag)
		{
			$this->append($offset, $value);
		}
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		// TODO Auto-generated method stub
		$this->remove($offset);
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::current()
	 */
	public function current()
	{
		// TODO Auto-generated method stub
		return $this->_position->getValue();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::next()
	 */
	public function next()
	{
		// TODO Auto-generated method stub
		$this->_position = $this->_position->getNext();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::key()
	 */
	public function key()
	{
		// TODO Auto-generated method stub
		return $this->_position->getKey();
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::valid()
	 */
	public function valid()
	{
		// TODO Auto-generated method stub
		return $this->_position !== NULL;
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::rewind()
	 */
	public function rewind()
	{
		// TODO Auto-generated method stub
		$this->_position = $this->_data;
	}


}

/**
 * 集合中的节点
 * @author fx
 *
 */
class node
{
	private $_key;
	private $_value;
	
	private $_prev;
	private $_next;
	
	function __construct($key,$value)
	{
		$this->_key = $key;
		$this->_value = $value;
		
		$this->_prev = NULL;
		$this->_next = NULL;
	}
	
	function setPrev(&$node)
	{
		$this->_prev = $node;
	}
	
	function getPrev()
	{
		return $this->_prev;
	}
	
	function setNext(&$node)
	{
		$this->_next = $node;
	}
	
	function getNext()
	{
		return $this->_next;
	}
	
	function setValue($value)
	{
		$this->_value = $value;
	}
	
	function getValue()
	{
		return $this->_value;
	}
	
	function getKey()
	{
		return $this->_key;
	}
	
	function setKey($key)
	{
		$this->_key = $key;
	}
}