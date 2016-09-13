<?php
namespace framework\core;

use framework\core\database\sql;
use framework\core\database\driver\mysql;

class model extends component
{
	private $_table;
	
	private $_sql;
	
	private $_db;
	
	private static $_history = array();
	
	private $_desc;
	
	function __construct($table = NULL)
	{
		$this->_sql = new sql();
		$this->_db = mysql::getInstance($this->getConfig('db'));
		$this->setTable($table);
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
		$this->_desc = $this->query('desc '.$this->_table);
	}
	
	/**
	 * find all rows from result
	 */
	function select($fields = '*')
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->select($fields);
		return $this->query($sql);
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
	
	function update($name,$value = '')
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->update($name,$value);
		var_dump($sql->__toString());
		var_dump($sql->getParams());
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
	
	function delete()
	{
		self::$_history[] = $sql->getSql();
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
}