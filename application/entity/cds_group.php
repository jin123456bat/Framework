<?php
namespace application\entity;
use framework\lib\data;

class cds_group extends data
{
	function __model()
	{
		return 'cds_group';
	}
	
	function __rules()
	{
		return array(
			array('required' => 'sn,name','message'=>'{field}不能为空'),
			array('unique' => 'name','message'=>'name已经存在'),
		);
	}
}