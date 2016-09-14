<?php
namespace application\control;
use framework\core\control;
use framework\core\response\json;
use framework\core\request;

class main extends control
{
	private $_startTime;
	
	private $_endTime;
	
	function overview()
	{
		$this->_startTime = request::param('starttime');
		$this->_endTime = request::param('endtime');
		
		
	}
	
	/**
	 * 配置访问权限
	 */
	function __access()
	{
		return array(
			array(
				'allow',//deny  允许访问
				'control' => array('main'),
				'express' => false,//改规则是否有效
			),
			array(
				'deny',
				'control' => '*',
				'express' => true,
				'message' => new json(array('code'=>0,'result'=>'没有权限')),
			)
			
		);
	}
}