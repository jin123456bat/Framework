<?php
namespace framework\core;

use framework\core\database\sql;
use framework\core\database\driver\mysql;
use framework\core\database\mysql\table;

class model extends component
{

	private $_table;

	private $_sql;

	/**
	 * @var \framework\core\database\driver\mysql
	 */
	private $_db;

	private static $_history = array();

	private $_desc;

	private $_compress = false;

	private $_compress_sql = array();

	function __construct($table = null)
	{
		$this->_table = $table;
	}

	/**
	 * show variables like $name
	 *
	 * @param string $name        	
	 * @return NULL|boolean
	 */
	public function getVariables($name = '')
	{
		if (! empty($name))
		{
			$result = $this->query('show variables like ?', array(
				$name
			));
			return isset($result[0]['Value']) ? $result[0]['Value'] : null;
		}
		return $this->query('show variables');
	}

	public static function debug_trace_sql()
	{
		return self::$_history;
	}

	/**
	 * when this class is initlized,this function will be execute
	 *
	 * {@inheritdoc}
	 *
	 * @see \core\component::initlize()
	 */
	function initlize()
	{
		$this->_sql = new sql();
		
		if (method_exists($this, '__config'))
		{
			$db = $this->__config();
		}
		else
		{
			$db = self::getDefaultDbConfig();
		}
		
		$this->_db = mysql::getInstance($db);
		
		if (method_exists($this, '__tableName'))
		{
			$this->_table = $this->__tableName();
		}
		
		$this->setTable($this->_table);
		parent::initlize();
	}

	/**
	 *
	 * @param array|string $connection        	
	 * @return \framework\core\database\database
	 */
	public static function getConnection($connection)
	{
		if (! empty($connection) && is_scalar($connection))
		{
			$db = self::getConfig('db');
			$config = $db[$connection];
		}
		else if (is_array($connection))
		{
			$config = $connection;
		}
		return mysql::getInstance($config);
	}

	/**
	 * 获取DB配置
	 * @return NULL|mixed
	 */
	private static function getDefaultDbConfig()
	{
		$db = self::getConfig('db');
		
		// 判断是否是多个db配置 多个db配置查询带default=true的 没有default=true的使用第一个
		if (! isset($db['db_type']))
		{
			$firstDb = null;
			foreach ($db as $d)
			{
				if (empty($firstDb))
				{
					$firstDb = $d;
				}
				if (isset($d['default']) && $d['default'])
				{
					return $d;
				}
			}
			return $firstDb;
		}
		return $db;
	}

	/**
	 * only for sql
	 *
	 * @param unknown $name        	
	 * @param unknown $args        	
	 * @return \framework\core\model
	 */
	function __call($name, $args)
	{
		call_user_func_array(array(
			$this->_sql,
			$name
		), $args);
		return $this;
	}

	/**
	 * set database table's name
	 *
	 * @param unknown $table        	
	 */
	function setTable($table)
	{
		$this->_table = $table;
		$this->_sql->from($this->_table);
		$this->parse();
	}

	/**
	 * get this database table's name
	 *
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
		$this->_desc = $this->query('DESC `' . $this->_table . '`');
		$this->_config['max_allowed_packet'] = $this->getVariables('max_allowed_packet');
	}

	/**
	 * find all rows from result
	 */
	function select($fields = '*')
	{
		$sql = $this->_sql->select($fields);
		$result = $this->query($sql);
		return $result;
	}

	/**
	 * find a row from result
	 */
	function find($fields = '*')
	{
		$result = $this->limit(1)->select($fields);
		return isset($result[0]) ? $result[0] : null;
	}

	/**
	 * find the first field's value from frist row
	 */
	function scalar($field = '*')
	{
		$result = $this->find($field);
		if (is_array($result))
		{
			return array_shift($result);
		}
		return null;
	}

	/**
	 * get row num from table
	 *
	 * @param string $field        	
	 * @return number
	 */
	function count($field = '*')
	{
		return $this->scalar('count(' . $field . ')');
	}

	/**
	 * the max value of fields
	 *
	 * @param unknown $field        	
	 * @return NULL|mixed
	 */
	function max($field)
	{
		return $this->scalar('max(' . $field . ')');
	}

	/**
	 * sum the value of fields
	 *
	 * @param unknown $field        	
	 * @return NULL|mixed
	 */
	function sum($field)
	{
		return $this->scalar('sum(' . $field . ')');
	}

	/**
	 * 某字段的平均值
	 *
	 * @param unknown $field        	
	 * @return NULL|mixed
	 */
	function avg($field)
	{
		return $this->scalar('avg(' . $field . ')');
	}

