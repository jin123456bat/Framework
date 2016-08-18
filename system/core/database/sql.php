<?php
namespace framework\core\database;

use framework\core\base;

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
	
	/**
	 * limit in sql
	 * ' limit start,length' or ' limit length'
	 * @var unknown
	 */
	private $_limit;
	
	private $_order;
	
	private $_group;
	
	private $_having;
	
	private $_having_params = [];
	
	private $_distinct = false;
	
	private $_ignore = false;
	
	private $_duplicate;
	
	function __construct()
	{
		
	}
	
	/**
	 * do select
	 * @param unknown $field
	 */
	function select($fields)
	{
		$this->_do = 'SELECT';
		
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
		$this->_do = 'UPDATE';
		
		if (is_array($key))
		{
			foreach ($key as $index=>$value)
			{
				if ($value instanceof sql)
				{
					$value = $value->__toString();
				}
				if (is_string($index))
				{
					$this->_fields[$index] = $value;
				}
			}
		}
		else if (is_string($key))
		{
			$this->_fields[$key] = $value;
		}
		
		return $this;
	}
	
	/**
	 * replace into
	 */
	function replace()
	{
		$this->_do = 'REPLACE';
		return $this;
	}
	
	/**
	 * $this->insert('a',1)->insert('b',2);
	 * $this->insert(['a'=>1,'b'=>2]);
	 * insert into
	 * @param unknown $name
	 * @param unknown $value
	 * @return \framework\core\database\sql
	 */
	function insert($name,$value = NULL)
	{
		$this->_do = 'INSERT';
		
		if (is_array($name))
		{
			foreach ($name as $index => $val)
			{
				$this->insert($index, $val);
			}
		}
		else if (is_string($name) && !empty($name))
		{
			$this->_fields[] = $name;
			$this->_params[] = $value;
		}
		return $this;
	}
	
	/**
	 * on duplicate key update
	 * @param unknown $name
	 * @param unknown $value
	 */
	function onDuplicateKeyUpdate($name,$value = NULL)
	{
		if (is_array($name))
		{
			foreach ($name as $index => $val)
			{
				
			}
		}
		else if (is_string($name))
		{
			$this->_duplicate = ' ON DUPLICATE KEY UPDATE SET '.$name.'='.$value;
		}
		return $this;
	}
	
	/**
	 * INSERT IGNORE INTO
	 */
	function ignore()
	{
		$this->_ignore = true;
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
		if (is_array($sql))
		{
			$sql = '('.implode(') '.$combine.' (', $sql).')';
		}
		if (empty($this->_where))
		{
			$this->_where = ' WHERE ('.$sql.')';
		}
		else
		{
			$this->_where = $this->_where.' '.$combine.' ('.$sql.')';
		}
		
		if (empty($this->_params))
		{
			$this->_params = $array;
		}
		else
		{
			$this->_params = array_merge($this->_params,$array);
		}
	}
	
	function join($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' JOIN `'.$table.'` ON '.$on;
		return $this;
	}
	
	function leftJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' LEFT JOIN `'.$table.'` ON '.$on;
		return $this;
	}
	
	function rightJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' RIGHT JOIN `'.$table.'` ON '.$on;
		return $this;
	}
	
	function innerJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' INNER JOIN `'.$table.'` ON '.$on;
		return $this;
	}
	
	function fullJoin($table,$on)
	{
		$table = '`'.trim($table).'`';
		$this->_join = ' FULL JOIN `'.$table.'` ON '.$on;
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
		if (is_array($field))
		{
			foreach ($field as $field_temp)
			{
				$this->order($field_temp,$order);
			}
		}
		else if (is_string($field))
		{
			if (empty($this->_order))
			{
				$this->_order = ' ORDER BY '.$field.' '.$order;
			}
			else
			{
				$this->_order .= ','.$field.' '.$order;
			}
		}
		return $this;
	}
	
	function group($field)
	{
		$this->_group = ' GROUP BY '.$field;
		return $this;
	}
	
	function limit($start,$length = NULL)
	{
		if ($length === NULL)
		{
			$this->_limit = ' LIMIT '.$start;
		}
		else
		{
			$this->_limit = ' LIMIT '.$start.','.$length;
		}
		return $this;
	}
	
	/**
	 * between(a,1,10,'and')
	 * @param unknown $field
	 * @param unknown $a
	 * @param unknown $b
	 * @param string $combine
	 * @return \framework\core\database\sql
	 */
	function between($field,$a,$b,$combine = 'and')
	{
		$this->where($field.' BETWEEN ? and ?',[$a,$b],$combine);
		return $this;
	}
	
	function notbetween($field,$a,$b,$combine = 'and')
	{
		$this->where($field.' NOT BETWEEN ? and ?',[$a,$b],$combine);
		return $this;
	}
	
	function in($field,array $data = [],$combine = 'and')
	{
		if ( array_keys($data) !== range(0, count($data) - 1) )
		{
			$sql= '';
			foreach ($data as $index => $value)
			{
				$sql .= ':'.$index.',';
			}
			$sql = $field.' IN ('.rtrim($sql,',').')';
		}
		else
		{
			$sql = $field.' IN ('.implode(',', array_fill(0, count($data), '?')).')';
	    }
	    $this->where($sql,$data,$combine);
		return $this;
	}
	
	function isNULL($fields,$combine = 'and')
	{
		if (is_array($fields))
		{
			foreach ($fields as $field)
			{
				$this->where($field.' is NULL',[],$combine);
			}
		}
		else if (is_string($fields))
		{
			$this->where($fields.' is NULL',[],$combine);
		}
	}
	
	function having($sql,array $data = [],$combine = 'and')
	{
		if (is_array($sql))
		{
			foreach ($sql as $string)
			{
				$this->having($string,[],$combine);
			}
		}
		else
		{
			if (empty($this->_having))
			{
				$this->_having = ' HAVING '.$sql;
			}
			else
			{
				$this->_having .= ' '.$combine.' '.$sql;
			}
		}
		
		if (empty($this->_having_params))
		{
			$this->_having_params = $data;
		}
		else
		{
			$this->_having_params = array_merge($this->_having_params,$data);
		}
	}
	
	function distinct()
	{
		$this->_distinct = true;
	}
	
	function __toString()
	{
		switch (strtolower(trim($this->_do)))
		{
			case 'select':
				return $this->_do.' '.($this->_distinct?'DISTINCT':'').' '.
				implode(',', $this->_fields).
				' FROM '.
				$this->_from.' '.
				$this->_join.' '.
				$this->_where.
				$this->_limit.
				$this->_group.
				$this->_having;
			case 'insert':
				return $this->_do.' '.
					($this->_ignore?'IGNORE':'').' INTO '.
					$this->_from.
					' ('.implode(',',$this->_fields).') VALUES ('.implode(',',array_map(function($value){return ':'.$value;}, $this->_fields)).')'.
					$this->_duplicate;	
		}
	}
	
	function getParams()
	{
		return $this->_params;
	}
}