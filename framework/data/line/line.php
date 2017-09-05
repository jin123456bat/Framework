<?php
namespace framework\data\line;

use framework\data\data;

/**
 * 线性结构
 * 链表(chain),队列(queue),栈(stack)都继承此类
 * @author jin
 *
 */
abstract class line extends data implements \Iterator 
{
	/**
	 * 内部指针
	 * @var unknown
	 */
	private $_pointer;
	
	/**
	 * 内部指针的序号
	 * @var unknown
	 */
	private $_key = 0;
	
	/**
	 * 头指针
	 * @var \stdClass
	 */
	protected $_head;
	
	/**
	 * 尾指针
	 * @var \stdClass
	 */
	protected $_tail;
	
	/**
	 * 数据长度
	 * @var int
	 */
	protected $_length;
	
	
	/**
	 * 获取数据长度
	 * @return number
	 */
	function length()
	{
		return $this->_length;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::current()
	 */
	public function current()
	{
		// TODO Auto-generated method stub
		return $this->_pointer->data;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::next()
	 */
	public function next()
	{
		// TODO Auto-generated method stub
		$this->_pointer = &$this->_pointer->next;
		$this->_key++;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::key()
	 */
	public function key()
	{
		// TODO Auto-generated method stub
		return $this->_key;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::valid()
	 */
	public function valid()
	{
		// TODO Auto-generated method stub
		return $this->_pointer!=NULL;
	}
	
	/**
	 * {@inheritDoc}
	 * @see Iterator::rewind()
	 */
	public function rewind()
	{
		// TODO Auto-generated method stub
		$this->_pointer = &$this->_head;
		$this->_key = 0;
	}
	
	/**
	 * 比较2个线性结构是否完全相同
	 * 顺序相同
	 * 内容相同
	 * @param line|array $line
	 */
	function equal($line)
	{
		if ($line instanceof line)
		{
			if ($line->length() != $this->length())
			{
				return false;
			}
		}
		else if (is_array($line))
		{
			if (count($line) != $this->length())
			{
				return false;
			}
		}
		$point = $this->_head;
		foreach ($line as $value)
		{
			if ($value != $point->data)
			{
				return false;;
			}
			$point = $point->next;
		}
		return true;
	}
}