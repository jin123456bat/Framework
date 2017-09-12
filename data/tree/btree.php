<?php
namespace framework\data\tree;

use framework\data\line\stack;

/**
 * 二叉树
 * @author jin
 *
 */
class btree extends tree
{
	/**
	 * 添加节点的时候的数据堆栈
	 * @var stack
	 */
	private $_stack;
	/**
	 * 添加数据的时候的节点指针
	 * @var unknown
	 */
	private $_pointer = NULL;
	/**
	 * 左节点还是右节点
	 * 0左 1右
	 * @var integer
	 */
	private $_left_or_right = 0;
	
	function __construct()
	{
		$this->_stack = new stack();
	}
	
	/**
	 * 往树中添加数据
	 * 先左在右
	 * @param mixed $data 默认为NULL不添加数据
	 */
	function push($data = NULL)
	{
		if ($data === NULL)
		{
			if ($this->_pointer!==NULL)
			{
				if (!isset($this->_pointer->left_visit))
				{
					$this->_pointer->left_visit = true;
				}
				else if (!isset($this->_pointer->right_visit))
				{
					$this->_pointer->right_visit = true;
				}
				else
				{
					$this->_pointer = $this->_stack->pop();
				}
			}
		}
		else
		{
			if (empty($this->_root))
			{
				$node = $this->node($data);
				$this->_root = &$node;
				$this->_pointer = $this->_root;
				$this->_left_or_right = 0;
				$this->_length++;
				$this->_stack->push($this->_root);
			}
			else
			{
				if ($data !== NULL)
				{
					$node = $this->node($data);
					if ($this->_left_or_right === 0)
					{
						$this->_pointer->left = &$node;
						$this->_pointer = $this->_pointer->left;
					}
					else if ($this->_left_or_right === 1)
					{
						$this->_pointer->right = &$node;
						$this->_pointer = $this->_pointer->right;
					}
					$this->_stack->push($this->_pointer);
				}
				else
				{
					if ($this->_left_or_right === 1)
					{
						do{
							$node = $this->_stack->pop();
							$this->_pointer = &$node;
						}while (!empty($this->_pointer->right));
						$this->_left_or_right = 1;
					}
					else if ($this->_left_or_right === 0)
					{
						$this->_left_or_right = 1;
					}
				}
			}
		}
	}
		
	/**
	 * 创建一个节点
	 * @param mixed $data
	 * @return \stdClass
	 */
	protected function node($data)
	{
		$temp = new \stdClass();
		$temp->data = $data;
		$temp->left = NULL;
		$temp->right = NULL;
		return $temp;
	}
	
	/**
	 * 先序遍历
	 * @param $node 遍历开始的节点  默认为根节点
	 */
	function preIterator(\stdClass $node = NULL)
	{
		if ($node === NULL)
		{
			$node = &$this->_root;
		}
		
		$stack = new stack();
		
		$array = array();
		$temp = &$node;
		while ($temp !== NULL || $stack->length()>0)
		{
			if ($temp!==NULL)
			{
				$stack->push($temp);
				$array[] = $this->get($temp);
				$temp = &$temp->left;
			}
			else
			{
				$temp = $stack->pop();
				$temp = &$temp->right;
			}
		}
		return $array;
	}
	
	/**
	 * 中序遍历
	 * @param \stdClass $node 遍历开始的节点 默认为根节点
	 * @return NULL[]
	 */
	function inIterator(\stdClass $node = NULL)
	{
		if ($node === NULL)
		{
			$node = $this->_root;
		}
		
		$stack = new stack();
		
		$array = array();
		$temp = &$node;
		while ($temp !== NULL || $stack->length()>0)
		{
			if ($temp!==NULL)
			{
				$stack->push($temp);
				$temp = &$temp->left;
			}
			else
			{
				$temp = $stack->pop();
				$array[] = $this->get($temp);
				$temp = &$temp->right;
			}
		}
		return $array;
	}
	
	/**
	 * 后序遍历
	 * @param \stdClass $node 遍历开始的节点 默认为根节点
	 * @return NULL[]
	 */
	function postIterator(\stdClass $node = NULL)
	{
		if ($node === NULL)
		{
			$node = $this->_root;
		}
		
		return $this->postIteratorRecursion($node);
	}
	
	/**
	 * 递归式后序遍历
	 * @param \stdClass $node 遍历开始的节点
	 */
	private function postIteratorRecursion(\stdClass $node = NULL)
	{
		static $array = array();
		if ($node !== NULL)
		{
			$this->postIteratorRecursion($node->left);
			
			$this->postIteratorRecursion($node->right);
			
			$array[] = $this->get($node);
		}
		return $array;
	}
}