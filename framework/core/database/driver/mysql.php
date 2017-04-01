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

	private $config;

	private static $mysql = array();

	private $pdo;

	private $_transaction_level = 0;

	private function __construct($config)
	{
		$this->config = $config;
		$this->connect();
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
	 */
	private function connect()
	{
		$charset = isset($this->config['db_charset']) ? $this->config['db_charset'] : 'utf8';
		
		$init_command = array();
		if (isset($this->config['init_command']))
		{
			if (is_array($this->config['init_command']))
			{
				$init_command = array_merge($init_command, $this->config['init_command']);
			}
			else if (is_string($this->config['init_command']))
			{
				$init_command[] = $this->config['init_command'];
			}
		}
		
		$db_port = 3306;
		if (isset($this->config['db_port']))
		{
			$db_port = $this->config['db_port'];
		}
		
		$this->pdo = new PDO($this->config['db_type'] . ':host=' . $this->config['db_server'] . ';port=' . $db_port . ';dbname=' . $this->config['db_dbname'], $this->config['db_user'], $this->config['db_password'], array(
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 使用默认的索引模式
			PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // 不使用buffer，防止数据量过大导致php内存溢出，但是这个东西貌似需要直接操作pdo效果才会体现
			PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT, // 抛出异常模式
			PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
		)); // 设置字符集

		foreach ($init_command as $command)
		{
			$this->pdo->exec($command);
		}
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
	 *
	 * @return array|boolean 对于select语句返回结果集，对于其他语句返回影响数据的条数
	 */
	public function query($sql, array $array = array())
	{
		// $this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$statement = $this->pdo->prepare($sql);
		if ($statement)
		{
			list ($start_m_second, $start_second) = explode(' ', microtime());
			$result = $statement->execute($array);
			list ($end_m_second, $end_second) = explode(' ', microtime());
			if (defined('DEBUG') && DEBUG)
			{
				log::mysql($sql, $end_second + $end_m_second - $start_m_second - $start_second);
			}
			if (in_array(strtolower(substr(trim($statement->queryString), 0, stripos(trim($statement->queryString), ' '))), array(
				'select',
				'show'
			), true))
			{
				return $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			else if (in_array(strtolower(substr(trim($statement->queryString), 0, stripos(trim($statement->queryString), ' '))), array(
				'insert',
				'delete',
				'update'
			), true))
			{
				return $statement->rowCount();
			}
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		return false;
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
