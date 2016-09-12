<?php
namespace framework\core;

use framework\core\database\sql;
use framework\core\database\driver\mysql;

class model extends component
{
	private $_table;
	
	private $_sql;
	
	private $_db;
	
	function __construct($table = NULL)
	{
		$this->_sql = new sql();
		$this->_db = mysql::getInstance($this->getConfig('db'));
		$this->setTable($table);
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
		
	}
	
	/**
	 * find all rows from result
	 */
	function select($fields = '*')
	{
		$this->_sql->from($this->_table);
		$sql = $this->_sql->select($fields);
		var_dump($sql->__toString());
		exit();
		return $this->_db->query($sql);
	}
	
	/**
	 * find a row from result
	 */
	function find($fields = '*')
	{
		$result = $this->select();
		return isset($result[0])?$result[0]:NULL;
	}
	
	/**
	 * find the first field's value from frist row
	 */
	function scalar($field)
	{
		foreach ($this->find($field) as $index => $value)
		{
			return $value;
		}
	}
	
	function update()
	{
		
	}
	
	function insert()
	{
		
	}
	
	function delete()
	{
		
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
			$sql = $sql->__toString();
			$array = $sql->getParams();
		}
		return $this->_db->query($sql,$array);
	}
}