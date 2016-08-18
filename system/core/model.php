<?php
namespace framework\core;
class model extends component
{
	private $_table;
	
	private $_sql;
	
	function __construct($table = NULL)
	{
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
	function select()
	{
		
	}
	
	/**
	 * find a row from result
	 */
	function find()
	{
		
	}
	
	/**
	 * find the first field's value from frist row
	 */
	function scalar()
	{
		
	}
}