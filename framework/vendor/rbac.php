<?php
namespace framework\vendor;

use framework\core\component;

/**
 * rbac类
 * 使用之前必须配置who what how等变量
 * 这里使用rbac96模型中的rbac0
 * @author jin
 * @link http://www.cnblogs.com/zkwarrior/p/5792947.html
 *
 */
class rbac extends component
{
	/**
	 * 用户表
	 * @var string
	 */
	private $_users= 'user';
	
	/**
	 * 用户表主键
	 * @var string
	 */
	private $_users_key = 'id';
	
	/**
	 * 角色表
	 * @var string
	 */
	private $_roles= '';
	
	/**
	 * 对象表
	 * @var string
	 */
	private $_objects = '';
	
	/**
	 * 对象唯一标识字段
	 * @var string
	 */
	private $_objects_id = 'id';
	
	/**
	 * 操作表
	 * @var string
	 */
	private $_operations = '';
	
	/**
	 * 权限表
	 * @var string
	 */
	private $_permissions = '';
	
	
}
