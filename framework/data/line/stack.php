<?php
namespace framework\data\line;

/**
 * 栈
 * FILO
 * @author jin
 *
 */
class stack extends line
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
	 * 删除链表中的尾
	 * 只删除一个
	 * 返回被删除的元素
	 * @return mixed
	 */
	function pop()
	{
		if ($this->length()>0)
		{
			if ($this->length()==1)
			{
				$result = $this->_head->data;
				$this->_head = NULL;
				$this->_tail = NULL;
				$this->_pointer = NULL;
				$this->_length--;
				return $result;
			}
			else
			{
				$result = $this->_tail->data;
				$this->_tail = &$this->_tail->prev;
				$this->_tail->next = NULL;
				$this->_length--;
				return $result;
			}
		}
		return NULL;
	}
}