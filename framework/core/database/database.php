<?php
namespace framework\core\database;

/**
 * 数据库接口   所有实现的数据库 必须继承这个接口
 * @author fx
 */
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
}
