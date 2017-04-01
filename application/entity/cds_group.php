<?php
namespace application\entity;

use framework\lib\data;

/**
 *
 * @author fx
 *        
 */
class cds_group extends data
{

	/**
	 * 数据唯一行标识,指示哪个字段是主键
	 */
	function __primaryKey()
	{
		return 'id';
	}

	/**
	 * 数据关联的表
	 *
	 * @return string
	 */
	function __model()
	{
		return 'cds_group';
	}

	function __rules()
	{
		return array(
			array(
				'required' => 'name',
				'message' => '{field}不能为空'
			),
			array(
				'unique' => 'name',
				'message' => 'name已经存在'
			),
			array(
				'safe' => 'name',
				'on' => 'save'
			)
		);
	}

	function save()
	{
		$pk = $this->__primaryKey();
		if (empty($this->$pk))
		{
			$this->$pk = $this->model('cds_group')->add($this->name, $this->sn);
			return true;
		}
		else
		{
			return $this->model('cds_group')->save($this->$pk, $this->name, $this->sn);
		}
	}
}
