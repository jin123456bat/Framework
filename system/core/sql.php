<?php
namespace core;

class sql extends base
{
	/**
	 * 执行的方法select update insert delete
	 * @var unknown
	 */
	private $_do;
	
	/**
	 * 当为select的时候 select的字段
	 * @var array
	 */
	private $_fields = ['*'];
	
	/**
	 * from的table
	 * @var unknown
	 */
	private $_from;
	
	/**
	 * join等sql
	 * @var unknown
	 */
	private $_join;
	
	/**
	 * where的sql
	 * @var array
	 */
	private $_where = [];
	
	/**
	 * sql中的所有参数
	 * @var array
	 */
	private $_params = [];
	
	private $_limit;
	
	private $_order;
	
	private $_group;
	
	private $_top;
	
	function __construct()
	{
		
	}
	
	/**
	 * 
	 * @param unknown $field
	 */
	function select($fields)
	{
		$this->_do = 'SELECT ';
		
		if (is_array($fields))
		{
			foreach ($fields as $as => $field)
			{
				if ($field instanceof sql)
				{
					if (is_string($as))
					{
						$this->_fields[$as] = $field->__toString();
					}
					else if (is_int($as))
					{
						$this->_fields[] = $field->__toString();
					}
				}
				else
				{
					if (is_string($as))
					{
						$this->_fields[$as] = $field;
					}
					else if (is_int($as))
					{
						$this->_fields[] = $field;
					}
				}
			}
		}
		else if ($fields instanceof sql)
		{
			$this->_fields[] = $fields->__toString();
		}
		else if (is_string($fields))
		{
			$this->_fields[] = $fields;
		}
		return $this;
	}
	
	function update($key,$value = NULL)
	{
		$this->_do = 'UPDATE ';
		return $this;
	}
	
	function insert($data)
	{
		$this->_do = 'INSERT ';
		return $this;
	}
	
	function delete()
	{
		$this->_do = 'DELETE ';
		return $this;
	}
	
	function from($table)
	{
		$table = '`'.trim($table,'`').'`';
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
	
	function where($sql,$array = [],$combine = 'and')
	{
		if (empty($this->_where))
		{
			$this->_where = ' where ('.$sql.')';
		}
		else
		{
			$this->_where = $this->_where.' '.$combine.' ('.$sql.')';
		}
	}
	
	function join($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' join `'.$table.'` on '.$on;
		return $this;
	}
	
	function leftJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' left join `'.$table.'` on '.$on;
		return $this;
	}
	
	function rightJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' right join `'.$table.'` on '.$on;
		return $this;
	}
	
	function innerJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' inner join `'.$table.'` on '.$on;
		return $this;
	}
	
	function fullJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' full join `'.$table.'` on '.$on;
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
		if ($length === NULL)
		{
			$this->_limit = ' limit '.$start;
		}
		else
		{
			$this->_limit = ' limit '.$start.','.$length;
		}
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
	
	function distinct()
	{
		
	}
	
	function __toString()
	{
		switch (strtolower(trim($this->_do)))
		{
			case 'select':
				return $this->_do.' '.implode(',', $this->_fields).' FROM '.$this->_from.' '.$this->_join.' '.$this->_where.$this->_limit;
			case 'insert':
				return ;
		}
	}
}