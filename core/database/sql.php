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
	 * 强制索引
	 * force index
	 * 
	 * @param unknown $index        
	 */
	function forceIndex($index)
	{
		if (is_array($index))
		{
			$this->_temp['forceIndex'] = $index;
		}
		else if (is_string($index))
		{
			$this->_temp['forceIndex'] = explode(',', $index);
		}
	}

	/**
	 * do select
	 * 
	 * @param unknown $field        
	 */
	function select($fields = '*')
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

	function update($key, $value = null)
	{
		$this->_do = 'update';
		
		if (is_array($key))
		{
			foreach ($key as $index => $value)
			{
				if (is_string($index))
				{
					$this->_temp['update'][$index] = $value;
				}
			}
		}
		else if (is_string($key))
		{
			$this->_temp['update'][$key] = $value;
		}
		return $this;
	}

	/**
	 * replace into
	 */
	function replace($key, $value = null)
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
	 * 
	 * @param unknown $name        
	 * @param unknown $value        
	 * @return \framework\core\database\sql
	 */
	function insert($name, $value = null)
	{
		$this->_do = 'INSERT';
		
		if (is_array($name))
		{
			foreach ($name as $index => $val)
			{
				$this->insert($index, $val);
			}
		}
		else if (is_string($name))
		{
			$this->_temp['insert'][$name] = $value;
		}
		else if (is_int($name))
		{
			$this->_temp['insert'][] = $value;
		}
		else if ($name instanceof sql && $name->getType() == 'select')
		{
			// insert select
			$this->_temp['insert'] = $name;
		}
		return $this;
	}

	/**
	 * on duplicate key update
	 * 
	 * @param unknown $name        
	 * @param unknown $value        
	 */
	function duplicate($name, $value = null)
	{
		if (! isset($this->_temp['duplicate']))
		{
			$this->_temp['duplicate'] = ' ON DUPLICATE KEY UPDATE ';
		}
		else
		{
			$this->_temp['duplicate'] .= ',';
		}
		if (is_array($name))
		{
			foreach ($name as $index => $val)
			{
				$this->_temp['duplicate'] .= $index . '=:' . $index . '_duplicate,';
				$this->_temp['duplicate_params'][$index . '_duplicate'] = $val;
			}
		}
		else if (is_string($name))
		{
			$this->_temp['duplicate'] .= $name . '=:' . $name . '_duplicate,';
			$this->_temp['duplicate_params'][$name . '_duplicate'] = $value;
		}
		$this->_temp['duplicate'] = rtrim($this->_temp['duplicate'], ',');
		return $this;
	}

	/**
	 * INSERT IGNORE INTO
	 */
	function ignore()
	{
		$this->_temp['ignore'] = true;
		return $this;
	}

	function delete()
	{
		$this->_do = 'DELETE ';
		return $this;
	}

	/**
	 * 添加额外的表
	 * 
	 * @param unknown $table        
	 * @return \framework\core\database\sql
	 */
	function from($table, $as = '')
	{
		if (empty($as))
		{
			$this->_temp['from'][] = self::fieldFormat($table);
		}
		else
		{
			$this->_temp['from'][$as] = self::fieldFormat($table);
		}
		return $this;
	}

	/**
	 * 重新设定from
	 * 
	 * @param unknown $table        
	 * @param string $as        
	 * @return \framework\core\database\sql
	 */
	function setFrom($table, $as = '')
	{
		if ($table instanceof sql)
		{
			$this->_temp['params'] = array_merge($table->getParams(), $this->getParams());
		}
		if (empty($as))
		{
			$this->_temp['from'] = array(
				$table
			);
		}
		else
		{
			$this->_temp['from'] = array(
				$as => $table
			);
		}
		return $this;
	}

	/**
	 * @example
	 * $this->where('id=?',array(1))
	 * select * from table where id=1
	 * 
	 * $this->where('id=:id',array('id'=>1))
	 * select * from table where id=1
	 * 
	 * $this->where(array(
	 * 	'id' => 1,
	 * ))
	 * select * from table where id=1
	 * 
	 * $this->where(array(
	 * 	'name' => array('张三','李四')
	 * ))
	 * select * from table where name in ('张三','李四')
	 * 
	 * @param unknown $sql
	 * @param array $array
	 * @param string $combine
	 * @return \framework\core\database\sql
	 */
	function where($sql, $array = array(), $combine = 'and')
	{
		if (is_array($sql))
		{
			foreach ($sql as $field => $s)
			{
				if (is_string($field))
				{
					if (is_scalar($s))
					{
						if (is_null($s))
						{
							$this->where(self::fieldFormat($field).' is NULL',array(),$combine);
						}
						else
						{
							$this->where(self::fieldFormat($field).'=?',array($s),$combine);
						}
					}
					else if (is_array($s))
					{
						if (count($s) == 1)
						{
							$this->where(self::fieldFormat($field).'=?',array(current($s)),$combine);
						}
						else
						{
							$this->in($field,$s,$combine);
						}
					}
				}
			}
		}
		else if (is_string($sql))
		{
			if (empty($this->_temp['where']))
			{
				$this->_temp['where'] = ' WHERE (' . $sql . ')';
			}
			else
			{
				$this->_temp['where'] = $this->_temp['where'] . ' ' . $combine . ' (' . $sql . ')';
			}
			
			if (empty($this->_temp['params']))
			{
				$this->_temp['params'] = $array;
			}
			else
			{
				$this->_temp['params'] = array_merge($this->_temp['params'], $array);
			}
		}
		return $this;
	}

	/**
	 * like in 扩展方法
	 * 
	 * @param unknown $field        
	 * @param array $array        
	 */
	function likein($field, $array = array())
	{
		if (! empty($array))
		{
			$sql = '';
			foreach ($array as $value)
			{
				$sql .= $field . ' like ? or ';
			}
			$sql = substr($sql, 0, - 4);
			$this->where($sql, $array);
		}
		return $this;
	}

	function join($table, $on, $combine = 'AND')
	{
		$table = '`' . trim($table, '`') . '`';
		if (is_array($on))
		{
			$on = implode(' ' . $combine . ' ', $on);
		}
		if (! empty($this->_temp['join']))
		{
			$this->_temp['join'] .= ' JOIN ' . $table . ' ON ' . $on;
		}
		else
		{
			$this->_temp['join'] = ' JOIN ' . $table . ' ON ' . $on;
		}
		return $this;
	}

	function leftJoin($table, $on, $combine = 'AND')
	{
		$table = '`' . trim($table, '`') . '`';
		if (is_array($on))
		{
			$on = implode(' ' . $combine . ' ', $on);
		}
		if (! empty($this->_temp['join']))
		{
			$this->_temp['join'] .= ' LEFT JOIN ' . $table . ' ON ' . $on;
		}
		else
		{
			$this->_temp['join'] = ' LEFT JOIN ' . $table . ' ON ' . $on;
		}
		return $this;
	}

	function rightJoin($table, $on, $combine = 'AND')
	{
		$table = '`' . trim($table, '`') . '`';
		if (is_array($on))
		{
			$on = implode(' ' . $combine . ' ', $on);
		}
		if (! empty($this->_temp['join']))
		{
			$this->_temp['join'] .= ' RIGHT JOIN ' . $table . ' ON ' . $on;
		}
		else
		{
			$this->_temp['join'] = ' RIGHT JOIN ' . $table . ' ON ' . $on;
		}
		return $this;
	}

	function innerJoin($table, $on, $combine = 'AND')
	{
		$table = '`' . trim($table, '`') . '`';
		if (is_array($on))
		{
			$on = implode(' ' . $combine . ' ', $on);
		}
		if (! empty($this->_temp['join']))
		{
			$this->_temp['join'] .= ' INNER JOIN ' . $table . ' ON ' . $on;
		}
		else
		{
			$this->_temp['join'] = ' INNER JOIN ' . $table . ' ON ' . $on;
		}
		return $this;
	}

	function fullJoin($table, $on, $combine = 'AND')
	{
		$table = '`' . trim($table, '`') . '`';
		if (is_array($on))
		{
			$on = implode(' ' . $combine . ' ', $on);
		}
		if (! empty($this->_temp['join']))
		{
			$this->_temp['join'] .= ' FULL JOIN ' . $table . ' ON ' . $on;
		}
		else
		{
			$this->_temp['join'] = ' FULL JOIN ' . $table . ' ON ' . $on;
		}
		return $this;
	}

	function union($all = false, $sql_)
	{
		$all = false;
		$sqls = func_get_args();
		$sql_string = array();
		$this->_temp['other_sql_params'] = isset($this->_temp['other_sql_params']) ? $this->_temp['other_sql_params'] : array();
		foreach ($sqls as $index => $sql)
		{
			if ($sql === true)
			{
				$all = true;
			}
			else if ($sql instanceof sql)
			{
				$sql_string[] = $sql->__toString();
				$this->_temp['other_sql_params'] = array_merge($this->_temp['other_sql_params'], $sql->getParams());
			}
			else if (is_string($sql))
			{
				$sql_string[] = $sql;
			}
		}
		$this->_temp['union'] = $this->__toString() . ' UNION ' . ($all ? 'ALL ' : '') . implode(' UNION ' . ($all ? 'ALL ' : ''), $sql_string);
		return $this;
	}

	function order($field, $order = 'ASC')
	{
		if (is_array($field))
		{
			foreach ($field as $asc => $field_temp)
			{
				if (in_array(strtolower(trim($asc)), array(
					'asc',
					'desc'
				)))
				{
					$this->order($field_temp, $asc);
				}
				else if (is_int($asc))
				{
					$this->order($field_temp, $order);
				}
				else
				{
					$this->order($asc, $field_temp);
				}
			}
		}
		else if (is_string($field))
		{
			if (empty($this->_temp['order']))
			{
				$this->_temp['order'] = ' ORDER BY ' . $field . ' ' . $order;
			}
			else
			{
				$this->_temp['order'] .= ',' . $field . ' ' . $order;
			}
		}
		return $this;
	}

	function group($fields)
	{
		if (is_array($fields))
		{
			$fields = implode(',', $fields);
		}
		$this->_temp['group'] = ' GROUP BY ' . $fields;
		return $this;
	}

	function limit($start, $length = null)
	{
		if ($length === null)
		{
			$this->_temp['limit'] = ' LIMIT ' . $start;
		}
		else
		{
			$this->_temp['limit'] = ' LIMIT ' . $start . ',' . $length;
		}
		return $this;
	}

	/**
	 * between(a,1,10,'and')
	 * 
	 * @param unknown $field        
	 * @param unknown $a        
	 * @param unknown $b        
	 * @param string $combine        
	 * @return \framework\core\database\sql
	 */
	function between($field, $a, $b, $combine = 'and')
	{
		$this->where($field . ' BETWEEN ? and ?', array(
			$a,
			$b
		), $combine);
		return $this;
	}

	function notbetween($field, $a, $b, $combine = 'and')
	{
		$this->where($field . ' NOT BETWEEN ? and ?', array(
			$a,
			$b
		), $combine);
		return $this;
	}

	/**
	 * field in (data1,data2...)
	 * 当data的数据只有一个的时候会自动转化为field = data
	 * 
	 * @param unknown $field        
	 * @param array $data        
	 * @param string $combine        
	 * @return \framework\core\database\sql
	 */
	function in($field, array $data = array(), $combine = 'and')
	{
		$data = array_unique($data);
		if (count($data) > 1)
		{
			$sql = self::fieldFormat($field) . ' IN (' . implode(',', array_fill(0, count($data), '?')) . ')';
			$this->where($sql, $data, $combine);
		}
		else if (count($data) == 1)
		{
			$data = array_shift($data);
			if (is_scalar($data))
			{
				$sql = self::fieldFormat($field) . ' = ?';
				$this->where($sql, array(
					$data
				), $combine);
			}
		}
		return $this;
	}

	/**
	 * field not in (data1,data2...)
	 * 当data的数据只有一个的时候会自动转化为field = data
	 * 
	 * @param unknown $field        
	 * @param array $data        
	 * @param string $combine        
	 * @return \framework\core\database\sql
	 */
	function notIn($field, array $data = array(), $combine = 'and')
	{
		if (! empty($data))
		{
			if (count($data) > 1)
			{
				$sql = self::fieldFormat($field) . ' NOT IN (' . implode(',', array_fill(0, count($data), '?')) . ')';
				$this->where($sql, $data, $combine);
			}
			else if (count($data) == 1)
			{
				$data = array_shift($data);
				if (is_scalar($data))
				{
					$sql = self::fieldFormat($field) . ' != ?';
					$this->where($sql, array(
						$data
					), $combine);
				}
			}
		}
		return $this;
	}

	function isNULL($fields, $combine = 'and')
	{
		if (is_array($fields))
		{
			$string = array();
			foreach ($fields as $field)
			{
				$field = self::fieldFormat($field);
				$string[] = $field.' is NULL';
			}
			
			if (!empty($string))
			{
				$this->where(implode(' '.$combine.' ', $string));
			}
		}
		else if (is_string($fields))
		{
			$fields = self::fieldFormat($fields);
			$this->where($fields . ' is NULL');
		}
		return $this;
	}

	function having($sql, array $data = array(), $combine = 'and')
	{
		if (is_array($sql))
		{
			$sql = '(' . implode(') ' . $combine . ' (', $sql) . ')';
		}
		
		if (empty($this->_temp['having']))
		{
			$this->_temp['having'] = ' HAVING (' . $sql . ')';
		}
		else
		{
			$this->_temp['having'] .= ' ' . $combine . ' (' . $sql . ')';
		}
		
		if (empty($this->_temp['_having_params']))
		{
			$this->_temp['_having_params'] = $data;
		}
		else
		{
			$this->_temp['_having_params'] = array_merge($this->_temp['_having_params'], $data);
		}
		return $this;
	}

	function distinct()
	{
		$this->_temp['distinct'] = true;
		return $this;
	}

	public static function fieldFormat($field)
	{
		$field = trim($field, '` ');
		$fields = explode('.', $field);
		$string = array();
		foreach ($fields as $value)
		{
			$string[] = '`' . trim($value, '` ') . '`';
		}
		return implode('.', $string);
	}

	function __toString()
	{
		if (isset($this->_temp['union']) && ! empty($this->_temp['union']))
		{
			return $this->_temp['union'];
		}
		switch (strtolower(trim($this->_do)))
		{
			case 'insert':
				$table = '';
				if (isset($this->_temp['from']) && ! empty($this->_temp['from']))
				{
					$table = implode(',', $this->_temp['from']);
				}
				
				$this->_temp['ignore'] = isset($this->_temp['ignore']) && $this->_temp['ignore'] ? ' IGNORE' : '';
				
				$sql = '';
				if (isset($this->_temp['insert']))
				{
					$this->_temp['duplicate'] = isset($this->_temp['duplicate']) ? $this->_temp['duplicate'] : '';
					if (is_array($this->_temp['insert']))
					{
						// 数字下标
						if (array_keys($this->_temp['insert']) === range(0, count($this->_temp['insert']) - 1, 1))
						{
							$fields = '';
							$this->_temp['params'] = array_values($this->_temp['insert']);
							$values = array_fill(0, count($this->_temp['params']), '?');
							
						}
						else
						{
							// 字符串下标
							$fields = '(`' . implode('`,`', array_keys($this->_temp['insert'])) . '`)';
							$this->_temp['params'] = $this->_temp['insert'];
							$values = array_map(function ($value) {
								return ':' . $value;
							}, array_keys($this->_temp['insert']));
						}
						$sql = 'INSERT' . $this->_temp['ignore'] . ' INTO ' . $table . ' ' . $fields . ' VALUES (' . implode(',', $values) . ') ' . $this->_temp['duplicate'];
					}
					else if ($this->_temp['insert'] instanceof sql)
					{
						
						$sql = 'INSERT' . $this->_temp['ignore'] . ' INTO ' . $table . ' ' . $this->_temp['insert']->__toString() . ' ' . $this->_temp['duplicate'];
						$this->_temp['params'] = $this->_temp['insert']->getParams();
					}
				}
				return $sql;
			case 'select':
				$distinct = isset($this->_temp['distinct']) && $this->_temp['distinct'] === true ? 'DISTINCT ' : '';
				
				$fields = '*';
				if (isset($this->_temp['fields']) && ! empty($this->_temp['fields']))
				{
					$fields = '';
					foreach ($this->_temp['fields'] as $as => $field)
					{
						if (! is_int($as))
						{
							$fields .= $field . ' as ' . $as . ',';
						}
						else
						{
							$fields .= $field . ',';
						}
					}
					$fields = rtrim($fields, ',');
				}
				
				$table = '';
				if (isset($this->_temp['from']) && ! empty($this->_temp['from']))
				{
					if (is_array($this->_temp['from']))
					{
						foreach ($this->_temp['from'] as $as => $from)
						{
							if ($from instanceof sql)
							{
								if (is_int($as))
								{
									$table .= '(' . $from->__toString() . '),';
								}
								else
								{
									$table .= '(' . $from->__toString() . ') as ' . $as . ',';
								}
							}
							else if (is_string($from))
							{
								if (is_int($as))
								{
									$table .= $from . ',';
								}
								else
								{
									$table .= $from . ' as ' . $as . ',';
								}
							}
						}
						$table = substr($table, 0, - 1);
					}
				}
				
				$this->_temp['where'] = isset($this->_temp['where']) ? $this->_temp['where'] : '';
				
				$this->_temp['join'] = isset($this->_temp['join']) ? $this->_temp['join'] : '';
				
				$this->_temp['group'] = isset($this->_temp['group']) ? $this->_temp['group'] : '';
				
				$this->_temp['having'] = isset($this->_temp['having']) ? $this->_temp['having'] : '';
				
				$this->_temp['order'] = isset($this->_temp['order']) ? $this->_temp['order'] : '';
				
				$this->_temp['limit'] = isset($this->_temp['limit']) ? $this->_temp['limit'] : '';
				
				$this->_temp['forceIndex'] = isset($this->_temp['forceIndex']) && is_array($this->_temp['forceIndex']) ? ' FORCE INDEX(' . implode(',', $this->_temp['forceIndex']) . ') ' : '';
				// for update
				$forUpdate = (isset($this->_temp['forUpdate']) && $this->_temp['forUpdate'] === true) ? ' FOR UPDATE' : '';
				
				$sql = 'SELECT ' . $distinct . $fields . ' FROM ' . $table . $this->_temp['forceIndex'] . $this->_temp['join'] . $this->_temp['where'] . $this->_temp['group'] . $this->_temp['having'] . $this->_temp['order'] . $this->_temp['limit'] . $forUpdate;
				return $sql;
			case 'update':
				$table = '';
				if (isset($this->_temp['from']) && ! empty($this->_temp['from']))
				{
					$table = implode(',', $this->_temp['from']);
				}
				
				$set = ' SET ';
				if (isset($this->_temp['update']) && is_array($this->_temp['update']))
				{
					foreach ($this->_temp['update'] as $index => $value)
					{
						if (substr($index, - 2) == '+=')
						{
							$index = substr($index, 0, - 2);
							$set .= self::fieldFormat($index) . '=' . self::fieldFormat($index) . '+' . $value . ',';
						}
						else if (substr($index, - 2) == '-=')
						{
							$index = substr($index, 0, - 2);
							$set .= self::fieldFormat($index) . '=' . self::fieldFormat($index) . '-' . $value . ',';
						}
						else
						{
							$set .= self::fieldFormat($index) . '=\'' . addslashes($value) . '\',';
						}
					}
					$set = rtrim($set, ',');
				}
				else
				{
					$set = '';
				}
				
				$this->_temp['where'] = isset($this->_temp['where']) ? $this->_temp['where'] : '';
				
				$this->_temp['limit'] = isset($this->_temp['limit']) ? $this->_temp['limit'] : '';
				
				$sql = 'UPDATE ' . $table . $set . $this->_temp['where'] . $this->_temp['limit'];
				
				return $sql;
			case 'delete':
				$table = '';
				if (isset($this->_temp['from']) && ! empty($this->_temp['from']))
				{
					$table = implode(',', $this->_temp['from']);
				}
				
				$this->_temp['where'] = isset($this->_temp['where']) ? $this->_temp['where'] : '';
				
				$this->_temp['order'] = isset($this->_temp['order']) ? $this->_temp['order'] : '';
				
				$this->_temp['limit'] = isset($this->_temp['limit']) ? $this->_temp['limit'] : '';
				
				$sql = 'DELETE FROM ' . $table . $this->_temp['where'] . $this->_temp['order'] . $this->_temp['limit'];
				return $sql;
		}
		return '';
	}

	function getParams()
	{
		$this->_temp['params'] = isset($this->_temp['params']) ? $this->_temp['params'] : array();
		$this->_temp['_having_params'] = isset($this->_temp['_having_params']) ? $this->_temp['_having_params'] : array();
		$this->_temp['duplicate_params'] = isset($this->_temp['duplicate_params']) ? $this->_temp['duplicate_params'] : array();
		$this->_temp['other_sql_params'] = isset($this->_temp['other_sql_params']) ? $this->_temp['other_sql_params'] : array();
		
		return array_merge($this->_temp['params'], $this->_temp['_having_params'], $this->_temp['duplicate_params'], $this->_temp['other_sql_params']);
	}

	/**
	 * 关联params和sql后的sql
	 */
	function getSql($sql = null, $params = array())
	{
		if (empty($sql))
		{
			$sql = $this->__toString();
		}
		
		// 去掉sql中的百分号
		$sql = str_replace('%', '#', $sql);
		
		$sql_s = str_replace('?', '%s', $sql);
		if (empty($params))
		{
			$params = $this->getParams();
		}
		// echo $sql.'<br>|';
		
		$num_params = array();
		$word_params = array();
		foreach ($params as $index => $value)
		{
			if (is_int($index))
			{
				$num_params[] = '\'' . $value . '\'';
			}
			else
			{
				$word_params[$index] = '\'' . $value . '\'';
			}
		}
		
		// 排序，防止出现 a把ab替换掉了
		uksort($word_params, function ($a, $b) {
			if (strlen($a) > strlen($b))
			{
				return - 1;
			}
			elseif (strlen($a) == strlen($b))
			{
				return 0;
			}
			return 1;
		});
		
		$sql_w = vsprintf($sql_s, $num_params);
		// 把#替换为% 恢复sql
		$sql_w = str_replace('#', '%', $sql_w);
		
		foreach ($word_params as $index => $value)
		{
			$sql_w = str_replace(':' . $index, $value, $sql_w);
		}
		return $sql_w;
	}

	/**
	 * 除了from外都清空
	 */
	function clear()
	{
		// 保留from
		$from = $this->_temp['from'];
		$this->_temp = array(
			'from' => $from
		);
		$this->_do = '';
	}
	
	/**
	 * 清空fields和limit
	 */
	function clearWithoutCondition()
	{
		unset($this->_temp['fields']);
		unset($this->_temp['limit']);
	}

	/**
	 * sql查询的类型
	 * 
	 * @return string
	 */
	function getType()
	{
		return strtolower(trim($this->_do));
	}
}
