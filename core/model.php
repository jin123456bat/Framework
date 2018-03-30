<?php
namespace framework\core;

use framework\core\database\mysql\table;
use framework;
use framework\core\database\sql;

/**
 * 表数据	
 * @author jin	
 * @method model where($sql,array $array = array(),$combine = 'and') 添加条件语句	
 * @method model in($field,array $data,$combine = 'and') in语句	
 * @method model forUpdate() select for update
 * @method model forceIndex($index) 强制索引
 * @method model replace($key, $value = null)  replace into
 * @method model join($table, $on, $combine = 'AND')
 * @method model leftJoin($table, $on, $combine = 'AND')
 * @method model rightJoin($table, $on, $combine = 'AND')
 * @method model innerJoin($table, $on, $combine = 'AND')
 * @method model fullJoin($table, $on, $combine = 'AND')
 * @method model union($all = false, $sql_)
 * @method model order($field, $order = 'ASC')
 * @method model group($fields)
 * @method model limit($start, $length = null)
 * @method model between($field, $a, $b, $combine = 'and')
 * @method model notbetween($field, $a, $b, $combine = 'and')
 * @method model notIn($field, array $data = array(), $combine = 'and')
 * @method model isNULL($fields, $combine = 'and')
 * @method model having($sql, array $data = array(), $combine = 'and')
 * @method model distinct()
 */
class model extends component
{

	/**
	 * 表名
	 * 
	 * @var unknown
	 */
	private $_table;

	/**
	 *
	 * @var sql
	 */
	private $_sql;

	/**
	 *
	 * @var \framework\core\database\database
	 */
	private $_db;

	/**
	 * 当前表执行的sql记录
	 * 
	 * @var array
	 */
	private $_history = array();

	/**
	 * 表结构 通过desc tableName获取
	 * 
	 * @var unknown
	 */
	private $_desc;

	/**
	 * 是否开启批量提交
	 * 批量insert的时候特别好用
	 * 
	 * @var string
	 */
	private $_compress = false;
	
	/**
	 * 数组下标字段
	 * @var unknown
	 */
	private $_index;

	/**
	 * 存储批量提交的sql
	 * 
	 * @var array
	 */
	private $_compress_sql = array();
	
	
	/**
	 * @var string
	 */
	private $_debug = false;
	
	/**
	 * @var string
	 */
	private $_keep = false;

	function __construct($table = null)
	{
		$this->_table = $table;
	}

	/**
	 * 获取当前表执行的sql记录
	 * @return array
	 */
	public function history()
	{
		return $this->_history;
	}
	
	/**
	 * 当使用select方法获取二维数组的时候设置某个字段为下标
	 * @param string $name
	 */
	public function index($name)
	{
		$this->_index = $name;
		return $this;
	}
	
	/**
	 * 获取数据库的配置
	 * @param string $table_name 表名
	 * @param string $name 配置名称
	 */
	public static function getDefaultConfig($table_name,$name = NULL)
	{
		$config = parent::getConfig('db');
		if (is_array($config))
		{
			if (empty(array_diff(array(
				'type',
				'server',
			), array_keys($config))))
			{
				return $config;
			}
			else
			{
				if ($name === NULL)
				{
					//查找是否有指定model的 配置
					foreach ($config as $c)
					{
						if (isset($c['model']) && !empty($c['model']))
						{
							if (is_array($c['model']) && in_array($table_name, $c['model']))
							{
								return $c;
							}
							else if (is_string($c['model']) && in_array($table_name, explode(',', $c['model'])))
							{
								return $c;
							}
						}
					}
					
					//查找是否有默认配置的配置
					foreach ($config as $c)
					{
						if (isset($c['default']) && $c['default'] === true)
						{
							return $c;
						}
					}
				}
				else
				{
					return $config[$name];
				}
			}
		}
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
		if (method_exists($this, '__config'))
		{
			$this->_config = $this->__config();
			if (!empty($this->_config) && is_scalar($this->_config))
			{
				$this->_config= self::getDefaultConfig($this->getTable(),$this->_config);
			}
		}
		else
		{
			$this->_config= self::getDefaultConfig($this->getTable());
		}
		
		// 实例化mysql的类
		$type = $this->_config['type'];
		$db = '\\framework\\core\\database\\driver\\' . $type;
		
		$this->_db = $db::getInstance($this->_config);
		
		if (method_exists($this, '__tableName'))
		{
			$this->_table = $this->__tableName();
		}
		
		$sql = '\\framework\\core\\database\\'.$type.'\\sql';
		$this->_sql = application::load($sql);
		
		$this->setTable($this->getTable());
		parent::initlize();
	}

