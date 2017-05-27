<?php
namespace framework\vendor;
use framework\core\component;
use framework\core\model;
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
		$this->model($tableName)->drop();
		$table = new table($tableName);
		
		$table->int('id');
		$table->varchar('username', 64)->unique()->comment('用户名');
		$table->timestamp('regtime')->comment('注册时间');
		$table->char('telephone', 11)->comment('手机号码');
		$table->varchar('email', 128)->comment('邮箱');
		
		//$table->unique('telephone','a');
		$table->index('telephone');
		
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