<?php
namespace framework\core\database\driver;

use \PDO;
use framework\core\database\sql;
/**
 * mysql类
 *
 * @author jcc
 *        
 */
class mysql
{

	private $config;

	private static $mysql;

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
		if (empty(self::$mysql))
			self::$mysql = new mysql($config);
		return self::$mysql;
	}

	/**
	 * 数据库链接
	 */
	private function connect()
	{
		$this->pdo = new PDO(
			$this->config['db_type'] . ':host=' . $this->config['db_server'] . ';dbname=' . $this->config['db_dbname'], 
			$this->config['db_user'], 
			$this->config['db_password'], 
			array(
				PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,//抛出异常模式
				PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$this->config['db_charset'],//设置字符集
			)
		);
	}

	/**
	 * 执行sql语句
	 * @return array|boolean 对于select语句返回结果集，对于其他语句返回影响数据的条数
	 */
	public function query($sql, array $array = array())
	{
		$statement = $this->pdo->prepare($sql);
		if ($statement) {
			$result = $statement->execute($array);
			if ($result) {
				if (in_array(strtolower(substr($statement->queryString, 0, 6)), array('select'))) {
					return $statement->fetchAll(PDO::FETCH_ASSOC);
				}
				else if (in_array(strtolower(substr($statement->queryString, 0, 6)), array('insert','delete','update')))
				{
					return $statement->rowCount();
				}
				return $statement->fetchAll(PDO::FETCH_ASSOC);
			}
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