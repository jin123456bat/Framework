<?php
namespace framework\event;

use framework\core\base;

/**
 * @author jin12
 *
 */
class event extends base
{
	/**
	 * 触发事件中所有绑定的函数
	 * @param string $eventName 事件名称中间通过.来分割
	 */
	static function trigger($eventName,$data)
	{
		$backtrace = debug_backtrace();
		
		$param = new param();
		$param->_class = isset($backtrace[1]['class'])?$backtrace[1]['class']:NULL;
		$param->_function = isset($backtrace[1]['function'])?$backtrace[1]['function']:NULL;
		$param->_object = isset($backtrace[1]['object'])?$backtrace[1]['object']:NULL;
		$param->_data = $data;
		
		list($class,$function) = explode('.', $eventName,2);
		
		$namespace = APP_NAME.'\\event\\'.$class;
		if (class_exists($namespace))
		{
			$class = new $namespace();
			if (method_exists($class, $function))
			{
				call_user_func(array($class,$function),$param);
			}
		}
	}
}