<?php
namespace framework\core;

/**
 * @author jin12
 *
 */
class event extends base
{
	/**
	 * 事件添加绑定
	 * 不会覆盖原来的事件
	 * 按照绑定的顺序先后执行
	 * @param unknown $eventName
	 * @param unknown $callback  假如返回false则不会执行后面的事件
	 */
	static function on($eventName,$callback,$callback_name)
	{
		
	}
	
	/**
	 * 事件删除
	 * 只删除绑定的一个
	 */
	static function off($eventName,$callback_name)
	{
		
	}
	
	/**
	 * 事件绑定
	 * 和on不同这个会覆盖原来的所有的事件
	 */
	static function bind($eventName,$callback)
	{
		
	}
	
	/**
	 * 事件删除
	 * 删除所有绑定的事件
	 */
	static function unbind($eventName)
	{
		
	}
	
	/**
	 * 触发事件
	 * @param unknown $eventName
	 */
	static function trigger($eventName)
	{
		
	}
}