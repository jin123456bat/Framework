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
}
