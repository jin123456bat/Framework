<?php
namespace framework\data\tree;

/**
 * 查找二叉树
 * 若它的左子树不空，则左子树上所有结点的值均小于它的根结点的值； 
 * 若它的右子树不空，则右子树上所有结点的值均大于它的根结点的值；
 * 它的左、右子树也分别为二叉排序树
 * @author jin
 *
 */
class bstree extends btree
{
	/**
	 * 往树中添加一个节点
	 * @param mixed $data
	 * @param boolean true添加成功 false添加失败（数据已经存在才会失败）
	 */
	function push($data = NULL)
	{
		$temp = new \stdClass();
		$temp->left = NULL;
		$temp->right = NULL;
		$temp->data = $data;
		if (empty($this->_root))
		{
			$this->_root = &$temp;
			$this->_length++;
			return true;
		}
		else
		{
			$pointer = &$this->_root;
			while (!empty($pointer))
			{
				if ($data > $pointer->data)
				{
					if (empty($pointer->right))
					{
						$pointer->right = &$temp;
						$this->_length++;
						return true;
					}
					else
					{
						$pointer = &$pointer->right;
					}
				}
				else if ($data < $pointer->data)
				{
					if (empty($pointer->left))
					{
						$pointer->left= &$temp;
						$this->_length++;
						return true;
					}
					else
					{
						$pointer = &$pointer->left;
					}
				}
				else
				{
					return false;
				}
			}
		}
	}
}