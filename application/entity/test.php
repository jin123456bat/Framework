<?php 
namespace application\entity;
use framework\core\entity;

class test extends entity
{
	function __rules()
	{
		return array(
			'required' => array(
				'fields' => 'username,password',
				'message' => '用户名或密码必须填写',
			),
			//最标准的写法应该是这样子的
			'>=' => array(
				'fields' => array(
					'username' => array(
						'render' => 'mb_strlen',//对参数先经过这个函数  然后在做判断
						'message' => '用户名长度不能低于6位',
						'data' => 6,
						'on' => array('insert'),//在什么样的情景下才可用，默认是所有情景，可以数组，多个情景 也可以是逗号分开的字符串
					),
					'age' => array(
						'message' => '年龄必须大于等于18岁',
						'data' => 18,
					)
				),
			),
			'unsafe' => array(
				'fields' => 'sql',//提交的参数中不能包含sql这个参数
				'message' => '提交不能存在sql参数',
			),
			'int' => array(
				'fields' => 'age',
				'message' => '年龄必须是整数',
			),
			'decimal' => array(
				'fields' => array(
					'money' => '金额必须是数字'
				)
			),
			'unique' => array(
				'telephone' => array(
					'message' => '手机号码必须唯一',
				)
			),
			'telephone' => array(
				'telephone' => '请填写一个正确的手机号码',
			),
			'ip' => array(
				'ip' => 'IP错误',
			),
			'email' => array(
				'email' => 'email错误',
			),
			'enum' => array(
				'fields' => array('sex'),
				'data' => array(
					'男','女',
				)
			),
			'datetime' => array(
				'fields' => 'time',
				'data' => 'Y-m-d H:i:s', //这个应该是可选的，假如没有格式限制应该任意格式都是允许的
				'message' => array(
					'时间错误',
				)
			),
			'function' => array(//自定义函数
				'fields' => 'relations',
				'render' => function(){
					return '好的';
				},
				'message' => '123123',
				'callback' => function($val){
					return !!$val;//返回true或者false
				},
			)
		);
	}
}
?>