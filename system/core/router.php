<?php
namespace framework\core;

class router extends base
{
	/**
	 * 获取控制器名称
	 * @return string 
	 */
	static function getControlName()
	{
		return 'control\index';
	}
	
	static function getActionName()
	{
		return 'index';
	}
	
	static function listen($url,$callback)
	{
		
	}
	
	/**
	 * 将回调函数加入到get请求的队列中
	 * @param unknown $url
	 * @param unknown $callback
	 */
	static function get($url,$callback)
	{
		
	}
	
	/**
	 * 将回调函数加入到post请求的队列中
	 * @param unknown $url
	 * @param unknown $callback
	 */
	static function post($url,$callback)
	{
		
	}
}