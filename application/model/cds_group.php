<?php
namespace application\model;
use framework\core\model;

class cds_group extends model
{
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cloud_web_v2'];
	}
	
	
	
	function add($name,$sns)
	{
		$this->transaction();
		if(!$this->insert(array(
			'name'=>$name,
			'sort'=>1,
		)))
		{
			$this->rollback();
			return false;
		}
		$id = $this->lastInsertId();
		foreach ($sns as $sn)
		{
			if(!$this->model('cds_group_sn')->insert(array($id,$sn)))
			{
				$this->rollback();
				return false;
			}
		}
		$this->commit();
		return $id;
	}
	
	function remove($id)
	{
		$result = $this->where('id=?',array($id))->find();
		if(empty($result))
		{
			return false;
		}
		$result = $this->where('cds_group.id=?',array($id))->delete();
		if($result)
		{
			$this->model('cds_group_sn')->where('cds_group_id=?',array($id))->delete();
			return true;
		}
		return false;
	}
	
	function save($id,$name,$sns)
	{
		$this->transaction();
		$this->where('id=?',array($id))->update('name',$name);
		$this->model('cds_group_sn')->where('cds_group_id=?',array($id))->delete();
		foreach ($sns as $sn)
		{
			$this->model('cds_group_sn')->insert(array($id,$sn));
		}
		$this->commit();
		return true;
	}
}