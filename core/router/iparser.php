<?php
namespace framework\core\router;

interface iparser
{
	/**
	
	 * @param unknown $queryString
	 * 对于web模式，参数是queryString
	 * 对于cli模式，参数是index.php后面的内容
	 * 对于server模式，参数是socket发送的所有内容
	 * @param string $options
	 */
	function setQueryString($queryString);
	
	/**
	 * 获取控制器名
	 */
	function getControlName();
	
	/**
	 * 获取方法名
	 */
	function getActionName();
	
	/**
	 * 获取其他get请求的参数
	 */
	function getData();
}