<?php
namespace framework\core\database\driver;

use \PDO;
use framework\core\database\mysql\sql;
use framework\core\log;
use framework\core\database\database;

/**
 * mysql类
 * 
 * @author jcc
 */
class mysql extends database
{

	/**
	 * 链接配置
	 * 
	 * @var unknown
	 */
	private $_config;

	/**
	 * 所有的数据库链接
	 * 
	 * @var array
	 */
	private static $_mysql = array();

	/**
	 *
	 * @var \PDO
	 */
	private $_pdo;

	/**
	 * 事务等级
	 * 
	 * @var integer
	 */
	private $_transaction_level = 0;

	private function __construct($config)
	{
		$this->_config = $config;
		$this->_pdo = $this->connect($config);
	}

	/**
	 * 获取mysql进程
	 * @param array $config 配置数组
	 * @return mysql
	 */
	public static function getInstance($config)
	{
		$configKey = md5(json_encode($config));
		if (! isset(self::$_mysql[$configKey]) || empty(self::$_mysql[$configKey]))
		{
			self::$_mysql[$configKey] = new mysql($config);
		}
		return self::$_mysql[$configKey];
	}

	/**
	 * 数据库链接
	 * 
	 * @param $config 配置        
	 * @return \PDO
	 */
	private function connect($config)
	{
		$charset = isset($config['charset']) ? $config['charset'] : 'utf8';
		
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
		if (isset($config['port']))
		{
			$db_port = $config['port'];
		}
		
		try{
			$pdo = new PDO($config['type'] . ':host=' . $config['server'] . ';port=' . $db_port . ';dbname=' . $config['dbname'], $config['user'], $config['password'], array(
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // 使用默认的索引模式
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, // 不使用buffer，防止数据量过大导致php内存溢出，但是这个东西貌似需要直接操作pdo效果才会体现
				PDO::ATTR_ERRMODE => (defined('DEBUG') && DEBUG) ? PDO::ERRMODE_EXCEPTION : PDO::ERRMODE_SILENT, // 抛出异常模式
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $charset
			)); // 设置字符集
		}
		catch (\Exception $e)
		{
			//连接数据库的时候必须trycatch，因为假如连接失败会导致页面提示错误信息，而这个错误信息中会包含数据库的相关信息，引发泄露
			//或者set_exception_handler
			exit('连接到数据库失败');
		}
		foreach ($init_command as $command)
		{
			if (!empty($command))
			{
				$pdo->exec($command);
			}
		}
		
		return $pdo;
	}

	/**
	 * 获取所有表名
	 * 
	 * @return mixed[]
	 */
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

	/**
	 * 获取链接配置
	 * 
	 * @param unknown $name        
	 */
	public function getConfig($name = null)
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

	/**
	 * 设置链接配置
	 * 
	 * @param unknown $name        
	 * @param unknown $value        
	 * @return boolean
	 */
	public function setConfig($name, $value)
	{
		return $this->query('set global ' . $name . '=?', array(
			$value
		));
	}

	/**
	 * 执行sql语句
	 * 
	 * @param string $sql
	 *        要执行的sql
	 * @param array $array
	 *        默认array() sql中的参数
	 * @return array|boolean 对于select语句返回结果集，对于其他语句返回影响数据的条数
	 */
	public function query($sql, array $array = array())
	{
		$sqlCom = new sql();
		self::$_history[] = $sqlCom->getSql($sql,$array);
		list ($start_m_second, $start_second) = explode(' ', microtime());
		$isSelect = $this->isSelectSql($sql);
		if ($isSelect == 1)
		{
			$statement = $this->_pdo->prepare($sql);
			if ($statement)
			{
				$statement->execute($array);
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			}
		}
		else if ($isSelect == - 1)
		{
			$statement = $this->_pdo->prepare($sql);
			if ($statement)
			{
				$statement->execute($array);
				$result = $statement->rowCount();
			}
		}
		else
		{
			$statement = $this->_pdo->prepare($sql);
			if ($statement)
			{
				$statement->execute($array);
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);
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
	 * 执行一些内部语句，主要是考虑读写分离的问题
	 * 
	 * @param string $sql        
	 * @param array $array        
	 */
	function execute($sql, array $array = array())
	{
		$sqlCom = new sql();
		self::$_history[] = $sqlCom->getSql($sql,$array);
		list ($start_m_second, $start_second) = explode(' ', microtime());
		$statement = $this->_pdo->prepare($sql);
		if ($statement)
		{
			$statement->execute($array);
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		list ($end_m_second, $end_second) = explode(' ', microtime());
		if (defined('DEBUG') && DEBUG)
		{
			log::mysql($sql, $end_second + $end_m_second - $start_m_second - $start_second);
		}
		return $result;
	}

	/**
	 * 通过逐行遍历的形式来遍历一个sql的结果集
	 * 返回结果是一个数组，这个数组包含了以sql结果的json串为key以回调函数的值为值的数组
	 * 
	 * @param string $sql        
	 * @param callback $callback        
	 * @return array
	 */
	function fetch($sql, array $array = array(), $callback = NULL)
	{
		$sqlCom = new sql();
		self::$_history[] = $sqlCom->getSql($sql,$array);
		list ($start_m_second, $start_second) = explode(' ', microtime());
		$statement = $this->_pdo->prepare($sql, array(
			PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL
		));
		$result = array();
		if ($statement)
		{
			$statement->execute($array);
			if (is_callable($callback))
			{
				$sql_result = $statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
				while (!empty($sql_result))
				{
					$result[json_encode($sql_result)] = call_user_func($callback, $sql_result);
					$sql_result = $statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_NEXT);
				}
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
	 * 判断SQL是否是查询语句
	 * 
	 * @param string $sql        
	 * @return -1|1|0 返回1是select或者show语句，-1是update或者insert或者delete语句 返回0是其他语句
	 */
	private function isSelectSql($sql)
	{
		if (in_array(strtolower(substr(trim($sql), 0, stripos(trim($sql), ' '))), array(
			'select',
			'show'
		), true))
		{
			return 1;
		}
		else if (in_array(strtolower(substr(trim($sql), 0, stripos(trim($sql), ' '))), array(
			'insert',
			'delete', 
			'update',
			'truncate',
		), true))
		{
			return - 1;
		}
		return 0;
	}

	/**
	 * 开始事物
	 * @param string|null 
	 * 	READ_UNCOMMITTED | READ_COMMITTED | REPEATABLE_READ | SERIALIZABLE
	 * @retun boolean 成功返回true 失败返回false
	 */
	public function transaction($level = NULL)
	{
		$this->_transaction_level ++;
		if ($this->_transaction_level === 1)
		{
			if ($level!==NULL)
			{
				$level = str_replace('_', ' ', $level);
				$this->execute("SET SESSION TRANSACTION ISOLATION LEVEL $level");
			}
			if ($this->_pdo->beginTransaction())
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
			return $this->_pdo->commit();
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
			return $this->_pdo->rollBack();
		}
		return true;
	}

	/**
	 * 上一次插入的id
	 */
	public function lastInsert($name = null)
	{
		return $this->_pdo->lastInsertId($name);
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
		return $this->_pdo->errorInfo();
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
		return $this->_pdo->errorCode();
	}
	
	/**
	 * 获取mysql执行的sql记录
	 * @return array
	 */
	public static function history()
	{
		return self::$_history;
	}
	
	/**
	 * 关闭连接
	 */
	public function __destruct()
	{
		$this->_pdo = NULL;
	}
}
