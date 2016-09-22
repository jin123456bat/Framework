<?php
namespace application\control;
use framework\core\control;
use framework\core\request;
use framework\core\response\json;

class cds_group extends control
{
	/**
	 * 添加CDS组
	 */
	function add()
	{
		$name = request::param('name','','trim|htmlspecialchars');
		$sn = request::param('sn',array(),NULL,'a');
		
		$cds_groupData = new \application\entity\cds_group(array(
			'sn' => $sn,
			'name' => $name,
		));
		if (!$cds_groupData->validate())
		{
			return new json(json::FAILED,$cds_groupData->getError());
		}
		
		if($this->model('cds_group')->add($name,$sn))
		{
			return new json(json::OK);
		}
		return new json(json::FAILED,'添加失败');
	}
	
	/**
	 * CDS组删除
	 */
	function remove()
	{
		$id = request::param('id',0,'int|abs');
		
		if($this->model('cds_group')->remove($id))
		{
			return new json(json::OK);
		}
		return new json(json::FAILED,'CDS组不存在');
	}
}