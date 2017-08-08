<?php
namespace framework\core\database\mysql;

/**
 *
 * @author fx
 */
class field
{

	/**
	 * 字段名
	 * 
	 * @var unknown
	 */
	private $_field_name;

	/**
	 * 表名
	 * 
	 * @var unknown
	 */
	private $_table_name;

	/**
	 * 连接
	 * 
	 * @var \framework\core\database\driver\mysql
	 */
	private $_connection;

	function __construct($field_name, $table_name, $connection)
	{
		$this->_field_name = $field_name;
		$this->_table_name = $table_name;
		$this->_connection = $connection;
	}

	function getFieldName()
	{
		return $this->_field_name;
	}

	/**
	 * 删除字段
	 * 
	 * @return boolean
	 */
	function drop()
	{
		$sql = 'ALTER TABLE `' . $this->_table_name . '` DROP `' . $this->getFieldName() . '`';
		$this->_connection->query($sql);
		return $this->_connection->errno() == '00000';
	}

	/**
	 * 为字段添加注释
	 * 
	 * @param unknown $string        
	 */
	function comment($string)
	{
	}

	/**
	 * 修改字段名称
	 */
	function rename()
	{
	}

	/**
	 * 移动字段
	 */
	function move()
	{
	}

	/**
	 * 设置默认值
	 */
	function default()
	{
	}

	/**
	 * 类型为text，varchar，char类型的时候这个方法有效
	 * 设置字符集
	 */
	function charset()
	{
	}

	/**
	 * 是否可空
	 */
	function isNull()
	{
	}

	/**
	 * 是否自增
	 * 只有字段为主键并且为int类型的时候有效
	 */
	function autoIncrement()
	{
	}

	private function prototype()
	{
	}

	function binary()
	{
	}

	function unsigned()
	{
	}

	function unsignedZerofill()
	{
	}

	/**
	 * 设置为当更新的时候 该字段为当前时间
	 * 只有当字段类型为datetime或者timestamp或者date或者time或者year的时候有效
	 */
	function onUpdateCurrentTimestamp()
	{
	}

	/**
	 * 设置类型
	 */
	private function type($type, $length)
	{
	}

	function int($length = 11)
	{
	}

	/**
	 * 将字段类型设置为varchar类型
	 */
	function varchar($length = 32)
	{
	}

	/**
	 * 将字段类型设置为text类型
	 */
	function text($length = 65535)
	{
		if ($length <= 0)
		{
			$length = 65535;
		}
		
		if ($length <= pow(2, 8) - 1)
		{
			$type = 'tinytext';
		}
		else if ($length < pow(2, 16) - 1)
		{
			$type = 'text';
		}
		else if ($length < pow(2, 24) - 1)
		{
			$type = 'mediumtext';
		}
		else if ($length < pow(2, 32) - 1)
		{
			$type = 'longtext';
		}
		return $this->type($type, $length);
	}

	/**
	 * 将字段类型设置为datetime类型
	 */
	function datetime()
	{
	}
}