<?php
namespace framework\event;
class param extends \stdClass
{
	/**
	 * 触发事件的类的完整名称
	 * @var string
	 */
	public $_class = '';
	
	/**
	 * 触发事件的类的对象
	 * @var string
	 */
	public $_object = '';
	
	/**
	 * 触发事件的函数名称
	 * @var string
	 */
	public $_function = '';
	
	/**
	 * 事件的其他参数
	 * @var array
	 */
	public $_data = array();
}