	/**
	 * 获取当前model的连接标识符
	 * @return \framework\core\database\database
	 */
	public function getConnection()
	{
		return $this->_db;
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
		$this->_sql->from($table);
		$this->parse();
	}

	/**
	 * get this database table's name
	 * 
	 * @return unknown|string
	 */
	public function getTable()
	{
		return $this->_table;
	}

	/**
	 * process something about this tables;
	 */
	private function parse()
	{
		$this->_desc = $this->query('DESC `' . $this->getTable() . '`');
		if (strtolower($this->_config['type']) == 'mysql')
		{
			$this->_config['max_allowed_packet'] = $this->_db->getConfig('max_allowed_packet');
		}
	}
	
	/**
	 * 设置为debug模式
	 */
	function debug($debug = true)
	{
		$this->_debug = $debug;
		return $this;
	}
	
	/**
	 * 当执行完sql后不删除条件
	 * @param string $keep
	 */
	function keepCondition($keep = true)
	{
		$this->_keep = $keep;
		return $this;
	}

	/**
	 * find all rows from result
	 */
	function select($fields = '*')
	{
		$sql = $this->_sql->select($fields);
		$result = $this->query($sql);
		if (!empty($this->_index) && isset($result[0][$this->_index]))
		{
			$temp = array();
			foreach ($result as $r)
			{
				$temp[$r[$this->_index]] = $r;
			}
			unset($this->_index);
			return $temp;
		}
		return $result;
	}
	
	/**
	 * find the first column as a array
	 */
	function column($fields = '*')
	{
		$result = $this->select($fields);
		if ($this->_debug)
		{
			return $result;
		}
		$temp = array();
		foreach ($result as $r)
		{
			$temp[] = current($r);
		}
		return $temp;
	}
	
	/**
	 * find a row from result
	 */
	function find($fields = '*')
	{
		$result = $this->limit(1)->select($fields);
		if ($this->_debug)
		{
			return $result;
		}
		return isset($result[0]) ? $result[0] : null;
	}

