<?php
namespace framework\core\database;

use framework\core\base;

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
	 * 添加额外的表
	 *
	 * @param unknown $table
	 * @return $this
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
}