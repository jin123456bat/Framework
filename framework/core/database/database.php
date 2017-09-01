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
	 * 执行需要参数的sql
	 * 通常用于pdo的prepare
	 * 
	 * @param unknown $sql        
	 * @param array $array        
	 */
	abstract function query($sql, array $array);

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
