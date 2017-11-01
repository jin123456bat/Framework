<?php
namespace framework\core;

/**
 * 控制器基类
 * 
 * @author fx
 */
class control extends component
{

	function initlize()
	{
		return parent::initlize();
	}

	/**
	 * action的访问控制
	 */
	function __access()
	{
		return array();
	}

	/**
	 * 如何将要输出的内容发送到输出设备
	 */
	function __output($msg)
	{
		echo $msg;
	}
	
	/**
	 * 这个函数应该返回一个数组或者字符串
	 * 假如调用的action在返回的数组中
	 * 就必须要通过csrf验证才允许调用
	 * 支持*通配符
	 * @example
	 * return array(
			'action' => '*',
			'message' => '请刷新重试',
		);
	 */
	function __csrf()
	{
		return array();
	}
	
	/**
	 * cli模式下只允许一个实例
	 */
	function __single()
	{
		return array();
	}
}