	/**
	 * find the first field's value from frist row
	 */
	function scalar($field = '*')
	{
		$result = $this->find($field);
		if ($this->_debug)
		{
			return $result;
		}
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
		$result = $this->scalar('count(' . $field . ')');
		if ($this->_debug)
		{
			return $result;
		}
		return $result;
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
		$fields = array();
		foreach ($this->_desc as $x => $y)
		{
			$fields[] = $y['Field'];
		}
		$data = array();
		if (is_array($name))
		{
			foreach ($name as $k => $v)
			{
				if (in_array($k, $fields))
				{
					$data[$k] = $v;
				}
			}
			$sql = $this->_sql->update($data);
			return $this->query($sql);
		}
		else if (is_string($name))
		{
			if (in_array($name, $fields))
			{
				$sql = $this->_sql->update($name,$value);
				return $this->query($sql);
			}
		}
		return false;
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
				//只对数据库中有的字段做判断  没有设置默认值并且字段不可以为null  排除掉主键字段
				if (! in_array($value['Field'], array_keys($data)) && (($value['Default'] === null && $value['Null'] == 'NO') && !(strtolower($value['Key']) == 'pri' && strtolower($value['Extra']) == 'auto_increment') ))
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
						case 'year':
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
							$enum = '/enum\((\'(?<value>[^\']+)\',?)+\)/';
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
							else if (preg_match($enum, $value['Type'],$match))
							{
								$data[$value['Field']] = $match['value'];
							}
					}
				}
			}
			// 调整字段顺序
			$temp = array();
			foreach ($this->_desc as $value)
			{
				if (isset($data[$value['Field']]))
				{
					$temp[$value['Field']] = $data[$value['Field']];
				}
			}
			$data = $temp;
		}
		if ($this->_compress)
		{
			static $__strlen = 0;
			static $__key = 0;
			if (! isset($this->_compress_sql['insert']))
			{
				$keys = array_keys($data);
				$sql = 'INSERT INTO ' . $this->getTable() . ' (`' . implode('`,`', $keys) . '`) values (\'' . implode('\',\'', $data) . '\')';
				$this->_compress_sql['insert'][$__key] = $sql;
				$__strlen = strlen($sql);
			}
			else
			{
				$sql = ',(\'' . implode('\',\'', $data) . '\')';
				$this->_compress_sql['insert_duplicate_values'] = isset($this->_compress_sql['insert_duplicate_values']) ? $this->_compress_sql['insert_duplicate_values'] : '';
				if (($__strlen + strlen($sql) + strlen($this->_compress_sql['insert_duplicate_values']) + 1) * 3 < $this->_config['max_allowed_packet'])
				{
					$this->_compress_sql['insert'][$__key] .= $sql;
					$__strlen += strlen($sql);
				}
				else
				{
					$__key++;
					$keys = array_keys($data);
					$sql = 'INSERT INTO ' . $this->getTable() . ' (`' . implode('`,`', $keys) . '`) values (\'' . implode('\',\'', $data) . '\')';
					$this->_compress_sql['insert'][$__key] = $sql;
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
	 * @return $this
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
			else if (!empty($value))
			{
				$this->_compress_sql['insert_duplicate_values'] = ' ON DUPLICATE KEY UPDATE '.$name.'="'.addslashes($value).'"';
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
			$this->_history[] = $complete_sql;
			$array = $sql->getParams();
			$sql_string = $sql->__toString();
			if ($this->_keep)
			{
				$sql->clearWithoutCondition();
			}
			else
			{
				$sql->clear()->from($this->getTable());
			}
			$sql = $sql_string;
		}
		else
		{
			$complete_sql = $this->_sql->getSql($sql, $array);
			$this->_history[] = $complete_sql;
		}
		if ($this->_compress)
		{
			$this->_compress_sql[] = $complete_sql;
			return true;
		}
		var_dump($sql);
		
		if ($this->_debug)
		{
			return $complete_sql;
		}
		return $this->_db->query($sql, $array);
	}

	/**
	 * 事务开始
	 * @param string|null 
	 * 	READ_UNCOMMITTED | READ_COMMITTED | REPEATABLE_READ | SERIALIZABLE
	 * @retun boolean 成功返回true 失败返回false
	 */
	function transaction($level = NULL)
	{
		return $this->_db->transaction($level);
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
		return $this->_db->query('TRUNCATE `' . $this->getTable() . '`');
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
	 * like
	 * @param unknown $field
	 * @param unknown $value
	 */
	function like($field,$value)
	{
		$this->_sql->where($field.' like ?',array('%'.$value.'%'));
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
				$insert_sql = $this->_compress_sql['insert'];
				
				$insert_sql = array_map(function ($sql) use ($insert_duplicate_values) {
					return $sql . $insert_duplicate_values;
				}, $insert_sql);
				//把其他的比如update 等sql都放到一起
				$this->_compress_sql = array_merge($this->_compress_sql, $insert_sql);
				unset($this->_compress_sql['insert']);
			}
			else
			{
				unset($this->_compress_sql['insert_duplicate_values']);
			}
			//启用事务插入的方式执行
			$this->_db->transaction();
			try{
				$sql = array_shift($this->_compress_sql);
				$sql = trim($sql, ' ;'); // 去除前后空格和分号
				$sql = str_replace('  ', ' ', $sql); // 把2个空格转化为1个空格
				$result = 0;
				while (! empty($sql))
				{
					$result += $this->_db->query($sql . ';');
					$sql = array_shift($this->_compress_sql);
				}
				$this->_compress = false;//关闭compress状态
			}
			catch (\Exception $e)
			{
				$this->_db->rollback();
				return false;
			}
			$this->_db->commit();
			return $result;
		}
		return false;
	}	
}