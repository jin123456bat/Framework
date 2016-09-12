<?php
namespace framework\core\database;

use framework\core\base;

class sql extends base
{
	private $_temp = array();
	
	private $_do = '';
	
	function __construct()
	{

	}
	
	/**
	 * select for update
	 */
	function forUpdate()
	{
		$this->_temp['forUpdate'] = true;
	}
	
	/**
	 * do select
	 * @param unknown $field
	 */
	function select($fields)
	{
		$this->_do = 'select';
		
		if (is_array($fields))
		{
			foreach ($fields as $as => $field)
			{
				if ($field instanceof sql)
				{
					$field = $field->__toString();
				}
				if (is_string($as))
				{
					$this->_temp['fields'][$as] = $field;
				}
				else if (is_int($as))
				{
					$this->_temp['fields'][] = $field;
				}
			}
		}
		else if ($fields instanceof sql)
		{
			$this->_temp['fields'][] = $fields->__toString();
		}
		else if (is_string($fields))
		{
			$this->_temp['fields'][] = $fields;
		}
		return $this;
	}
	
	function update($key,$value = NULL)
	{
		if (empty($this->_do))
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
				if ($value instanceof sql)
				{
					$value = $value->__toString();
				}
				$this->_fields[$key] = $value;
			}
		}
		return $this;
	}
	
	/**
	 * replace into
	 */
	function replace($key,$value = NULL)
	{
		if (empty($this->_do))
		{
			$this->_do = 'REPLACE';
			
			if ($value instanceof sql)
			{
				$value = $value->__toString();
			}
			
			if (is_string($key))
			{
				$this->_fields[] = $key;
				$this->_params[] = $value;
			}
			else if (is_array($key))
			{
				foreach ($key as $index => $val)
				{
					if ($val instanceof sql)
					{
						$val = $val->__toString();
					}
					$this->_fields[] = $index;
					$this->_params[] = $val;
				}
			}
		}
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
				if ($val instanceof sql)
				{
					$val = $val->__toString();
				}
				$this->insert($index, $val);
			}
		}
		else if (is_string($name))
		{
			if ($value instanceof sql)
			{
				$value = $value->__toString();
			}
			$this->_fields[] = $name;
			$this->_params[] = $value;
		}
		else if (is_int($name))
		{
			if ($value instanceof sql)
			{
				$value = $value->__toString();
			}
			$this->_fields[] = '?';
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
		$this->_duplicate = ' ON DUPLICATE KEY UPDATE';
		if (is_array($name))
		{
			foreach ($name as $index => $val)
			{
				$this->_duplicate_name[] = $index;
				$this->_duplicate_value[] = $val;
			}
		}
		else if (is_string($name))
		{
			$this->_duplicate_name[] = $name;
			$this->_duplicate_value[] = $value;
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
		$this->_temp['from'][] = $table;
		return $this;
	}
	
	function where($sql,$array = array(),$combine = 'and')
	{
		if (is_array($sql))
		{
			$sql = '('.implode(') '.$combine.' (', $sql).')';
		}
		if (empty($this->_temp['where']))
		{
			$this->_temp['where'] = ' WHERE ('.$sql.')';
		}
		else
		{
			$this->_temp['where'] = $this->_temp['where'].' '.$combine.' ('.$sql.')';
		}
		
		if (empty($this->_temp['params']))
		{
			$this->_temp['params'] = $array;
		}
		else
		{
			$this->_temp['params'] = array_merge($this->_temp['params'],$array);
		}
		return $this;
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
		$sql_string = array();
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
	
	function order($field,$order = 'ASC')
	{
		if (is_array($field))
		{
			foreach ($field as $asc => $field_temp)
			{
				if (in_array(strtolower(trim($asc)), array('asc','desc')))
				{
					$this->order($field_temp,$asc);
				}
				else if (is_int($asc))
				{
					$this->order($field_temp,$order);
				}
				else
				{
					$this->order($asc,$field_temp);
				}
			}
		}
		else if (is_string($field))
		{
			if (empty($this->_temp['order']))
			{
				$this->_temp['order'] = ' ORDER BY '.$field.' '.$order;
			}
			else
			{
				$this->_temp['order'] .= ','.$field.' '.$order;
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
			$this->_temp['limit'] = ' LIMIT '.$start;
		}
		else
		{
			$this->_temp['limit'] = ' LIMIT '.$start.','.$length;
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
		$this->where($field.' BETWEEN ? and ?',array($a,$b),$combine);
		return $this;
	}
	
	function notbetween($field,$a,$b,$combine = 'and')
	{
		$this->where($field.' NOT BETWEEN ? and ?',array($a,$b),$combine);
		return $this;
	}
	
	function in($field,array $data = array(),$combine = 'and')
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
				$this->where($field.' is NULL',array(),$combine);
			}
		}
		else if (is_string($fields))
		{
			$this->where($fields.' is NULL',array(),$combine);
		}
		return $this;
	}
	
	function having($sql,array $data = array(),$combine = 'and')
	{
		if (is_array($sql))
		{
			foreach ($sql as $string)
			{
				$this->having($string,array(),$combine);
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
		return $this;
	}
	
	function distinct()
	{
		$this->_temp['distinct'] = true;
		return $this;
	}
	
	static public function fieldFormat($field)
	{
		$field = trim($field,'` ');
		$fields = explode('.', $field);
		$string = array();
		foreach ($fields as $value)
		{
			$string[] = '`'.trim($value,'` ').'`';
		}
		return implode(',', $string);
	}
	
	function __toString()
	{
		switch (strtolower(trim($this->_do)))
		{
			case 'insert':
				$sql = 'INSERT ';
				return $sql;
			case 'select':
				$distinct = isset($this->_temp['distinct']) && $this->_temp['distinct']===true?'DISTINCT ':'';
				
				$fields = '*';
				if (isset($this->_temp['fields']) && !empty($this->_temp['fields']))
				{
					$fields = '';
					foreach ($this->_temp['fields'] as $as => $field)
					{
						if(!is_int($as))
						{
							$fields .= $field.' as '.$as.' ';
						}
						else
						{
							$fields .= $field;
						}
					}
					$fields = implode(',', $this->_temp['fields']);
				}
				
				$table = '';
				if (isset($this->_temp['from']) && !empty($this->_temp['from']))
				{
					$table = '`'.implode('`,`',$this->_temp['from']).'`';
				}
				
				$this->_temp['order'] = isset($this->_temp['order'])?$this->_temp['order']:'';
				
				$this->_temp['limit'] = isset($this->_temp['limit'])?$this->_temp['limit']:'';
				
				//for update
				$forUpdate = (isset($this->_temp['forUpdate']) && $this->_temp['forUpdate']===true)?' FOR UPDATE':'';
				
				var_dump($this->_temp['params']);
				
				$sql = 'SELECT '.$distinct.$fields.' FROM '.$table.$this->_temp['where'].$this->_temp['order'].$this->_temp['limit'].$forUpdate;
				return $sql;
			case 'update':
				$sql = 'UPDATE ';
				return $sql;
			case 'delete':
				$sql = 'DELETE ';
				return $sql;
		}
		return '';
	}
	
	function getParams()
	{
		$this->_fields = array_merge($this->_fields,$this->_duplicate_name);
		$this->_params = array_merge($this->_params,$this->_duplicate_value);
		
		return $this->_params;
	}
	
	function clear()
	{
		$this->_temp = array();
		$this->_do = '';
	}
}