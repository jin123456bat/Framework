<?php
namespace framework\core\router;

interface iparser
{
	/**
	 * 构造函数
	 * @param unknown $queryString
	 */
	function __construct($queryString);
	
	/**
	 * 获取控制器名
	 */
	function getControllName();
	
	/**
	 * 获取方法名
	 */
	function getActionName();
}