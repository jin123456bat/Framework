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
	private function createSql()
	{
		$collation = '';
		//需要字符集的字段
		$need_collation_fields = array(
			'text',
			'tinytext',
			'mediumtext',
			'longtext',
			'char',
			'varchar',
			'enum',
		);
		//判断字段类型中是否允许字符集
		if (in_array($this->_field_info['type'], $need_collation_fields) && !empty($this->_field_info['collation']))
		{
			$character = current(explode('_', $this->_field_info['collation']));
			$collation = 'CHARACTER SET '.$character.' collate '.$this->_field_info['collation'];
		}
		
		$type = $this->_field_info['type'];
		
		//需要长度的字段
		$need_length_fields = array(
			'tinyint',
			'smallint',
			'mediumint',
			'int',
			'bigint',
			'enum',
			'decimal',
			'bit',
		);
		if (in_array($this->_field_info['type'], $need_length_fields))
		{
			$type = $this->_field_info['type'] . '(' . $this->_field_info['length'] . ')';
		}
		
		$null = $this->_field_info['null'] ? 'NULL' : 'NOT NULL';
		
		//是否是时间字段
		$is_time_fields = array(
			'date',
			'datetime',
			'timestamp',
			'time',
			'year',
		);
		
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
		else if ($this->_field_info['default'] == 'CURRENT_TIMESTAMP' && in_array($type, $is_time_fields))
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
		
		//int字段
		$number_fields= array(
			'tinyint',
			'smallint',
			'mediumint',
			'int',
			'bigint',
			'decimal',
			'float',
			'double',
		);
		$prototype = '';
		if (isset($this->_field_info['prototype']) && !empty($this->_field_info['prototype']))
		{
			if ($prototype == 'on update current_timestamp' && in_array($type, $is_time_fields))
			{
				$prototype = $this->_field_info['prototype'];
			}
			else if (($prototype == 'UNSIGNED' || $prototype == 'UNSIGNED ZEROFILL') && in_array($type, $number_fields))
			{
				$prototype = $this->_field_info['prototype'];
			}
		}
		
		$field_name = $this->getFieldName();
		if (isset($this->_field_info['name']) && !empty($this->_field_info['name']))
		{
			$field_name = $this->_field_info['name'];
		}
		
		$sql = 'ALTER TABLE `' . $this->_table_name . '` CHANGE `' . $this->getFieldName() . '` `'.$field_name.'` ' . $type .' '.$prototype.' '.$collation.' ' . $null .' ' .$auto_increment. ' ' . $default.' '.$comment.$after;
		
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
		$sql = $this->createSql();
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

	/**
	 * 字段添加binary属性
	 * 这个函数可能导致字段字符集变化，比如原来是utf8_general_ci代表不区分大小写的utf8，设置为binary之后会变为utf8_bin
	 * 而这种变化是程序目前无法检测出来的
	 */
	function binary()
	{
		$this->_field_info['prototype'] = 'BINARY';
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 字段类型为int等的时候，设置当前字段为无符号整形
	 */
	function unsigned()
	{
		$this->_field_info['prototype'] = 'UNSIGNED';
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 字段类型为int等的时候，设置当前字段为无符号整形，其余位用0填充
	 */
	function unsignedZerofill()
	{
		$this->_field_info['prototype'] = 'UNSIGNED ZEROFILL';
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 设置为当更新的时候 该字段为当前时间
	 * 只有当字段类型为datetime或者timestamp或者date或者time或者year的时候有效
	 */
	function onUpdateCurrentTimestamp()
	{
		$this->_field_info['prototype'] = 'on update current_timestamp';
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为tinyint
	 * 1字节表示 范围2^8^1
	 * @param number $length
	 */
	function tinyint($length = 4)
	{
		$this->_field_info['type'] = 'tinyint';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为smallint
	 * 2字节表示 范围2^8^2
	 * @param number $length
	 */
	function smallint($length = 6)
	{
		$this->_field_info['type'] = 'smallint';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为mediumint
	 * 2字节表示 范围2^8^3
	 * @param number $length
	 */
	function mediumint($length = 9)
	{
		$this->_field_info['type'] = 'mediumint';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 设置字段类型为int
	 * 4字节表示 范围2^8^4
	 * @param number $length
	 */
	function int($length = 11)
	{
		$this->_field_info['type'] = 'int';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为bigint
	 * 4字节表示 范围2^8^8
	 * @param number $length
	 */
	function bigint($length = 20)
	{
		$this->_field_info['type'] = 'bigint';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 将字段类型设置为char类型
	 * 定长（0-255，默认1）存储时候会在右边补充空格到指定长度
	 */
	function char($length = 1)
	{
		$this->_field_info['type'] = 'char';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 将字段类型设置为varchar类型
	 * 变长
	 */
	function varchar($length = 32)
	{
		$this->_field_info['type'] = 'varchar';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
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
		$this->_field_info['type'] = $type;
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}

	/**
	 * 将字段类型设置为datetime类型
	 */
	function datetime()
	{
		$this->_field_info['type'] = 'datetime';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为timestamp
	 */
	function timestamp()
	{
		$this->_field_info['type'] = 'timestamp';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为date
	 * @return \framework\core\database\mysql\field
	 */
	function date()
	{
		$this->_field_info['type'] = 'date';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为time
	 * @return \framework\core\database\mysql\field
	 */
	function time()
	{
		$this->_field_info['type'] = 'time';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为year
	 * @return \framework\core\database\mysql\field
	 */
	function year()
	{
		$this->_field_info['type'] = 'year';
		$this->_field_info['length'] = 4;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置类型为定点数
	 * @param unknown $M 整数部分，最大65
	 * @param unknown $D 小数部分，最大30
	 * @return \framework\core\database\mysql\field
	 */
	function decimal($M,$D)
	{
		if ($M>=65)
		{
			$M = 65;
		}
		if ($D>=30)
		{
			$D = 30;
		}
		$this->_field_info['type'] = 'decimal';
		$this->_field_info['length'] = $M.','.$D;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为float 单精度浮点数
	 * 
	 */
	function float()
	{
		$this->_field_info['type'] = 'float';
		$this->_field_info['length'] = 4;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 设置字段类型为double 单精度浮点数
	 *
	 */
	function double()
	{
		$this->_field_info['type'] = 'double';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 位类型
	 * @param number $length = 1
	 */
	function bit($length = 1)
	{
		$this->_field_info['type'] = 'bit';
		$this->_field_info['length'] = $length;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 枚举类型
	 * @param array $array
	 * @return \framework\core\database\mysql\field
	 */
	function enum($array)
	{
		$this->_field_info['type'] = 'enum';
		$this->_field_info['length'] = '"'.implode('","', $array).'"';
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 二进制字节流
	 * 最多2^8-1字节
	 * @return \framework\core\database\mysql\field
	 */
	function tinyblob()
	{
		$this->_field_info['type'] = 'tinyblob';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 二进制字节刘
	 * 最多2^24-1字节
	 * @return \framework\core\database\mysql\field
	 */
	function mediumblob()
	{
		$this->_field_info['type'] = 'mediumblob';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 二进制字节流
	 * 最多2^16-1字节
	 * @return \framework\core\database\mysql\field
	 */
	function blob()
	{
		$this->_field_info['type'] = 'blob';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 二进制字节流
	 * 最多2^32-1字节
	 * @return \framework\core\database\mysql\field
	 */
	function longblob()
	{
		$this->_field_info['type'] = 'longblob';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
	
	/**
	 * 存储json对象
	 * @return \framework\core\database\mysql\field
	 */
	function json()
	{
		$this->_field_info['type'] = 'json';
		$this->_field_info['length'] = 0;
		$sql = $this->createSql();
		$this->_connection->execute($sql);
		return $this;
	}
}