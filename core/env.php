<?php
namespace framework\core;

class env extends base
{
	public static $_php_sapi_name = 'cli';
	
	/**
	 * 代码执行方式 cli web server
	 * @return string
	 */
	static function php_sapi_name()
	{
		if (stripos(php_sapi_name(), 'cli') !== false)
		{
			return self::$_php_sapi_name;
		}
		else
		{
			return 'web';
		}
	}
	
	/**
	 * 程序在url中的目录
	 * @return string
	 */
	static function path()
	{
		$dir = $_SERVER['SCRIPT_NAME'];
		return substr($dir, 0,strripos($dir, '/'));
	}
}