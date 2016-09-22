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
		return true;
	}
	
	function remove($id)
	{
		$result = $this->where('id=?',array($id))->find();
		if(empty($result))
		{
			return false;
		}
		if($this->where('id=?',array($id))->delete())
		{
			return $this->model('cds_group_sn')->where('cds_group_id=?',array($id))->delete();
		}
		return false;
	}
}