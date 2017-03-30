<?php
namespace framework\core\database;

interface database
{

	/**
	 * 执行需要参数的sql
	 * 通常用于pdo的prepare
	 * 
	 * @param unknown $sql        	
	 * @param array $array        	
	 */
	function query($sql, array $array);

	/**
	 * 执行sql语句
	 * 
	 * @param string $sql        	
	 * @param array $array        	
	 */
	function exec($sql);

	/**
	 * 获取所有表名
	 * 
	 * @return array()
	 */
	function showTables();

	/**
	 * 获取数据库配置变量
	 */
	function showVariables($name = null);

	/**
	 * 设置数据库配置变量
	 * 
	 * @param unknown $name        	
	 * @param unknown $value        	
	 */
	function setVariables($name, $value);

	/**
	 * 获取mysql进程
	 * 
	 * @param array $config        	
	 */
	static function getInstance($config);

	/**
	 * 获取错误信息
	 */
	function error();

	/**
	 * 获取错误代码
	 */
	function errno();
}
