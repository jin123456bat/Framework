<?php
namespace core;

class sql extends base
{
	private $_do;
	
	private $_from;
	
	private $_where;
	
	function __construct()
	{
		
	}
	
	/**
	 * 
	 * @param unknown $field
	 */
	function select($field)
	{
		$this->_do = 'select ';
		return $this;
	}
	
	function update()
	{
		$this->_do = 'update ';
		return $this;
	}
	
	function insert()
	{
		$this->_do = 'insert ';
		return $this;
	}
	
	function delete()
	{
		$this->_do = 'delete';
		return $this;
	}
	
	function from($table)
	{
		if (!empty($this->_from))
		{
			$this->_from = $this->_from.','.$table;
		}
		else
		{
			$this->_from = $table;
		}
		return $this;
	}
	
	function where($field,$value,$operation = '=',$combine = 'and')
	{
		
	}
	
	function join()
	{
		
	}
	
	function leftJoin()
	{
		
	}
	
	function rightJoin($table,$on)
	{
		
	}
	
	function innerJoin($table,$on)
	{
		
	}
	
	function fullJoin($table,$on)
	{
		return $this;
	}
	
	function union($all = false,$sql1,$sql2,$sql_)
	{
		$all = false;
		$sqls = func_get_args();
		$sql_string = [];
		foreach ($sqls as $index => $sql)
		{
			if ($sql === true)
			{
				$all = true;
			}
			else if ($sql instanceof sql)
			{
				$sql_string[] = $sql->__toString();
			}
			else if (is_string($sql))
			{
				$sql_string[] = $sql;
			}
		}
		return implode(' UNION '.($all?'ALL ':''), $sql_string);
	}
	
	function order($field,$order = 'asc')
	{
		return $this;
	}
	
	function group()
	{
		return $this;
	}
	
	function limit($start,$length = NULL)
	{
		return $this;
	}
	
	function between($field,$a,$b)
	{
		return $this;
	}
	
	function top($number)
	{
		
	}
	
	function in($field,array $data = [])
	{
		
	}
	
	function isNULL($fields)
	{
		if (is_array($fields))
		{
			
		}
		else
		{
			
		}
	}
	
	function having()
	{
		
	}
	
	function __toString()
	{
		
	}
}