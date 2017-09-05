<?php
namespace framework\data\tree;

/**
 * 二叉树
 * @author jin
 *
 */
class btree extends tree
{
	/**
	 * 根节点
	 * @var unknown
	 */
	protected $_root;
	
	/**
	 * 树高
	 * @return int 
	 */
	function height()
	{
		
	}
	
	/**
	 * 节点个数
	 */
	function size()
	{
		
	}
	
	function push($data)
	{
		$temp = new \stdClass();
		$temp->left = NULL;
		$temp->right = NULL;
		$temp->data = $data;
		if (empty($this->_root))
		{
			$this->_root = &$temp;
		}
		else
		{
			
		}
	}
}