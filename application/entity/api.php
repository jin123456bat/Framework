<?php
namespace application\entity;

use framework\lib\data;

class api extends data
{

	function __rules()
	{
		return array(
			array(
				'required' => 'starttime,endtime',
				'message' => '{field}不能为空'
			),
			array(
				'lt' => array(
					'starttime',
					'endtime'
				),
				'render' => 'strtotime',
				'message' => '开始时间必须小于于结束时间'
			),
			array(
				'required' => 'sn',
				'message' => '{field}不能为空',
				'on' => 'sn,sn_duration'
			),
			array(
				'validate' => array(
					$this,
					'isSn'
				),
				'fileds' => 'sn',
				'message' => '{field}不合法',
				'on' => 'sn,sn_duration'
			),
			array(
				'required' => 'duration',
				'message' => 'duration间隔最少5分钟',
				'on' => 'duration,sn_duration'
			),
			array(
				'ge' => array(
					'duration',
					5 * 60
				),
				'message' => 'duration间隔最少5分钟',
				'on' => 'duration,sn_duration'
			)
		);
	}

	/**
	 * 判断是否是sn号
	 *
	 * @param unknown $sn        	
	 */
	public function isSn($sn)
	{
		$pattern = '$C[A-Z]S\d{10}$';
		if (is_array($sn))
		{
			foreach ($sn as $s)
			{
				if (! preg_match($pattern, $s, $match))
				{
					return false;
				}
			}
			return true;
		}
		else
		{
			if (preg_match($pattern, $sn, $match))
			{
				return true;
			}
			return false;
		}
	}
}