	/**
	 * 更新数据表
	 *
	 * @param unknown $name        	
	 * @param string $value        	
	 * @return boolean
	 */
	function update($name, $value = '')
	{
		$sql = $this->_sql->update($name, $value);
		return $this->query($sql);
	}

	function insert($data = array())
	{
		// 字段名称检查
		$fields = array();
		foreach ($this->_desc as $index => $value)
		{
			$fields[] = $value['Field'];
		}
		
		// 是否是数字下标
		$source_keys = array_keys($data);
		$des_keys = range(0, count($data) - 1, 1);
		$diff = array_diff($source_keys, $des_keys);
		$is_num_index = empty($diff);
		
		// 补充默认值
		if (! $is_num_index)
		{
			// 去除多余的数据
			foreach ($data as $index => $value)
			{
				if (! in_array($index, $fields))
				{
					unset($data[$index]);
				}
			}
			
			// 填充默认数据
			foreach ($this->_desc as $index => $value)
			{
				if (! in_array($value['Field'], array_keys($data)))
				{
					if ($value['Default'] === null)
					{
						if ($value['Null'] == 'YES')
						{
							$data[$value['Field']] = null;
						}
						else
						{
							if ($value['Key'] == 'PRI' && $value['Extra'] == trim('AUTO_INCREMENT'))
							{
								$data[$value['Field']] = null;
							}
							else
							{
								switch ($value['Type'])
								{
									case 'datetime':
									case 'timestamp':
										$data[$value['Field']] = date('Y-m-d H:i:s');
									break;
									case 'date':
										$data[$value['Field']] = date('Y-m-d');
									break;
									case 'year(4)':
										$data[$value['Field']] = date('Y');
									break;
									case 'float':
										$data[$value['Field']] = 0;
									break;
									default:
										$zero = '$int\(\d+\)$';
										$empty_string = '$(char)?(text)?$';
										$double = '$double\(\d+,\d+\)$';
										$decimal = '$decimal\(\d+,\d+\)$';
										$bit = '$bit\(\d+\)$';
										if (preg_match($zero, $value['Type']))
										{
											$data[$value['Field']] = 0;
										}
										else if (preg_match($empty_string, $value['Type']))
										{
											$data[$value['Field']] = '';
										}
										else if (preg_match($double, $value['Type']))
										{
											$data[$value['Field']] = 0;
										}
										else if (preg_match($decimal, $value['Type']))
										{
											$data[$value['Field']] = 0;
										}
										else if (preg_match($bit, $value['Type']))
										{
											$data[$value['Field']] = 0;
										}
								}
							}
						}
					}
					else
					{
						$data[$value['Field']] = $value['Default'];
					}
				}
			}
			
			// 调整字段顺序
			$temp = array();
			foreach ($this->_desc as $value)
			{
				$temp[$value['Field']] = $data[$value['Field']];
			}
			$data = $temp;
		}
		
		if ($this->_compress)
		{
			static $__strlen = 0;
			if (! isset($this->_compress_sql['insert']))
			{
				$keys = array_keys($data);
				$this->_compress_sql['insert'] = 'INSERT INTO ' . $this->_table . ' (`' . implode('`,`', $keys) . '`) values (\'' . implode('\',\'', $data) . '\')';
				$__strlen = strlen($this->_compress_sql['insert']);
			}
			else
			{
				$sql = ',(\'' . implode('\',\'', $data) . '\')';
				$this->_compress_sql['insert_duplicate_values'] = isset($this->_compress_sql['insert_duplicate_values']) ? $this->_compress_sql['insert_duplicate_values'] : '';
				if (($__strlen + strlen($sql) + strlen($this->_compress_sql['insert_duplicate_values']) + 1) * 3 < $this->_config['max_allowed_packet'])
				{
					$this->_compress_sql['insert'] .= $sql;
					$__strlen += strlen($sql);
				}
				else
				{
					$keys = array_keys($data);
					$sql = 'INSERT INTO ' . $this->_table . ' (`' . implode('`,`', $keys) . '`) values (\'' . implode('\',\'', $data) . '\')';
					$this->_compress_sql['insert'] .= ';' . $sql;
					$__strlen = strlen($sql);
				}
			}
			return true;
		}
		$sql = $this->_sql->insert($data);
		return $this->query($sql);
	}

