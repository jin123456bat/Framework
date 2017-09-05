<?php
namespace framework\data\line;

/**
 * 队列
 * FIFO
 * @author jin
 *
 */
class queue extends line
{	
	/**
	 * 在链表的结尾添加数据
	 * @param mixed $data
	 */
	function push($data)
	{
		$temp = new \stdClass();
		$temp->data = $data;
		$temp->prev = NULL;
		$temp->next = NULL;
		if (empty($this->_head))
		{
			$this->_head = &$temp;
			$this->_tail = &$temp;
			$this->_pointer = &$temp;
		}
		else
		{
			$temp->prev = &$this->_tail;
			
			$this->_tail->next = &$temp;
			$this->_tail = &$temp;
		}
		$this->_length++;
	}
	
	/**
	 * 删除链表中的第一个元素
	 * 返回被删除的元素
	 */
	function shift()
	{
		if ($this->length()>0)
		{
			if ($this->length()==1)
			{
				$this->_head = NULL;
				$this->_tail = NULL;
				$this->_pointer = NULL;
			}
			else 
			{
				$result = $this->_head->data;
				$this->_head = &$this->_head->next;
				$this->_pointer = &$this->_head;
				$this->_head->prev = NULL;
				$this->_length--;
				return $result;
			}
		}
		return NULL;
	}
}