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
	 * 当其它的模式调用了这个控制器中的方法的时候，调用这个函数来提供一个友好的输出提示
	 * 
	 * @param unknown $mode        
	 */
	public function __runningMode($mode)
	{
		return 'can\'t running in ' . $mode . ' mode';
	}
}
