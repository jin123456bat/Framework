<?php
namespace framework\core\database\sphinx;

class sql extends \framework\core\database\sql
{
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
	 * @param array|string $sql
	 * @param array $array
	 * @param string $combine
	 * @return $this
	 */
	function where($sql,$array = array())
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
							$this->where(self::fieldFormat($field).' is NULL',array());
						}
						else
						{
							$this->where(self::fieldFormat($field).'=?',array($s));
						}
					}
					else if (is_array($s))
					{
						if (count($s) == 1)
						{
							$this->where(self::fieldFormat($field).'=?',array(current($s)));
						}
						else
						{
							$this->in($field,$s);
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
				$this->_temp['where'] = $this->_temp['where'] . ' and (' . $sql . ')';
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
	 * field in (data1,data2...)
	 * 当data的数据只有一个的时候会自动转化为field = data
	 *
	 * @param unknown $field
	 * @param array $data
	 * @param string $combine
	 * @return $this
	 */
	function in($field, array $data = array())
	{
		$data = array_unique($data);
		if (count($data) > 1)
		{
			$sql = self::fieldFormat($field) . ' IN (' . implode(',', array_fill(0, count($data), '?')) . ')';
			$this->where($sql, $data);
		}
		else if (count($data) == 1)
		{
			$data = array_shift($data);
			if (is_scalar($data))
			{
				$sql = self::fieldFormat($field) . ' = ?';
				$this->where($sql, array(
					$data
				));
			}
		}
		return $this;
	}
	
	function options($data)
	{
		$support = array(
			'agent_query_timeout',
			'boolean_simplify',
			'comment',
			'cutoff',
			'field_weights',
			'global_idf',
			'idf',
			'local_df',
			'index_weights',
			'max_matches',
			'max_query_time',
			'max_predicted_time',
			'ranker',
			'retry_count',
			'retry_delay',
			'reverse_scan',
			'sort_method',
			'rand_seed',
		);
		if (empty($data))
		{
			$this->_temp['option'] = array();
		}
		return $this;
	}
	
	function __toString()
	{
		switch ($this->_temp['do'])
		{
			case 'insert':
				break;
			case 'delete':
				break;
			case 'select':
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
				
				//sphinx的from不支持临时表
				$table = '';
				if (isset($this->_temp['from']) && ! empty($this->_temp['from']))
				{
					if (is_array($this->_temp['from']))
					{
						foreach ($this->_temp['from'] as $as => $from)
						{
							if (is_string($from))
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
				
				$this->_temp['group'] = isset($this->_temp['group']) ? $this->_temp['group'] : '';
				
				$this->_temp['having'] = isset($this->_temp['having']) ? $this->_temp['having'] : '';
				
				$this->_temp['order'] = isset($this->_temp['order']) ? $this->_temp['order'] : '';
				
				$this->_temp['limit'] = isset($this->_temp['limit']) ? $this->_temp['limit'] : '';
				
				$sql = 'select '.$fields.' from '.$table .$this->_temp['where'] . $this->_temp['group'] . $this->_temp['having'] . $this->_temp['order'] . $this->_temp['limit'];
				return $sql;
				break;
			case 'update':
				break;
		}
	}
}