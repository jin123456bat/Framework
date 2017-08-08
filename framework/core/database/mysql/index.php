<?php
namespace framework\core\database\mysql;

use framework\core\base;
use framework\core\database\driver\mysql;

class index extends base
{

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

	function __construct($index_name, $index, $table_name, $connection)
	{
		$this->_index_name = $index_name;
		$this->_index = $index;
		$this->_table_name = $table_name;
		$this->_connection = $connection;
	}

	/**
	 * 删除索引
	 */
	function drop()
	{
		$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX ' . $this->_index_name;
		$this->_connection->execute($sql);
		return $this->_connection == '00000';
	}

	/**
	 * 索引改名
	 */
	function rename($new_name)
	{
		$fields = '`' . implode('`,`', $this->_index['fields']) . '`';
		$type = $this->_index['index_type'];
		if (strtolower($this->_index_name) == 'primary')
		{
			$sql = 'alter table `' . $this->_table_name . '` drop primary key, add primary key (' . $fields . ') using ' . $type;
		}
		else
		{
			if ($this->_index['unique'])
			{
				$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX `' . $this->_index_name . '`, ADD UNIQUE `' . $new_name . '` (' . $fields . ') using ' . $type;
			}
			else
			{
				$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP INDEX `' . $this->_index_name . '`, ADD INDEX `' . $new_name . '` (' . $fields . ') using ' . $type;
			}
		}
		$this->_connection->execute($sql);
		return $this->_connection == '00000';
	}
}