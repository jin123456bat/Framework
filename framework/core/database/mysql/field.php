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
	 * 注释
	 * @var string
	 */
	public $_commit = '';
	
	const DEFAULT_CURRENT_TIMESTAMP = 'current_timestamp';
	
	const PROTOTYPE_UNSIGNED = 'unsigned';
	
	const PROTOTYPE_BINARY = 'binary';
	
	const PROTOTYPE_UNSIGNED_ZEROFILL = 'unsigned zerofill';
	
	const PROTOTYPE_ON_UPDATE_CURRENT_TIMESTAMP = 'on update current timestamp';

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
		if ($null)
		{
			$this->default('NULL');
		}
		$this->_null = $null;
	}

	/**
	 * 设置字段默认值
	 * @param unknown $value
	 */
	function default($value)
	{
		$this->_default = $value;
	}
	
	/**
	 * 字符集
	 */
	function charset()
	{
		
	}
	
	function prototype($prototype)
	{
		
	}

	/**
	 * 设置字段备注
	 * @param unknown $string
	 */
	function commit($string)
	{
		$this->_commit = $string;
	}
	
	/**
	 * 转化为create table的sql语句
	 */
	function __toCreateSql()
	{
		
	}
}