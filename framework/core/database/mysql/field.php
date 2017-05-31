<?php
namespace framework\core\database\mysql;
/**
 * @author fx
 *
 */
class field
{
	public $_name = '';

	/**
	 * 是否为null
	 * @var string
	 */
	public $_null = false;

	/**
	 * 数据类型
	 * @var string
	 */
	public $_type = '';
	
	/**
	 * 数据类型长度
	 * @var integer
	 */
	public $_length = 0;
	
	/**
	 * 默认值
	 * @var string
	 */
	public $_default = '';
	
	/**
	 * 属性
	 * @var string
	 */
	public $_prototype = '';
	
	/**
	 * 是否自增  当$_type为int的类型的时候有效
	 * @var string
	 */
	public $_auto_increment = false;
	
	/**
	 * 注释
	 * @var string
	 */
	public $_comment = '';
	
	/**
	 * 当前字段是否唯一
	 * @var string
	 */
	public $_unique = false;
	
	/**
	 * 当前字段是否主键
	 * @var string
	 */
	public $_primary = false;
	
	/**
	 * 字符集
	 * @var string
	 */
	public $_charset = '';
	
	/**
	 * 新加字段位置
	 * @var string
	 */
	public $_pos = '';
	
	const DEFAULT_CURRENT_TIMESTAMP = 'current_timestamp';
	
	const PROTOTYPE_UNSIGNED = 'unsigned';
	
	const PROTOTYPE_BINARY = 'binary';
	
	const PROTOTYPE_UNSIGNED_ZEROFILL = 'unsigned zerofill';
	
	const PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP = 'on update current_timestamp';

	function __construct($name,$type,$length = 0)
	{
		$this->_name = $name;
		$this->_type = $type;
		$this->_length = $length;
	}

	/**
	 * 设置字段是否可空  默认为false
	 * @param string $null  true可空  false不可空  默认为true
	 */
	function nullable($null = true)
	{
		$this->_null = $null;
	}

	/**
	 * 设置字段默认值
	 * @param unknown $value
	 */
	function default($value)
	{
		$this->_default = $value;
		return $this;
	}
	
	/**
	 * 字符集
	 */
	function charset($charset)
	{
		$this->_charset = $charset;
		return $this;
	}
	
	/**
	 * 设置属性
	 * @param unknown $prototype
	 */
	function prototype($prototype)
	{
		if ($prototype === self::PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP)
		{
			if (in_array(strtolower($this->_type), array('timestamp'),true))
			{
				$this->_prototype = $prototype;
			}
		}
		else if ($prototype === self::PROTOTYPE_UNSIGNED || $prototype === self::PROTOTYPE_UNSIGNED_ZEROFILL)
		{
			if (in_array(strtolower($this->_type), array('int'),true))
			{
				$this->_prototype = $prototype;
			}
		}
		else
		{
			$this->_prototype = $prototype;
		}
		return $this;
	}
	
	/**
	 * 字段位置在XXX之后
	 * @param unknown $name
	 */
	function after($name)
	{
		$this->_pos = 'AFTER `'.$name.'`';
	}
	
	/**
	 * 字段位置在最开始
	 */
	function first()
	{
		$this->_pos = 'FIRST';
	}
	
	/**
	 * AUTO_INCREMENT
	 * @param bool $auto_increment
	 */
	function AI($auto_increment = true)
	{
		$this->_auto_increment = $auto_increment;
		$this->_primary = true;
		return $this;
	}
	
	/**
	 * 设置当前字段为唯一
	 */
	function unique($unique = true)
	{
		$this->_unique = $unique;
		return $this;
	}
	
	/**
	 * 设置当前字段为主键
	 */
	function primary($primary = true)
	{
		$this->_primary = $primary;
		return $this;
	}
	
	/**
	 * 判断当前字段时候设置为主键
	 * @return string
	 */
	function isPrimary()
	{
		return $this->_primary;
	}
	
	/**
	 * 设置字段备注
	 * @param unknown $string
	 */
	function comment($string)
	{
		$this->_comment = $string;
		return $this;
	}
	
	/**
	 * 转化为create table的sql语句
	 */
	function __toSql()
	{
		switch (strtolower($this->_type))
		{
			case 'varchar':
			case 'char':
			case 'int':
				$type = $this->_type.'('.$this->_length.')';
			break;
			case 'timestamp':
				$type = $this->_type;
		}
		
		if (strtolower($this->_type) == 'int' && $this->_auto_increment)
		{
			$auto_increment = 'AUTO_INCREMENT';
		}
		else
		{
			$auto_increment = '';
		}
		
		if ($this->_null)
		{
			$null = 'NULL';
		}
		else
		{
			$null = 'NOT NULL';
		}
		
		$prototype = $this->_prototype;
		
		$comment = empty($this->_comment)?'':'COMMENT \''.$this->_comment.'\'';
		
		$unique = $this->_unique?'UNIQUE KEY':'';
		
		$primary = $this->_primary?'PRIMARY KEY':'';
		
		if (empty($primary))
		{
			if ($this->_default === NULL)
			{
				$default = 'DEFAULT NULL';
			}
			else
			{
				$default = $this->_default!==''?('DEFAULT \''.$this->_default.'\''):'';
			}
		}
		else
		{
			$default = '';
		}
		
		
		if (in_array(strtolower($this->_type), array('varchar','char'),true) && !empty($this->_charset))
		{
			$charset = 'CHARACTER SET '.$this->_charset;
		}
		else
		{
			$charset = '';
		}
		return sprintf('`%s` %s %s %s %s %s %s %s %s %s',$this->_name,$type,$charset,$prototype,$null,$default,$auto_increment,$unique,$comment,$primary);
	}
}