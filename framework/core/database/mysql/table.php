<?php
namespace framework\core\database\mysql;

use framework\core\database\driver\mysql;
use framework\core\model;
use framework\core\component;

/**
 * model用来管理表的数据
 * table用来管理表的结构
 * 
 * @author fx
 */
class table extends component
{

	/**
	 *
	 * @var mysql
	 */
	private $_db;

	/**
	 * 表名
	 * 
	 * @var unknown
	 */
	private $_name;

	/**
	 * 表是否存在
	 * 
	 * @var string
	 */
	private $_exist = false;

	/**
	 * 存储表结构
	 * 
	 * @var array
	 */
	private $_desc = array();

	/**
	 * 存储索引结构
	 * 
	 * @var array
	 */
	private $_index_list = array();
	
	/**
	 * 存储主键索引结构
	 * @var array
	 */
	private $_primary_list = array();

	function __construct($table_name, $config = NULL)
	{
		$this->_name = $table_name;
		
		if (empty($config))
		{
			$model = $this->model($this->getName());
			$config = $this->getConfig('db');
		}
		else if (is_scalar($config))
		{
			$config = $this->getConfig('db',$config);
		}
		
		$type = $config['type'];
		$type = '\\framework\\core\\database\\driver\\' . $type;
		
		$this->_db = $type::getInstance($config);
		
		$tables = $this->_db->showTables();
		$this->_exist = in_array($this->getName(), $tables);
	}
	
	function initlize()
	{
		if ($this->_exist)
		{
			$descs = $this->_db->query('show full columns from '.$this->getName());
			foreach ($descs as $desc)
			{
				preg_match('/[a-zA-Z]+/', $desc['Type'], $type);
				preg_match('/\((?<length>.+)\)/', $desc['Type'], $lengthData);
				
				$type = strtolower($type[0]);
				
				$length = 0;
				if (isset($lengthData['length']))
				{
					$length = $lengthData['length'];
				}
				
				$auto_increment = false;
				$prototype = '';
				switch (strtolower(trim($desc['Extra'])))
				{
					case 'auto_increment':
						$auto_increment = true;
						break;
					case 'on update current_timestamp':
						$prototype = 'on update CURRENT_TIMESTAMP';
						break;
				}
				
				if (stripos($desc['Type'], 'unsigned zerofill')!==false)
				{
					$prototype = 'unsigned zerofill';
				}
				else if (stripos($desc['Type'], 'unsigned')!==false)
				{
					$prototype = 'unsigned';
				}
				
				
				$this->_desc[$desc['Field']] = array(
					'type' => $type,
					'length' => $length,
					'null' => $desc['Null'] !== 'NO',
					'default' => $desc['Default'],
					'auto_increment' => $auto_increment,
					'collation' => $desc['Collation'],
					'prototype' => $prototype,
					'comment' => $desc['Comment'],
				);
			}
			
			// 索引结构
			$keys = $this->_db->query('show keys from ' . $this->getName());
			foreach ($keys as $key)
			{
				$keyname = strtolower($key['Key_name']);
				if (! isset($this->_index_list[$keyname]))
				{
					$this->_index_list[$keyname] = array(
						'index_type' => $key['Index_type'], // 索引类型
						'unique' => $key['Non_unique'] == 0, // 是否唯一索引
						'comment' => $key['Comment'], // 注释
						'fields' => array( // 字段
							$key['Column_name']
						)
					);
				}
				else
				{
					$this->_index_list[$keyname]['fields'][] = $key['Column_name'];
				}
			}
		}
		parent::initlize();
	}

	/**
	 * 获取表名
	 * 
	 * @return \framework\core\database\mysql\unknown
	 */
	function getName()
	{
		return $this->_name;
	}

	/**
	 * 锁定字段
	 * 
	 * @param unknown $field_name        
	 * @return \framework\core\database\mysql\field
	 */
	function field($field_name)
	{
		//字段默认属性
		$field_info = isset($this->_desc[$field_name])?$this->_desc[$field_name]:array(
			'type' => 'int',
			'length' => 11,
			'null' => false,
			'default' => NULL,
			'auto_increment' => false,
			'collation' => '',
			'prototype' => '',
			'comment' => '',
		);
		$field = new field($field_info,$field_name, $this->getName(), $this->_db);
		if (!$this->exist())
		{
			$sql = 'CREATE TABLE `'.$this->_table_name.'` ( '.field::getFieldSqlString($field_info).' )';
			$this->_db->execute($sql);
		}
		return $field;
	}

	/**
	 * 获取索引
	 * @param unknown $index_name
	 * @return \framework\core\database\mysql\index|NULL
	 */
	function index($index_name)
	{
		$index_info = isset($this->_index_list[$index_name])?$this->_index_list[$index_name]:array(
			'index_type' => '', // 索引类型
			'unique' => false, // 是否唯一索引
			'comment' => '', // 注释
			'fields' => array( // 字段
			)
		);
		return new index($index_info,$index_name, $this->getName(), $this->_db);
	}
	
	/**
	 * 获取主键索引
	 * @return \framework\core\database\mysql\index|\framework\core\database\mysql\NULL
	 */
	function primary()
	{
		return $this->index('primary');
	}

	/**
	 * 删除表
	 */
	function drop()
	{
		if ($this->_exist)
		{
			$sql = 'DROP VIEW `' . $this->getName() . '`';
			$this->_db->query($sql);
			return $this->_db->error() == '00000';
		}
		return false;
	}

	/**
	 * 创建表
	 */
	function create()
	{
	}

	/**
	 * 给表添加注释
	 * 
	 * @param unknown $string        
	 * @return boolean
	 */
	function comment($string)
	{
		$sql = 'ALTER TABLE ' . $this->getName() . ' COMMENT="' . $string . '"';
		$this->_db->query($sql);
		return $this->_db->errno() == '00000';
	}

	/**
	 * 索引列表
	 */
	function getIndex()
	{
		return $this->_index_list;
	}

	/**
	 * 表结构
	 * 
	 * @return array
	 */
	function getDesc()
	{
		return $this->_desc;
	}
	
	/**
	 * 判断表是否存在
	 */
	function exist()
	{
		return $this->_exist;
	}
}

