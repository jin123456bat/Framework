<?php
namespace framework\core\database\mysql;

use framework\core\database\driver\mysql;
use framework\core\base;
use framework\core\model;

/**
 * model用来管理表的数据
 * table用来管理表的结构
 * 
 * @author fx
 */
class table extends base
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

	function __construct($table_name, $config = NULL)
	{
		$this->_name = $table_name;
		
		if (empty($config))
		{
			$model = $this->model($this->getName());
			$db = $model->getDefaultDbConfig();
		}
		else if (is_array($config))
		{
			// $this->_db = mysql::getInstance($config);
			$db = $config;
		}
		else if (is_scalar($config))
		{
			$config = $this->getConfig('db');
			$db = $config['db'];
			if (isset($db[$config]) && ! empty($db[$config]) && is_array($db[$config]))
			{
				$db = $db[$config];
			}
		}
		
		$type = $db['type'];
		$type = '\\framework\\core\\database\\driver\\' . $type;
		
		$this->_db = $type::getInstance($db);
		
		$tables = $this->_db->showTables();
		$this->_exist = in_array($this->getName(), $tables);
		
		if ($this->_exist)
		{
			$descs = $this->_db->query('desc ' . $this->getName());
			// var_dump($descs);
			foreach ($descs as $desc)
			{
				preg_match('/[a-zA-Z]+/', $desc['Type'], $type);
				preg_match('/[0-9]+/', $desc['Type'], $length);
				$this->_desc[$desc['Field']] = array(
					'type' => strtolower($type[0]),
					'length' => isset($length[0]) ? $length[0] : 0,
					'null' => $desc['Null'] !== 'NO',
					'default' => $desc['Default'],
					'auto_increment' => $desc['Extra'] == 'auto_increment'
				);
			}
			
			// 索引结构
			$keys = $this->_db->query('show keys from ' . $this->getName());
			foreach ($keys as $key)
			{
				if (! isset($this->_index_list[$key['Key_name']]))
				{
					$this->_index_list[$key['Key_name']] = array(
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
					$this->_index_list[$key['Key_name']]['fields'][] = $key['Column_name'];
				}
			}
		}
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
		return new field($field_name, $this->getName(), $this->_db);
	}

	/**
	 * 锁定索引
	 * 
	 * @param unknown $index_name        
	 * @return \application\control\index
	 */
	function index($index_name)
	{
		if (isset($this->_index_list[$index_name]))
		{
			return new index($index_name, $this->_index_list[$index_name], $this->getName(), $this->_db);
		}
		return NULL;
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
	function indexList()
	{
		return $this->_index_list;
	}

	/**
	 * 表结构
	 * 
	 * @return array
	 */
	function desc()
	{
		return $this->_desc;
	}
}

