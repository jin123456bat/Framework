<?php
namespace framework\event;

use framework\core\base;

/**
 * @author jin12
 *
 */
class event extends base
{
	
	private static $_events = array(
		
	);
	
	/**
	 * 事件添加绑定
	 * 不会覆盖原来的事件
	 * 按照绑定的顺序先后执行
	 * @param unknown $eventName
	 * @param string $callback_name callback的名称  如果存在会覆盖
	 * @param unknown $callback  假如返回false则不会执行后面的事件
	 */
	static function on($eventName,$callback_name,$callback)
	{
		self::$_events[$eventName][$callback_name] = array(
			'callback' => $callback,
		);
	}
	
	/**
	 * 事件删除
	 * 只删除绑定的一个
	 */
	static function off($eventName,$callback_name)
	{
		unset(self::$_events[$eventName][$callback_name]);
	}
	
	/**
	 * 事件绑定
	 * 和on不同这个会覆盖原来的所有的事件
	 */
	static function bind($eventName,$callback)
	{
		self::$_events[$eventName] = array(
			array(
				'callback' => $callback,
			)
		);
	}
	
	/**
	 * 事件删除
	 * 删除所有绑定的事件
	 */
	static function unbind($eventName)
	{
		self::$_events[$eventName] = array();
	}
	
	/**
	 * 触发事件
	 * @param unknown $eventName
	 */
	static function trigger($eventName)
	{
		foreach (self::$_events[$eventName] as $eventName=>$event)
		{
			if (isset($event['callback']) && is_callable($event['callback']))
			{
				$param = new param();
				$param->_type = $eventName;
				$param->_class = $event['class'];
				$param->_function = $event['function'];
				$param->_data = $event['data'];
				$result = call_user_func($event['callback'],$param);
				if ($result === false)
				{
					break;
				}
			}
		}
	}
}