<?php
namespace framework\vendor;
use framework\core\component;
use framework\core\model;
use framework\core\base;
use framework\core\database\mysql\table;

/**
 * 不管你使用什么模式，用户名密码？   ID？  appid和appsecret？
 * 用户授权验证的类
 * @author fx
 *
 */
class authorize extends component
{
	/**
	 * 验证数据
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * 验证规则
	 * @var array
	 */
	private $_rules = array(
		'username' => 'exists',//登陆验证 必须存在
	);
	
	function __construct()
	{
		$tableName = 'authorize';
		$table = new table($tableName);
		$table->int('id')->primary()->AI();
		$table->varchar('username', 64);
		
		$this->model($tableName)->create($table);
	}
	
	/**
	 * 设置验证规则
	 */
	function rules($data)
	{
		return array(
			
		);
	}
	
	
	/**
	 * 手动验证
	 */
	function attempt($data)
	{
		
	}
	
	/**
	 * 检查当前用户是否已经登陆
	 */
	function check()
	{
		
	}
	
	/**
	 * 已经登陆的用户列表
	 */
	function loginedList()
	{
		
	}
}