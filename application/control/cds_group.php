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
		
		if($cds_groupData->save())
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
	
	/**
	 * 重新保存CDS组
	 */
	function save()
	{
		$id = request::param('id',0,'int|abs');
		$name = request::param('name','','trim|htmlspecialchars');
		$sn = request::param('sn',array(),NULL,'a');
		
		$cds_groupData = new \application\entity\cds_group(array(
			'id' => $id,
			'sn' => $sn,
			'name' => $name,
		),'save');
		if (!$cds_groupData->validate())
		{
			return new json(json::FAILED,$cds_groupData->getError());
		}
		$cds_groupData->save();
	}
	
	/**
	 * CDS组列表
	 */
	function lists()
	{
		$cds_group = $this->model('cds_group')->select();
		foreach ($cds_group as &$group)
		{
			$result = $this->model('cds_group_sn')->where('cds_group_id=?',array($group['id']))->select('sn');
			$sn = array();
			foreach ($result as $r)
			{
				$sn[] = $r['sn'];
			}
			$group['sn'] = $sn;
		}
		return new json(json::OK,'ok',$cds_group);
	}
}