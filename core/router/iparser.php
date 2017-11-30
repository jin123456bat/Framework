<?php
namespace framework\core\router;

interface iparser
{
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