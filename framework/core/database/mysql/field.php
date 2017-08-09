<?php
namespace framework\core\database\mysql;

/**
 *
 * @author fx
 */
class field
{

	/**
	 * 字段的其他属性
	 * 
	 * @var unknown
	 */
	private $_field_info;

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

	function __construct($field_info, $field_name, $table_name, $connection)
	{
		$this->_field_info = $field_info;
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
	 * 根据字段属性创建sql
	 */
	private function createSql($change = false)
	{
		$collation = '';
		switch ($this->_field_info['type'])
		{
			case 'varchar':
				//CHARACTER SET utf8 COLLATE utf8_general_ci
				if (!empty($this->_field_info['collation']))
				{
					$character = current(explode('_', $this->_field_info['collation']));
					$collation = 'CHARACTER SET '.$character.' collate '.$this->_field_info['collation'];
				}
			case 'int':
				$type = $this->_field_info['type'] . '(' . $this->_field_info['length'] . ')';
			break;
			default:
				$type = $this->_field_info['type'];
		}
		
		$null = $this->_field_info['null'] ? 'NULL' : 'NOT NULL';
		
		$default = '';
		if ($this->_field_info['default'] === null)
		{
			if ($this->_field_info['null'])
			{
				$default = 'DEFAULT NULL';
			}
			else
			{
				$default = '';
			}
		}
		else if ($this->_field_info['default'] == 'CURRENT_TIMESTAMP')
		{
			$default = 'DEFAULT CURRENT_TIMESTAMP';
		}
		else
		{
			$default = 'DEFAULT	"'.$this->_field_info['default'].'"';
		}
		
		$comment = '';
		if (isset($this->_field_info['comment']) && !empty($this->_field_info['comment']))
		{
			$comment = ' comment "'.$this->_field_info['comment'].'"';
		}
		
		$after = '';
		if (isset($this->_field_info['after']) && !empty($this->_field_info))
		{
			$after = ' AFTER `'.$this->_field_info['after'].'`';
		}
		else if (isset($this->_field_info['first']) && $this->_field_info['first'])
		{
			$after = ' FIRST';
		}
		
		$auto_increment = $this->_field_info['auto_increment']?' AUTO_INCREMENT ':'';
		
		if ($change)
		{
			$field_name = $this->getFieldName();
			if (isset($this->_field_info['name']) && !empty($this->_field_info['name']))
			{
				$field_name = $this->_field_info['name'];
			}
			
			$sql = 'ALTER TABLE `' . $this->_table_name . '` CHANGE `' . $this->getFieldName() . '` `'.$field_name.'` ' . $type .' '.$collation.' ' . $null .' ' .$auto_increment. ' ' . $default.' '.$comment.$after;
		}
		else
		{
			$sql = 'ALTER TABLE `' . $this->_table_name . '` MODIFY `' . $this->getFieldName() . '` ' . $type .' '.$collation.' ' . $null .' ' .$auto_increment. ' ' . $default.' '.$comment.$after;
		}
		var_dump($sql);
		return $sql;
	}

	/**
	 * 为字段添加注释
	 * 
	 * @param unknown $string        
	 */
	function comment($string)
	{
		$this->_field_info['comment'] = $string;
		$sql = $this->createSql();
		$this->_connection->query($sql);
		return $this;
	}

	/**
	 * 修改字段名称
	 */
	function rename($new_name)
	{
		$this->_field_info['name'] = $new_name;
		$sql = $this->createSql(true);
		$this->_connection->query($sql);
		//更新名称
		$this->_field_name = $new_name;
		unset($this->_field_info['name']);
		return $this;
	}

	/**
	 * 移动字段 移动到某字段后面  假如是最前面的话请使用first方法
	 * @param $string $after
	 */
	function move($after)
	{
		if ($after instanceof field)
		{
			$after = $after->getFieldName();
		}
		$this->_field_info['after'] = trim($after,'`');
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		unset($this->_field_info['after']);
		return $this;
	}
	
	/**
	 * 移动字段到第一个 假如是移动到某一个字段后面请使用move方法
	 * @param unknown $first
	 * @return \framework\core\database\mysql\field
	 */
	function first()
	{
		$this->_field_info['first'] = true;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		unset($this->_field_info['first']);
		return $this;
	}
	
	/**
	 * 设置默认值
	 */
	function default($default)
	{
		$this->_field_info['default'] = $default;
		$sql = $this->createSql();
		$this->_connection->query($sql);
		return $this;
	}

	/**
	 * 类型为text，varchar，char类型的时候这个方法有效
	 * 设置字符集
	 */
	function charset($charset)
	{
		switch (strtolower(trim($charset)))
		{
			case 'utf-8':
			case 'utf8':
				$charset = 'utf8_general_ci';
			break;
			case 'utf8mb4':
				$charset = 'utf8mb4_general_ci';
			break;
			case 'gbk':
				$charset = 'gbk_chinese_ci';
			break;
			case 'gb2321':
				$charset = 'gb2312_chinese_ci';
			break;
		}
		$this->_field_info['collation'] = $charset;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 是否可空
	 * @param bool $null true为可空，false为非可空，默认为true
	 */
	function isNull($null = true)
	{
		$this->_field_info['null'] = $null;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 设置当前字段为自增
	 * 只有字段为主键并且为int类型的时候有效
	 * @param bool $ai 默认为true
	 */
	function autoIncrement($ai = true)
	{
		$this->_field_info['auto_increment'] = $ai;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
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