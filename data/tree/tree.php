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
	protected $_root = NULL;
	
	/**
	 * 节点个数
	 * @var int
	 */
	protected $_length = 0;
	
	/**
	 * 树高
	 * @var unknown
	 */
	protected $_height = 0;
	
	/**
	 * 树高
	 * @return int
	 */
	function height()
	{
		return $this->_height;
	}
	
	/**
	 * 节点个数
	 */
	function length()
	{
		return $this->_length;
	}
}