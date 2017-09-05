<?php 
namespace framework\data\line;

/**
 * 链表
 * @author jin
 *
 */
class chain extends line
{
	/**
	 * 数据填充的位置  左边
	 * @var unknown
	 */
	CONST FILL_LEFT = true;
	
	/**
	 * 数据填充的位置 右边
	 * @var unknown
	 */
	CONST FILL_RIGHT = false;
	
	function __construct($array = array())
	{
		foreach ($array as $value)
		{
			$this->push($value);
		}
	}
	
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
	 * 删除链表中的内容
	 * 删除的时候请注意，对比内容相同使用的是==  而非===
	 * @param mixed $data
	 * @param int $limit 删除的个数 0代表删除所有
	 */
	function remove($data,$limit = 0)
	{
		if ($this->length()>0)
		{
			$count = $limit;
			for($temp = $this->_head;$temp!=NULL;$temp = $temp->next)
			{
				if (!empty($limit))
				{
					if (empty($count))
					{
						break;
					}
				}
				
				if ($temp->data == $data)
				{
					if ($temp->prev !== NULL)
					{
						if ($temp->next !== NULL)
						{
							//删除中间的指针
							$temp->prev->next = &$temp->next;
							$temp->next->prev = &$temp->prev;
						}
						else
						{
							//删除尾指针
							$this->_tail = &$temp->prev;
							$this->_tail->next = NULL;
						}
					}
					else
					{
						//删除头指针
						$this->_head = &$temp->next;
						$this->_head->prev = NULL;
						$this->_pointer = &$this->_head;
					}
					
					$count--;
					$this->_length--;
				}
			}
		}
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
				$this->_head = NULL;
				$this->_tail = NULL;
				$this->_pointer = NULL;
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
	
	/**
	 * 向链表的头添加元素
	 */
	function unshift($data)
	{
		$temp = new \stdClass();
		$temp->data = $data;
		$temp->prev = NULL;
		$temp->next = NULL;
		if (empty($this->_head))
		{
			$this->_head = &$temp;
			$this->_tail = &$temp;
		}
		else
		{
			$temp->next = &$this->_head;
			
			$this->_head->prev = &$temp;
			$this->_head = &$temp;
		}
		$this->_pointer = &$this->_head;
		$this->_length++;
	}
	
	/**
	 * 逆转
	 */
	function reverse()
	{
		if ($this->length()>1)
		{
			for($temp = $this->_head;$temp!=NULL;$temp = $temp->prev)
			{
				$t = &$temp->prev;
				$temp->prev = &$temp->next;
				$temp->next = &$t;
			}
			
			$t = &$this->_head;
			$this->_head = &$this->_tail;
			$this->_tail = &$t;
			
			$this->_pointer = &$this->_head;
		}
	}
	
	/**
	 * 填充
	 */
	function fill($length,$data,$pos = self::FILL_RIGHT)
	{
		if ($length > $this->length())
		{
			$count = $length - $this->length();
			while ($count--)
			{
				if ($pos)
				{
					$this->unshift($data);
				}
				else
				{
					$this->push($data);
				}
			}
		}
	}
	
	/**
	 * 将链表拆分成多个链表
	 * @return array(chain)
	 */
	function chunk($size)
	{
		$result = array();
		$array = array();
		for($temp = $this->_head;$temp!=NULL;$temp = $temp->next)
		{
			$array[] = $temp->data;
			if (count($array) == $size)
			{
				$result[] = new chain($array);
				$array = array();
			}
		}
		
		if (!empty($array))
		{
			$result[] = new chain($array);
		}
		
		return $result;
	}
	
	/**
	 * 链表组合
	 * @param line|array $chain
	 */
	function combine($chain)
	{
		foreach ($chain as $value)
		{
			$this->push($value);
		}
	}
	
	/**
	 * 判断是否存在指定元素
	 * 判断使用== 而非===
	 * @param mixed $data
	 * @return boolean
	 */
	function exist($data)
	{
		for($temp = $this->_head;$temp!=NULL;$temp = $temp->next)
		{
			if ($temp->data == $data)
			{
				return true;
			}
		}
		return false;
	}
	
	/**
	 * 搜索下标，从0开始，不存在返回false
	 * 判断使用== 而非===
	 * @param mixed $data
	 * @return number|boolean
	 */
	function search($data)
	{
		$num = 0;
		for($temp = $this->_head;$temp!=NULL;$temp = $temp->next)
		{
			if ($temp->data == $data)
			{
				return $num;
			}
			$num++;
		}
		return false;
	}
	
	/**
	 * 去除重复的元素
	 */
	function unique()
	{
		$data=array();
		for($temp = $this->_head;$temp!=NULL;$temp = $temp->next)
		{
			$k = serialize($temp->data);
			if (!isset($data[$k]))
			{
				$data[$k] = 1;
			}
			else
			{
				if ($temp->prev !== NULL)
				{
					if ($temp->next !== NULL)
					{
						//删除中间的指针
						$temp->prev->next = &$temp->next;
						$temp->next->prev = &$temp->prev;
					}
					else
					{
						//删除尾指针
						$this->_tail = &$temp->prev;
						$this->_tail->next = NULL;
					}
				}
				else
				{
					//删除头指针
					$this->_head = &$temp->next;
					$this->_head->prev = NULL;
					$this->_pointer = &$this->_head;
				}
				
				$this->_length--;
			}
		}
		unset($data);
	}
}