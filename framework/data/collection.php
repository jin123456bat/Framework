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
class collection implements \Iterator
{
	/**
	 * 头指针
	 * @var unknown
	 */
	private $_top = NULL;
	
	/**
	 * 尾指针
	 * @var unknown
	 */
	private $_tail = NULL;
	
	/**
	 * 长度
	 * @var integer
	 */
	private $_length = 0;
	
	/**
	 * 内部遍历器指针
	 * @var unknown
	 */
	private $_position = NULL;
	
	/**
	 * 内部指针距离top的长度
	 * @var integer
	 */
	private $_position_length = 0;
	
	/**
	 * 一些模式变量
	 * @var array
	 */
	private $_mode = array(
		self::CASE_INSENSITIVE => true,//区分大小写
	);
	
	const CASE_INSENSITIVE = 1000;
	
	function __construct($data = NULL,$option = array())
	{
		foreach ($option as $key => $value)
		{
			$this->_mode[$key] = $value;
		}
		
		if (is_array($data))
		{
			foreach ($data as $value)
			{
				$this->append($value);
			}
		}
		else
		{
			$this->append($data);
		}
	}
	
	/**
	 * 设置模式
	 * @param unknown $name
	 * @param unknown $value
	 */
	function setMode($name,$value)
	{
		$this->_mode[$name] = $value;
	}
	
	/**
	 * 获取元素的位置，不存在返回false
	 * 注意返回有可能是0，因为是在第一个
	 * @param unknown $value
	 * @return unknown|boolean
	 */
	function isExist($value)
	{
		for($temp = $this->_top;$temp!=NULL;$temp = $temp->getNext())
		{
			if ($this->_mode[self::CASE_INSENSITIVE]?$temp->getValue() == $value:strtoupper($temp->getValue()) == strtoupper($value))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 在末尾追加一个元素
	 * @param unknown $value
	 */
	function append($value)
	{
		$temp = new node($value);
		if (empty($this->_tail))
		{
			$this->_tail = &$temp;
			$this->_top = &$temp;
			$this->_position = &$temp;
			$this->_length++;
		}
		else
		{
			if (!$this->isExist($value))
			{
				$this->_tail->setNext($temp);
				$temp->setPrev($this->_tail);
				$this->_tail = &$temp;
				$this->_length++;
			}
		}
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
		$this->_position_length++;
	}

	/**
	 * {@inheritDoc}
	 * @see Iterator::key()
	 */
	public function key()
	{
		// TODO Auto-generated method stub
		return $this->_position_length;
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
		$this->_position = $this->_top;
		$this->_position_length = 0;
	}

	
}

/**
 * 集合中的节点
 * @author fx
 */
class node
{
	private $_value;
	
	private $_prev;
	private $_next;
	
	function __construct($value)
	{
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
}