	/**
	 * insert into on duplicate
	 * 目前增加了在compress状态下的使用条件，对于多个insert的duplicate
	 *
	 * @param unknown $name        	
	 * @param string $value        	
	 * @return \framework\core\database\sql
	 */
	function duplicate($name, $value = '')
	{
		if ($this->_compress)
		{
			if (is_array($name))
			{
				// 是否是数字下标
				$source_keys = array_keys($name);
				$des_keys = range(0, count($name) - 1, 1);
				$diff = array_diff($source_keys, $des_keys);
				$is_num_index = empty($diff);
				if ($is_num_index)
				{
					$duplicate = '';
					foreach ($name as $n)
					{
						$duplicate .= $n . '=VALUES(' . $n . '),';
					}
					$duplicate = rtrim($duplicate, ',');
					$this->_compress_sql['insert_duplicate_values'] = ' ON DUPLICATE KEY UPDATE ' . $duplicate;
					return $this;
				}
			}
		}
		$this->_sql->duplicate($name, $value);
		return $this;
	}

	/**
	 * 删除
	 *
	 * @return boolean
	 */
	function delete()
	{
		$sql = $this->_sql->delete();
		return $this->query($sql);
	}

	/**
	 * 执行自定义sql
	 *
	 * @param unknown $sql        	
	 * @param array $array        	
	 * @return boolean
	 */
	function query($sql, $array = array())
	{
		if ($sql instanceof sql)
		{
			$complete_sql = $sql->getSql();
			self::$_history[] = $complete_sql;
			$array = $sql->getParams();
			$sql_string = $sql->__toString();
			$sql->clear();
			$sql = $sql_string;
		}
		else
		{
			$complete_sql = $this->_sql->getSql($sql, $array);
			self::$_history[] = $complete_sql;
		}
		if ($this->_compress)
		{
			$this->_compress_sql[] = $complete_sql;
			return true;
		}
		return $this->_db->query($sql, $array);
	}

	/**
	 * 事务开始
	 */
	function transaction()
	{
		return $this->_db->transaction();
	}

	/**
	 * 事务提交
	 */
	function commit()
	{
		return $this->_db->commit();
	}

	/**
	 * 事务回滚
	 */
	function rollback()
	{
		return $this->_db->rollback();
	}

	/**
	 * 上一个插入的ID
	 *
	 * @param unknown $name        	
	 */
	function lastInsertId($name = null)
	{
		return $this->_db->lastInsert($name);
	}

	/**
	 * 清空表
	 *
	 * @return boolean
	 */
	function truncate()
	{
		return $this->_db->exec('TRUNCATE `' . $this->getTable() . '`');
	}

	/**
	 * 开启sql压缩
	 * 所谓的sql压缩是指当需要一次性执行非常多的sql的时候，自动把所有的sql语句都拼接起来，当作一条sql执行
	 * 当开启sql压缩后query函数始终返回true
	 */
	function startCompress()
	{
		$this->_compress = true;
	}

	/**
	 * 提交压缩后的sql
	 */
	function commitCompress()
	{
		if ($this->_compress && ! empty($this->_compress_sql))
		{
			if (isset($this->_compress_sql['insert']) && ! empty($this->_compress_sql['insert']))
			{
				$insert_duplicate_values = isset($this->_compress_sql['insert_duplicate_values']) ? $this->_compress_sql['insert_duplicate_values'] : '';
				unset($this->_compress_sql['insert_duplicate_values']);
				$insert_sql = explode(';', $this->_compress_sql['insert']);
				
				$insert_sql = array_map(function ($sql) use ($insert_duplicate_values)
				{
					return $sql . $insert_duplicate_values;
				}, $insert_sql);
				$this->_compress_sql = array_merge($this->_compress_sql, $insert_sql);
				unset($this->_compress_sql['insert']);
			}
			else
			{
				unset($this->_compress_sql['insert_duplicate_values']);
			}
			$sql = array_shift($this->_compress_sql);
			$sql = trim($sql, ' ;'); // 去除前后空格和分号
			$sql = str_replace('  ', ' ', $sql); // 把2个空格转化为1个空格
			$result = 0;
			while (! empty($sql))
			{
				$result += $this->_db->exec($sql . ';');
				$sql = array_shift($this->_compress_sql);
			}
			$this->_compress = false;
			return $result;
		}
		return false;
	}

	/**
	 * 优化数据库
	 * @return boolean
	 */
	function optimize()
	{
		return $this->query('optimize table ' . $this->getTable());
	}
	
	/**
	 * 创建数据表
	 * @param table $table
	 * @param string $connection
	 */
	static function create(table $table,$connection = '')
	{
		if (empty($connection))
		{
			$config = self::getDefaultDbConfig();
			$connection = self::getConnection($config);
		}
		else 
		{
			$connection = self::getConnection($connection);
		}
		$sql = $table->__toSql();
		var_dump($sql);
		if($connection->exec($sql) === 0)
		{
			return self::model($table->getName());
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * 删除数据表
	 */
	public function drop()
	{
		$this->query('drop table ' . $this->getTable());
	}
}