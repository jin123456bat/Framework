<?php
namespace framework\core;

use framework\core\database\sql;
use framework\core\database\driver\mysql;

class model extends component
{
	/**
	 * 1对多
	 * @var integer
	 */
	const RELATION_ONE_MANY = 1;
	
	private $_table;
	
	private $_sql;
	
	private $_db;
	
	private static $_history = array();
	
	private $_desc;
	
	function __construct($table = NULL)
	{
		$this->_table = $table;
	}
	
	public static function debug_trace_sql()
	{
		return self::$_history;
	}
	
	/**
	 * when this class is initlized,this function will be execute
	 * {@inheritDoc}
	 * @see \core\component::initlize()
	 */
	function initlize()
	{
		$this->_sql = new sql();
		
		if (method_exists($this, '__config'))
		{
			$db = $this->__config();
		}
		else
		{
			$db = $this->getConfig('db');
			
			if (!isset($db['db_type']))
			{
				foreach ($db as $d)
				{
					if (isset($d['default']) && $d['default'])
					{
						$db = $d;
						break;
					}
				}
			}
		}
		
		$this->_db = mysql::getInstance($db);
		
		if (method_exists($this, '__tableName'))
		{
			$this->_table = $this->__tableName();
		}
		
		$this->setTable($this->_table);
		parent::initlize();
	}
	
	/**
	 * only for sql
	 * @param unknown $name
	 * @param unknown $args
	 * @return \framework\core\model
	 */
	function __call($name,$args)
	{
		call_user_func_array(array($this->_sql,$name),$args);
		return $this;
	}
	
	/**
	 * set database table's name
	 * @param unknown $table
	 */
	function setTable($table)
	{
		$this->_table = $table;
		$this->parse();
	}
	
	/**
	 * get this database table's name
	 * @return unknown|string
	 */
	function getTable()
	{
		return $this->_table;
	}
	
	/**
	 * process something about this tables;
	 */
	private function parse()
	{
		$this->_desc = $this->query('DESC `'.$this->_table.'`');
	}
	
	/**
	 * find all rows from result
	 */
	function select($fields = '*')
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->select($fields);
		$result = $this->query($sql);
		return $result;
	}
	
	/**
	 * find a row from result
	 */
	function find($fields = '*')
	{
		$result = $this->limit(1)->select($fields);
		return isset($result[0])?$result[0]:NULL;
	}
	
	/**
	 * find the first field's value from frist row
	 */
	function scalar($field = '*')
	{
		$result = $this->find($field);
		
		if (is_array($result))
		{
			return array_shift($result);
		}
		return NULL;
	}
	
	/**
	 * 获取数量
	 * @param unknown $field
	 * @return NULL|mixed
	 */
	function count($field)
	{
		return $this->scalar('count('.$field.')');
	}
	
	function max($field)
	{
		return $this->scalar('max('.$field.')');
	}
	
	function sum($field)
	{
		return $this->scalar('sum('.$field.')');
	}
	
	function avg($field)
	{
		return $this->scalar('avg('.$field.')');
	}
	
	/**
	 * 更新数据表
	 * @param unknown $name
	 * @param string $value
	 * @return boolean
	 */
	function update($name,$value = '')
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->update($name,$value);
		return $this->query($sql);
	}
	
	function insert($data = array())
	{
		//字段名称检查
		$fields = array();
		foreach ($this->_desc as $index=>$value)
		{
			$fields[] = $value['Field'];
		}
		
		//去除多余的数据
		foreach ($data as $index => $value)
		{
			if (!is_int($index))
			{
				if (!in_array($index, $fields))
				{
					unset($data[$index]);
				}
			}
		}
		
		//是否是数字下标
		$is_num_index = array_keys($data) == range(0, count($data)-1,1);
		//补充默认值
		foreach ($this->_desc as $index=>$value)
		{
			if (!$is_num_index)
			{
				if (!isset($data[$value['Field']]))
				{
					if ($value['Null'] == 'YES')
					{
						$data[$value['Field']] = NULL;
					}
					else
					{
						if ($value['Default'] == 'CURRENT_TIMESTAMP')
						{
							$data[$value['Field']] = date('Y-m-d H:i:s');
						}
						else
						{
							if ($value['Default'] === NULL)
							{
								$data[$value['Field']] = 0;
							}
							else
							{
								$data[$value['Field']] = $value['Default'];
							}
						}
					}
				}
			}
		}
		$this->_sql->from($this->_table);
		$sql = $this->_sql->insert($data);
		return $this->query($sql);
		
		
	}
	
	/**
	 * 删除
	 * @return boolean
	 */
	function delete()
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->delete();
		return $this->query($sql);
	}
	
	/**
	 * 执行自定义sql
	 * @param unknown $sql
	 * @param array $array
	 * @return boolean
	 */
	function query($sql,$array = array())
	{
		if ($sql instanceof sql)
		{
			self::$_history[] = $sql->getSql();
			$array = $sql->getParams();
			$sql_string = $sql->__toString();
			$sql->clear();
			$sql = $sql_string;
		}
		else
		{
			self::$_history[] = $this->_sql->getSql($sql,$array);
		}
		return $this->_db->query($sql,$array);
	}
	
	/**
	 * 事务开始
	 */
	function transaction()
	{
		return $this->_db->transaction();
	}
	
	/**
	 * 事务提交
	 */
	function commit()
	{
		return $this->_db->commit();
	}
	
	/**
	 * 事务回滚
	 */
	function rollback()
	{
		return $this->_db->rollback();
	}
	
	/**
	 * 上一个插入的ID
	 * @param unknown $name
	 */
	function lastInsertId($name = NULL)
	{
		return $this->_db->lastInsert($name);
	}
}