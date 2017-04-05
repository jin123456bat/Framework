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
		parent::initlize();
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
}
