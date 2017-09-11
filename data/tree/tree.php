<?php
namespace framework\data\tree;

use framework\data\data;

/**
 * 树形结构
 * 二叉树基于此类
 * @author jin
 *
 */
abstract class tree extends data
{
	/**
	 * 根节点
	 * @var unknown
	 */
	protected $_root;
	
	/**
	 * 节点个数
	 * @var int
	 */
	protected $_length;
	
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
		return $this->_length;
	}
	
	/**
	 * 获取节点中的数据
	 */
	protected function get(\stdClass $node)
	{
		return $node->data;
	}
	
	/**
	 * 创建一个节点
	 * @param mixed $data
	 * @return \stdClass
	 */
	protected function node($data)
	{
		
	}
}