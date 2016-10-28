<?php
namespace application\entity;

use framework\lib\data;
use framework\core\session;
use framework\core\request;

class user extends data
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
	 * @return string
	 */
	function __model()
	{
		return 'accounts';
	}
	
	function __rules()
	{
		return array(
			array('required' => 'username,password','message'=>'请填写{field}'),
			array('unique' => 'username','message'=>'用户名已经存在','on'=>'insert'),
			array('enum' => 'type','type' => array(0,1),'message' => '只能是普通用户或者超级管理员'),
			array('email'=>'email','message' => '请填写正确的邮箱'),
		);
	}
	
	/**
	 * 验证用户名和密码
	 * @return boolean
	 */
	function auth()
	{
		$this->user = $this->model($this->__model())->where('username=?',array($this->username))->find();
		if(!empty($this->user))
		{
			if ($this->user['password_error_num']>=10)
			{
				return false;
			}
			else
			{
				if ($this->user['password'] == self::encrypt($this->password))
				{
					$this->model($this->__model())->where('username=?',array($this->username))->limit(1)->update(array(
						'last_login_time' => date('Y-m-d H:i:s'),
						'last_login_ip' => request::php_sapi_name()=='web'?$_SERVER['REMOTE_ADDR']:'cli',
						'password_error_num'=>0,
					));
					return true;
				}
				else
				{
					$this->model($this->__model())->where('username=?',array($this->username))->limit(1)->update('password_error_num+=',1);
					return false;
				}
			}
		}
		return false;
	}
	
	function save()
	{
		if ($this->_scene == 'insert')
		{
			$this->password = self::encrypt($this->password);
		}
		return parent::save();
	}
	
	/**
	 * 保存用户信息，必须调用auth之后才可以调用
	 * 
	 */
	function saveUserSession()
	{
		if (isset($this->user) && !empty($this->user))
		{
			session::set('uid', $this->user['id']);
		}
	}
	
	/**
	 * 获取当前登录用户的id
	 * @return NULL|unknown
	 */
	public static function getLoginUserId()
	{
		return session::get('uid');
	}
	
	/**
	 * 用户密码加密
	 * @param unknown $password
	 * @return string
	 */
	public static function encrypt($password)
	{
		return md5($password);
	}
}