<?php
namespace framework\core\database;

use framework\core\base;
use framework\vendor\encryption;

abstract class sql extends base
{
	protected $_temp = array(
		'do' => '',
	);
	
	/**
	 * 清空sql中的所有数据
	 * @return $this
	 */
	public function clear()
	{
		$this->_temp = array(
			'do' => '',
		);
		return $this;
	}
	
	/**
	 * between(a,1,10,'and')
	 *
	 * @param unknown $field
	 * @param unknown $a
	 * @param unknown $b
	 * @param string $combine
	 * @return $this
	 */
	function between($field, $a, $b, $combine = 'and')
	{
		$this->where($field . ' BETWEEN ? and ?', array(
			$a,
			$b
		), $combine);
		return $this;
	}
	
	/**
	 * @param unknown $field
	 * @param unknown $a
	 * @param unknown $b
	 * @param string $combine
	 * @return \framework\core\database\sql
	 */
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
	 * @return $this
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
	 * @return $this
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
	
	/**
	 * @param unknown $field
	 * @param string $order
	 * @return \framework\core\database\sql
	 */
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
	
	/**
	 * @param unknown $fields
	 * @return \framework\core\database\sql
	 */
	function group($fields)
	{
		if (is_array($fields))
		{
			$fields = implode(',', $fields);
		}
		$this->_temp['group'] = ' GROUP BY ' . $fields;
		return $this;
	}
	
	
	
	/**
	 * @param unknown $start
	 * @param unknown $length
	 * @return $this
	 */
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
	 * 清空除fields和limit外的所有信息
	 * @return $this
	 */
	public function clearWithoutCondition()
	{
		unset($this->_temp['fields']);
		unset($this->_temp['limit']);
		return $this;
	}
	
	/**
	 * 为一些sql中的字段、表名等添加反引号
	 * @param string $field
	 * @return string
	 */
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
	
	/**
	 * 设置查询表
	 *
	 * @param unknown $table
	 * @return $this
	 */
	function from($table, $as = '')
	{
		if (empty($as))
		{
			if (is_string($table))
			{
				$this->_temp['from'][] = self::fieldFormat($table);
			}
			else
			{
				$this->_temp['from'] = array_map(function($field){
					return self::fieldFormat($field);
				}, $table);
			}
		}
		else
		{
			$this->_temp['from'][$as] = self::fieldFormat($table);
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
	
	/**
	 * @param string $fields
	 * @return $this
	 */
	function select($fields = '*')
	{
		$this->_temp['do'] = 'select';
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
	
	/**
	 * @param unknown $key
	 * @param unknown $value
	 * @return $this
	 */
	function update($key, $value = null)
	{
		$this->_temp['do'] = 'update';
		
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
	 * $this->insert('a',1)->insert('b',2);
	 * $this->insert(['a'=>1,'b'=>2]);
	 * insert into
	 *
	 * @param unknown $name
	 * @param unknown $value
	 * @return $this
	 */
	function insert($name, $value = null)
	{
		$this->_temp['do'] = 'INSERT';
		
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
			$this->_temp['insert'] = $name;
		}
		return $this;
	}
	
	/**
	 * @return $this
	 */
	function delete()
	{
		$this->_temp['do'] = 'DELETE ';
		return $this;
	}
	
	/**
	 * 获取sql中的参数
	 * @return array
	 */
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
		if ($sql === NULL && count($params)==0)
		{
			$sql = $this->__toString();
			$params = $this->getParams();
		}
		//把变量参数和？号参数区分开
		$num_params = array();
		$word_params = array();
		foreach ($params as $index => $value)
		{
			if (is_int($index))
			{
				$num_params[] = is_int($value)?$value:'\'' . $value . '\'';
			}
			else if (is_string($index))
			{
				$word_params[$index] = is_int($value)?$value:'\'' . $value . '\'';
			}
		}
		
		if (!empty($num_params))
		{
			do{
				$guid = encryption::random(12,'lower_word');
			}while(stripos($sql, $guid) !== FALSE);
			
			$sql_s = '';
			foreach (explode('?', $sql) as $k => $item)
			{
				$sql_s .= $item.':'.$guid.'_'.$k;
				if (isset($num_params[$k]))
				{
					$word_params[$guid.'_'.$k] = $num_params[$k];
				}
			}
			$sql = substr($sql_s, 0,strlen($sql_s)-strlen($guid.'_'.$k));
		}
		
		//把:name 替换成对应的数据
		if (!empty($word_params))
		{
			$index = array_map(function($item){
				return ':'.$item;
			}, array_keys($word_params));
			$value = array_values($word_params);
			$sql= str_replace($index, $value, $sql);
		}
		
		return $sql;
	}
	
	
	function __call($method,$arguments)
	{
		exit('unsupport method:'.$method);
	}
	
	abstract function __toString();
}