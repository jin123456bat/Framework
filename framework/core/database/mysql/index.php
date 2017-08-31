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
	 * @var unknown
	 */
	private $_index_name;

	/**
	 * 索引信息
	 * @var unknown
	 */
	private $_index;

	/**
	 * 表名
	 * @var unknown
	 */
	private $_table_name;

	/**
	 *
	 * @var mysql
	 */
	private $_connection;

	/**
	 * 索引是否存在
	 * @var unknown
	 */
	private $_exist;

	function __construct($index_info, $index_name, $table_name, $connection, $exist = true)
	{
		$this->_index_name = $index_name;
		$this->_index = $index_info;
		$this->_table_name = $table_name;
		$this->_connection = $connection;
		$this->_exist = $exist;
	}

	private function createSql()
	{
		$fields = '`' . implode('`,`', $this->_index['fields']) . '`';
		$using = ! empty($this->_index['index_type']) ? ' using ' . $this->_index['index_type'] : '';
		$index_type = strtolower($this->_index_name) == 'primary' ? 'PRIMARY KEY' : ($this->_index['unique'] ? 'UNIQUE' : 'INDEX');
		$comment = ! empty($this->_index['comment']) ? ' COMMENT "' . $this->_index['comment'] . '"' : '';
		if (! $this->_exist)
		{
			// 假如没有索引类型则为添加索引
			$name = '`' . $this->_index_name . '`';
			if (strtolower($this->_index_name) == 'primary')
			{
				$name = '';
			}
			$sql = 'ALTER TABLE `' . $this->_table_name . '` ADD ' . $index_type . ' ' . $name . ' (' . $fields . ')' . $using . $comment;
			$is_create_sql = true;
		}
		else
		{
			// 更改索引
			if (strtolower($this->_index_name) == 'primary')
			{
				$sql = 'alter table `' . $this->_table_name . '` drop primary key, add primary key (' . $fields . ')' . $using . $comment;
			}
			else
			{
				$new_name = $this->_index_name;
				if (isset($this->_new_name) && ! empty($this->_new_name))
				{
					$new_name = $this->_new_name;
				}
				$old_type = $this->_old_unique ? 'UNIQUE' : 'INDEX';
				$new_type = $this->_index['unique'] ? 'UNIQUE' : 'INDEX';
				$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP ' . $old_type . ' `' . $this->_index_name . '`, ADD ' . $new_type . ' `' . $new_name . '` (' . $fields . ')' . $using . $comment;
				$is_create_sql = false;
			}
		}
		return array(
			'sql' => $sql,
			'is_create_sql' => $is_create_sql,
		);
	}

	/**
	 * 删除索引
	 */
	function drop()
	{
		$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX ' . $this->_index_name;
		$this->_connection->execute($sql);
		$this->_exist = false;
		return $this;
	}

	/**
	 * 索引改名
	 */
	function rename($new_name)
	{
		$this->_new_name = $new_name;
		$sql = $this->createSql();
		$this->_connection->execute($sql['sql']);
		if ($sql['is_create_sql'])
		{
			$this->_exist = true;
		}
		unset($this->_new_name);
		$this->_index_name = $new_name;
		return $this;
	}

	/**
	 * 在该索引中添加其他字段
	 */
	function add($field_name)
	{
		if (is_string($field_name))
		{
			$this->_index['fields'][] = $field_name;
		}
		else if (is_array($field_name))
		{
			$this->_index['fields'] = array_merge($this->_index['fields'], $field_name);
		}
		$this->_index['fields'] = array_unique($this->_index['fields']);
		$sql = $this->createSql();
		$this->_connection->execute($sql['sql']);
		if ($sql['is_create_sql'])
		{
			$this->_exist = true;
		}
		return $this;
	}

	/**
	 * 删除字段 无法删除所有字段
	 * @param unknown $field_name        
	 */
	function remove($field_name)
	{
		$this->_index['fields'] = array_diff($this->_index['fields'], array(
			$field_name
		));
		$sql = $this->createSql();
		$this->_connection->execute($sql['sql']);
		return $this;
	}

	/**
	 * 设置索引类型
	 * @param string $type        
	 */
	function type($type = INDEX_TYPE_HASH)
	{
		$this->_index['index_type'] = $type;
		$sql = $this->createSql();
		$this->_connection->execute($sql['sql']);
		return $this;
	}

	/**
	 * 设置索引是否唯一
	 * @param string $unique        
	 */
	function unique($unique = true)
	{
		$this->_old_unique = $this->_index['unique'];
		$this->_index['unique'] = $unique;
		$sql = $this->createSql();
		$this->_connection->execute($sql['sql']);
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
		$this->_connection->execute($sql['sql']);
		return $this;
	}
}