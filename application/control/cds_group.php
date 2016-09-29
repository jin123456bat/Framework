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
			$pk = $cds_groupData->__primaryKey();
			$this->model('log')->add(\application\entity\user::getLoginUserId(),"创建了CDS分组:".$name);
			return new json(json::OK,NULL,$cds_groupData->$pk);
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
			$this->model('log')->add(\application\entity\user::getLoginUserId(),"删除了CDS分组:".$id);
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
		if($cds_groupData->save())
		{
			$this->model('log')->add(\application\entity\user::getLoginUserId(),"修改了CDS分组:".$id);
			return new json(json::OK);
		}
		return new json(json::FAILED,'保存失败');
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
	
	function __access()
	{
		return array(
			array(
				'deny',
				'actions' => '*',
				'express' => \application\entity\user::getLoginUserId()===NULL,
				'message' => new json(array('code'=>2,'result'=>'尚未登陆')),
			)
		);
	}
}