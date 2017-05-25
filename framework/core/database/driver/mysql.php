<?php
namespace framework\core\database\driver;

use \PDO;
use framework\core\database\sql;
use framework\core\log;
use framework\core\database\database;

/**
 * mysql类
 *
 * @author jcc
 *        
 */
class mysql implements database
{

	private $_config;

	private static $mysql = array();

	private $_read_pdo;
	
	private $_write_pdo;

	private $_transaction_level = 0;

	private function __construct($config)
	{
		$this->_config = $config;
		//这里要区分一下配置  考虑读写分离
		if (isset($config['server']))
		{
			if (is_string($config['server']))
			{
				$this->_read_pdo = $this->connect($config);
				$this->_write_pdo = $this->_read_pdo;
			}
			else if (is_array($config['server']))
			{
				if (isset($config['server']['read']))
				{
					$read = $config;
					if (is_string($read['server']['read']))
					{
						$read['server'] = $read['server']['read'];
						$this->_read_pdo = $this->connect($read);
					}
					else if (is_array($read['server']['read']) && isset($read['server']['read']['server']))
					{
						if (isset($read['server']['read']['server']))
						{
							$read = array_merge($read['server']['read'],$read);
							$this->_read_pdo = $this->connect($read);
						}
						else
						{
							//多个读服务器  随机取出来一个
							$key = array_rand($read['server']['read']);
							$read = array_merge($read['server']['read'][$key],$read);
							$this->_read_pdo = $this->connect($read);
						}
					}
				}
				
				if (isset($config['server']['write']))
				{
					$write = $config;
					if (is_string($write['server']['write']))
					{
						$write['server'] = $write['server']['write'];
						$this->_write_pdo = $this->connect($read);
					}
					else if (is_array($write['server']['write']) && isset($write['server']['write']['server']))
					{
						if (isset($write['server']['write']['server']))
						{
							$write = array_merge($write['server']['write'],$write);
							$this->_write_pdo = $this->connect($write);
						}
						else
						{
							//多个写服务器  随机取出来一个
							$key = array_rand($write['server']['write']);
							$write = array_merge($write['server']['write'][$key],$write);
							$this->_write_pdo = $this->connect($write);
						}
					}
				}
			}
		}
	}

	/**
	 * 获取mysql进程
	 */
	public static function getInstance($config)
	{
		$configKey = md5(json_encode($config));
		if (! isset(self::$mysql[$configKey]) || empty(self::$mysql[$configKey]))
		{
			self::$mysql[$configKey] = new mysql($config);
		}
		return self::$mysql[$configKey];
	}

	/**
	 * 数据库链接
	 * @param $config 配置
	 * @return \PDO
	 */
	private function connect($config)
	{
		$charset = isset($config['db_charset']) ? $config['db_charset'] : 'utf8';
		
		$init_command = array();
		if (isset($config['init_command']))
		{
			if (is_array($config['init_command']))
			{
				$init_command = array_merge($init_command, $config['init_command']);
			}
			else if (is_string($config['init_command']))
			{
				$init_command[] = $config['init_command'];
			}
		}
		
		$db_port = 3306;
		if (isset($config['db_port']))
		{
			$db_port = $config['db_port'];
		}
		
		$pdo = new PDO($config['db_type'] . ':host=' . $config['db_server'] . ';port=' . $db_port . ';dbname=' . $config['db_dbname'], $config['db_user'], $config['db_password'], array(
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 使用默认的索引模式
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // 不使用buffer，防止数据量过大导致php内存溢出，但是这个东西貌似需要直接操作pdo效果才会体现
			PDO::ATTR_ERRMODE => (defined('DEBUG') && DEBUG)?PDO::ERRMODE_EXCEPTION:PDO::ERRMODE_SILENT, // 抛出异常模式
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
		)); // 设置字符集

		foreach ($init_command as $command)
		{
			$pdo->exec($command);
		}
		
		return $pdo;
	}

	public function showTables()
	{
		$array = array();
		$result = $this->query('show tables');
		foreach ($result as $r)
		{
			$array[] = current($r);
		}
		return $array;
	}

	public function showVariables($name = null)
	{
		$array = array();
		$result = $this->query('show variables like ?', array(
			'%' . $name . '%'
		));
		foreach ($result as $r)
		{
			$array[$r['Variable_name']] = $r['Value'];
		}
		return $array;
	}

	public function setVariables($name, $value)
	{
		return $this->query('set global ' . $name . '=?', array(
			$value
		));
	}

	/**
	 * 执行sql语句
	 * @param string $sql 要执行的sql
	 * @param array $array 默认array() sql中的参数
	 * @return array|boolean 对于select语句返回结果集，对于其他语句返回影响数据的条数
	 */
	public function query($sql, array $array = array())
	{
		list ($start_m_second, $start_second) = explode(' ', microtime());
		if ($this->isSelectSql($sql))
		{
			$statement = $this->_read_pdo->prepare($sql);
			if ($statement)
			{
				$statement->execute($array);
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
		}
		else
		{
			$statement = $this->_write_pdo->prepare($sql);
			if ($statement)
			{
				$statement->execute($array);
				$result = $statement->rowCount();
			}
		}
		list ($end_m_second, $end_second) = explode(' ', microtime());
		if (defined('DEBUG') && DEBUG)
		{
			log::mysql($sql, $end_second + $end_m_second - $start_m_second - $start_second);
		}
		return $result;
	}

	/**
	 * 执行sql语句
	 *
	 * @param string $sql        	
	 * @return int: 影响数据库的条数
	 */
	public function exec($sql)
	{
		return $this->pdo->exec($sql);
	}
	
	/**
	 * 判断SQL是否是查询语句
	 * @param string $sql
	 * @return bool 
	 * 返回true是查询语句，全部由read数据服务器执行
	 * false为不是查询语句，全部由write数据服务器执行
	 */
	function isSelectSql($sql)
	{
		if (in_array(strtolower(substr(trim($sql), 0, stripos(trim($sql), ' '))), array(
			'select',
			'show'
		), true))
		{
			return true;
		}
		else if(in_array(strtolower(substr(trim($sql), 0, stripos(trim($sql), ' '))), array(
			'insert',
			'delete',
			'update'
		), true))
		{
			return false;
		}
	}

	/**
	 * 开始事物
	 * @retun boolean 成功返回true 失败返回false
	 */
	public function transaction()
	{
		$this->_transaction_level ++;
		if ($this->_transaction_level === 1)
		{
			if ($this->pdo->beginTransaction())
			{
				return true;
			}
		}
		return true;
	}

	/**
	 * 检查是否开启了事物
	 *
	 * @return boolean
	 */
	public function inTransaction()
	{
		return $this->_transaction_level > 0;
	}

	/**
	 * 执行
	 */
	public function commit()
	{
		$this->_transaction_level --;
		if ($this->_transaction_level === 0)
		{
			return $this->pdo->commit();
		}
		return true;
	}

	/**
	 * 事物回滚
	 */
	public function rollback()
	{
		$this->_transaction_level --;
		if ($this->_transaction_level === 0)
		{
			return $this->pdo->rollBack();
		}
		return true;
	}

	/**
	 * 上一次插入的id
	 */
	public function lastInsert($name = null)
	{
		return $this->pdo->lastInsertId($name);
	}

	/**
	 * 错误信息
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\database\database::error()
	 */
	function error()
	{
		return $this->pdo->errorInfo();
	}

	/**
	 * 错误代码
	 *
	 * {@inheritdoc}
	 *
	 * @see \framework\core\database\database::errno()
	 */
	function errno()
	{
		return $this->pdo->errorCode();
	}
}
