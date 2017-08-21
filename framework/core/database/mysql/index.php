<?php
namespace framework\core\database\mysql;

use framework\core\base;
use framework\core\database\driver\mysql;

class index extends base
{
	/**
	 * 索引类型 BTREE
	 * @var string
	 */
	const INDEX_TYPE_BTREE = 'btree';
	
	/**
	 * 索引类型hash
	 * @var string
	 */
	const INDEX_TYPE_HASH = 'hash';

	/**
	 * 索引名
	 * 
	 * @var unknown
	 */
	private $_index_name;

	/**
	 * 索引信息
	 * 
	 * @var unknown
	 */
	private $_index;

	/**
	 * 表名
	 * 
	 * @var unknown
	 */
	private $_table_name;

	/**
	 *
	 * @var mysql
	 */
	private $_connection;

	function __construct($index_info ,$index_name , $table_name, $connection)
	{
		$this->_index_name = $index_name;
		$this->_index = $index_info;
		$this->_table_name = $table_name;
		$this->_connection = $connection;
	}
	
	private function createSql()
	{
		$fields = '`' . implode('`,`', $this->_index['fields']) . '`';
		$type = $this->_index['index_type'];
		if (strtolower($this->_index_name) == 'primary')
		{
			$sql = 'alter table `' . $this->_table_name . '` drop primary key, add primary key (' . $fields . ') using ' . $type;
		}
		else
		{
			$new_name = $this->_index_name;
			if (isset($this->_new_name) && !empty($this->_new_name))
			{
				$new_name = $this->_new_name;
			}
			
			$comment = !empty($this->_index['comment'])?'COMMENT "'.$this->_index['comment'].'"':'';
			
			if ($this->_index['unique'])
			{
				$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX `' . $this->_index_name . '`, ADD UNIQUE `' . $new_name . '` (' . $fields . ') using ' . $type .' '. $comment;
			}
			else
			{
				$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX `' . $this->_index_name . '`, ADD INDEX `' . $new_name . '` (' . $fields . ') using ' . $type .' '. $comment;
			}
		}
		return $sql;
	}

	/**
	 * 删除索引
	 */
	function drop()
	{
		$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX ' . $this->_index_name;
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 索引改名
	 */
	function rename($new_name)
	{
		$this->_new_name = $new_name;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		unset($this->_new_name);
		$this->_index_name = $new_name;
		return $this;
	}
	
	/**
	 * 在该索引中添加其他字段
	 */
	function add($field_name)
	{
		$this->_index['fields'][] = $field_name;
		$this->_index['fields'] = array_unique($this->_index['fields']);
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 删除字段 无法删除所有字段
	 * @param unknown $field_name
	 */
	function remove($field_name)
	{
		$this->_index['fields'] = array_diff($this->_index['fields'], array($field_name));
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置索引类型
	 * @param string $type
	 */
	function type($type = INDEX_TYPE_HASH)
	{
		$this->_index['type'] = $type;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置索引是否唯一
	 * @param string $unique
	 */
	function unique($unique = true)
	{
		$this->_index['unique'] = $unique;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置索引注释
	 * @param unknown $comment
	 */
	function comment($comment)
	{
		$this->_index['comment'] = $comment;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
}