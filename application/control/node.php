<?php
namespace application\control;

use application\extend\BaseControl;
use framework\core\response\json;
use framework\core\request;

/**
 * 节点管理相关接口
 * @author fx
 *
 */
class node extends BaseControl
{
	/**
	 * CDS接口
	 */
	function cds()
	{
		$start = request::get('start',0,'intval');
		$length = request::get('length',10,'intval');
		
		
		
		$data = array(
			'group'
		);
		
		return new json(json::OK,'ok',$data);
	}
	
	function icache()
	{
		
	}
	
	function vpe()
	{
		
	}
	
	/**
	 * 子节点详情
	 */
	function detail()
	{
		
	}
}