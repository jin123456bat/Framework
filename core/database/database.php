<?php
namespace framework\core\database;

use framework\core\database\mysql\table;

/**
 * 数据库接口 所有实现的数据库 必须继承这个接口
 * 
 * @author fx
 */
abstract class database
{
	/**
	 * 所有的sql或者命令
	 * @var array
	 */
	protected static $_history = array();
	
	/**
	 * 执行sql
	 * 主要是增删改查
	 * 多服务器下用于读写分离的区别
	 * @param string $sql        
	 * @param array $array        
	 */
	abstract function query($sql, array $array = array());
	
	/**
	 * 执行sql
	 * 主要是一些表修改的sql
	 * 多服务器情况下对所有服务器都执行
	 * @param string $sql
	 * @param array $array
	 */
	abstract function execute($sql, array $array = array());
	
	/**
	 * 执行sql
	 * 主要用于读取数据，然后对数据用于$callback回调函数
	 * @param string $sql
	 * @param array $array
	 * @param callback $callback
	 */
	abstract function fetch($sql,array $array = array(),$callback = NULL);

	/**
	 * 设置配置
	 * 
	 * @param string $name        
	 * @param string $value        
	 */
	abstract function setConfig($name, $value);

	/**
	 * 获取配置
	 * 
	 * @param unknown $name
	 *        配置名称 假如为空获取所有配置
	 */
	abstract function getConfig($name = NULL);

	/**
	 * 错误信息
	 */
	abstract function error();

	/**
	 * 错误代码
	 */
	abstract function errno();

	/**
	 * 获取所有表名
	 */
	abstract function showTables();
	
	/**
	 * 开启事物
	 */
	abstract function transaction();
	
	/**
	 * 提交事物
	 */
	abstract function commit();
	
	/**
	 * 回滚事物
	 */
	abstract function rollback();
	
	/**
	 * 是否在事物中
	 */
	abstract function inTransaction();

/**
 * 判断表是否存在
 * 
 * @param unknown $tableName        
 */
	// abstract function isExist($tableName);

/**
 * 创建表
 * 
 * @param table $table        
 */
	// abstract function create(table $table);
}
