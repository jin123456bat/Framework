<?php
namespace framework\core\database\mysql;
/**
 * @author fx
 *
 */
class table
{
	/**
	 * 表名
	 * @var unknown
	 */
	private $_name;
	
	/**
	 * 存储了表的所有字段属性
	 * @var array
	 */
	private $_fields = array();
	
	/**
	 * 表在创建的时候需要额外的索引
	 * @var array
	 */
	private $_key = array(
		'unique' => array(),
		'primary' => array(),
		'index' => array(),
	);
	
	/**
	 * 数据表的存储引擎
	 * @var string
	 */
	private $_engine = 'innodb';
	
	/**
	 * 数据表的字符集
	 * @var string
	 */
	private $_charset = 'utf8';
	
	/**
	 * 只有当不存在表的时候才创建表
	 * @var string
	 */
	private $_not_exist = true;
	
	const ENGINE_INNODB = 'innodb';
	
	const ENGINE_MYISAM = 'myisam';
	
	function __construct($name)
	{
		$this->_name = $name;
	}
	
	/**
	 * 获取表名
	 */
	function getName()
	{
		return $this->_name;
	}
	
	/**
	 * 更改表名
	 */
	function setName($name)
	{
		$this->_name = $name;
	}
	
	/**
	 * 只有当不存在的时候才创建表
	 */
	function notExist($exist = false)
	{
		$this->_not_exist = $exist;
	}
	
	/**
	 * 添加一个varchar类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function varchar($name,$length)
	{
		$temp = new field($name,'varchar',$length);
		$this->_fields[] = $temp;
		return $temp;
	}
		
	/**
	 * 添加一个char类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function char($name,$length)
	{
		$temp = new field($name, 'char',$length);
		$this->_fields[] = $temp;
		return $temp;
	}
	
	/**
	 * 添加一个int类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function int($name,$length = 11)
	{
		$temp = new field($name, 'int',$length);
		$this->_fields[] = $temp;
		return $temp;
	}
	
	/**
	 * 添加一个timestamp类型的字段
	 * @param unknown $name 字段名称
	 * @return \framework\core\database\mysql\field
	 */
	function timestamp($name)
	{
		$temp = new field($name, 'timestamp');
		$this->_fields[] = $temp;
		return $temp;
	}
	
	/**
	 * 添加一个datetime类型的字段
	 * @param unknown $name 字段名称
	 * @return \framework\core\database\mysql\field
	 */
	function datetime($name)
	{
		$temp = new field($name, 'datetime');
		$this->_fields[] = $temp;
		return $temp;
	}
	
	/**
	 * 添加一个text类型的字段
	 * @param unknown $name 字段名称
	 * @param unknown $length 字段长度
	 * @return \framework\core\database\mysql\field
	 */
	function text($name,$length)
	{
		$temp = new field($name, 'text',$length);
		$this->_fields[] = $temp;
		return $temp;
	}
	
	/**
	 * 添加主键索引 未完成
	 * @param string|array $field
	 */
	function primary($field)
	{
		if (is_scalar($field))
		{
			
		}
		else if (is_array($field))
		{
			
		}
	}
	
	/**
	 * 添加索引 未完成
	 * @param string|array $field
	 * @param string $name 索引名 可选，默认第一个字段名
	 */
	function index($field,$name = '')
	{
		
	}
	
	/**
	 * 添加唯一索引  未完成
	 * @param string|array $field 字段名
	 * @param string $name 可选 索引名
	 */
	function unique($field,$name = '')
	{
		
	}
	
	/**
	 * 设置数据库引擎
	 * @param string $engine
	 */
	function engine($engine)
	{
		$this->_engine = $engine;
	}
	
	/**
	 * 设置表的字符集
	 * @param unknown $charset
	 */
	function charset($charset)
	{
		$this->_charset = $charset;
	}
	
	/**
	 * 转化为sql语句 尚未完成
	 */
	function __toSql()
	{
		$fields = array_map(function($field){
			return $field->__toSql();
		}, $this->_fields);
		
		
		$engine = empty($this->_engine)?'':'ENGINE='.$this->_engine;
		
		$charset = empty($this->_charset)?'':'DEFAULT CHARSET='.$this->_charset;
		
		$exist = $this->_not_exist?'IF NOT EXISTS ':'';
		
		return 'CREATE TABLE '.$exist.'`'.$this->getName().'` ('.implode(',', $fields).') '.$engine.' '.$charset.';';
	}
}

