<?php
namespace framework\core\database\mysql;
/**
 * @author fx
 *
 */
class table
{
	private $_name;
	
	private $_fields = array();
	
	private $_key = array(
		'unique' => array(),
	);
	
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
	function int($name,$length)
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
	 * 添加主键索引
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
	 * 添加索引
	 * @param string|array $field
	 * @param string $name 索引名 可选，默认第一个字段名
	 */
	function index($field,$name = '')
	{
		
	}
	
	/**
	 * 添加唯一索引
	 * @param string|array $field 字段名
	 * @param string $name 可选 索引名
	 */
	function unique($field,$name = '')
	{
		
	}
	
	/**
	 * 转化为sql语句 尚未完成
	 */
	function __toSql()
	{
		$fields = array_map(function($field){
			return $field->__toSql();
		}, $this->_fields);
		return 'CREATE TABLE `'.$this->getName().'` ('.implode(',', $fields).'
		 	PRIMARY KEY `id`(`id`,`name`),
		 	UNIQUE KEY `name` (`name`,`card`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
	}
}

