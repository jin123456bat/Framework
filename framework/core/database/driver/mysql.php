<?php
namespace framework\core\database\driver;

use \PDO;
use framework\core\database\sql;
use framework\core\log;
/**
 * mysql类
 *
 * @author jcc
 *        
 */
class mysql
{

	private $config;

	private static $mysql = array();

	private $pdo;
	
	private $_transaction_level = 0;

	private function __construct($config = NULL)
	{
		$this->config = $config;
		$this->connect();
	}

	/**
	 * 获取mysql进程
	 */
	public static function getInstance($config = NULL)
	{
		$configKey = md5(json_encode($config));
		if (!isset(self::$mysql[$configKey]) || empty(self::$mysql[$configKey]))
			self::$mysql[$configKey] = new mysql($config);
		return self::$mysql[$configKey];
	}

	/**
	 * 数据库链接
	 */
	private function connect()
	{
		$charset = isset($this->config['db_charset'])?$this->config['db_charset']:'utf8';
		
		$init_command = array();
		if (isset($this->config['init_command']))
		{
			if (is_array($this->config['init_command']))
			{
				$init_command = array_merge($init_command,$this->config['init_command']);
			}
			else if (is_string($this->config['init_command']))
			{
				$init_command[] = $this->config['init_command'];
			}
		}
		
		$this->pdo = new PDO(
			$this->config['db_type'] . ':host=' . $this->config['db_server'] . ';dbname=' . $this->config['db_dbname'], 
			$this->config['db_user'], 
			$this->config['db_password'], 
			array(
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,//使用默认的索引模式
				PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false, //不使用buffer，防止数据量过大导致php内存溢出，但是这个东西貌似需要直接操作pdo效果才会体现
				PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,//抛出异常模式
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset,//设置字符集
			)
		);
		//init_command
		foreach ($init_command as $command)
		{
			$this->pdo->exec($command);
		}
	}

	/**
	 * 执行sql语句
	 * @return array|boolean 对于select语句返回结果集，对于其他语句返回影响数据的条数
	 */
	public function query($sql, array $array = array())
	{
		//$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
		$statement = $this->pdo->prepare($sql);
		if ($statement) {
			list($start_m_second,$start_second) = explode(' ', microtime());
			$result = $statement->execute($array);
			list($end_m_second,$end_second) = explode(' ', microtime());
			if (defined('DEBUG') && DEBUG)
			{
				log::mysql($sql,$end_second + $end_m_second - $start_m_second - $start_second);
			}
			if (in_array(strtolower(substr(trim($statement->queryString), 0, 6)), array('select'),true)) {
				return $statement->fetchAll(PDO::FETCH_ASSOC);
			}
			else if (in_array(strtolower(substr(trim($statement->queryString), 0, 6)), array('insert','delete','update'),true))
			{
				return $statement->rowCount();
			}
			return $statement->fetchAll(PDO::FETCH_ASSOC);
		}
		return false;
	}

	/**
	 * 执行sql语句
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
		$this->_transaction_level++;
		if ($this->_transaction_level===1)
		{
			if($this->pdo->beginTransaction())
			{
				return true;
			}
		}
		return true;
	}
	
	/**
	 * 检查是否开启了事物
	 * @return boolean
	 */
	public function inTransaction()
	{
		return $this->_transaction_level>0;
	}

	/**
	 * 执行
	 */
	public function commit()
	{
		$this->_transaction_level--;
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
		$this->_transaction_level--;
		if ($this->_transaction_level === 0)
		{
			return $this->pdo->rollBack();
		}
		return true;
	}

	/**
	 * 上一次插入的id
	 */
	public function lastInsert($name = NULL)
	{
		return $this->pdo->lastInsertId($name);
	}
}