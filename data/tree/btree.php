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
	 * 根据一个数据构造一个节点
	 * @param unknown $data
	 * @return \stdClass
	 */
	protected function node($data)
	{
		$temp = new \stdClass();
		$temp->left = NULL;
		$temp->right = NULL;
		$temp->top = NULL;
		$temp->data = $data;
		return $temp;
	}
	
	/**
	 * 获取节点中的数据
	 * @param \stdClass $node
	 * @return unknown
	 */
	protected function get(\stdClass $node)
	{
		return $node->data;
	}
	
	/**
	 * 往节点中添加数据
	 * 深度优先
	 * 从左往右
	 * @param unknown $data
	 */
	function push($data = NULL)
	{
		static $_insert_pointer = NULL;//添加节点的未知
		static $_left_or_right = 0;//0 = left; 1=right
		static $_height = 0;
		
		if ($data!==NULL)
		{
			$temp = $this->node($data);
			
			if (empty($_insert_pointer))
			{
				$this->_root = &$temp;
				$_insert_pointer = &$temp;
				$this->_height++;
			}
			else
			{
				if ($_left_or_right === 0)
				{
					$_insert_pointer->left = &$temp;
					
					$_height++;
					if ($_height > $this->_height)
					{
						$this->_height = $_height;
					}
				}
				else if ($_left_or_right === 1)
				{
					$_insert_pointer->right = &$temp;
				}
				$temp->top = &$_insert_pointer;
			}
			$_left_or_right = 0;
			$_insert_pointer = &$temp;
			$this->_length++;
		}
		else
		{
			if ($_left_or_right === 0)
			{
				$_left_or_right = 1;
			}
			else if ($_left_or_right === 1)
			{
				do{
					$_insert_pointer = &$_insert_pointer->top;
					$_height--;
				}while (!empty($_insert_pointer->right));
				$_left_or_right = 0;
			}
		}
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
				//exit();
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
		//var_dump($this->_root);
		if ($node === NULL)
		{
			$node = $this->_root;
		}
		//var_dump($this->_root);
		
		$stack = new stack();
		
		$array = array();
		$temp = &$node;
		
		while ($temp !== NULL || $stack->length()>0)
		{
			if ($temp!==NULL)
			{
				$stack->push($temp);
				$temp = $temp->left;
				
			}
			else
			{
				$temp = $stack->pop();
				$array[] = $this->get($temp);
				$temp = $temp->right;
			}
		}
		
		return $array;
	}
	
	/**
	 * 后序遍历
	 * @param \stdClass $node 遍历开始的节点
	 */
	public function postIterator(\stdClass $node = NULL)
	{
		static $array = array();
		if ($node !== NULL)
		{
			$this->postIterator($node->left);
			
			$this->postIterator($node->right);
			
			$array[] = $this->get($node);
		}
		return $array;
	}
}