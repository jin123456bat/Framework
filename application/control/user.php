<?php
namespace application\control;

use framework\core\control;
use framework\core\request;
use framework\core\response\json;
use framework\core\session;
use application;

/**
 * 用户相关
 * @author fx
 *
 */
class user extends control
{
	/**
	 * 登陆
	 * @return \framework\core\response\json
	 */
	function login()
	{
		$username = request::post('username','');
		$password = request::post('password','');
		
		$user = new \application\entity\user(array(
			'username' => $username,
			'password' => $password,
		));
		
		if($user->validate())
		{
			if($user->auth())
			{
				$user->saveUserSession();
				$this->model('log')->add(\application\entity\user::getLoginUserId(),"登陆了系统");
				return new json(json::OK,NULL,$user);
			}
			else
			{
				return new json(json::FAILED,'账号或密码错误');
			}
		}
		else
		{
			return new json(json::FAILED,$user->getError());
		}
	}
	
	/**
	 * 注销
	 */
	function logout()
	{
		session::destory();
	}
	
	/**
	 * 用户添加
	 * @return \framework\core\response\json
	 */
	function register()
	{
		$username = request::post('username','');
		$password = request::post('password','');
		$email = request::post('email','');
		$type = request::post('type',0);
		
		$user = new \application\entity\user(array(
			'username' => $username,
			'password' => $password,
			'email' => $email,
			'type' => $type,
		),'insert');
		
		if($user->validate())
		{
			if($user->save())
			{
				$this->model('log')->add(user::getLoginUserId(),"添加了用户".$username);
				return new json(json::OK);
			}
			else
			{
				return new json(json::FAILED);
			}
		}
		return new json(json::FAILED,$user->getError());
	}
	
	/**
	 * 用户列表
	 */
	function lists()
	{
		$user = $this->model('accounts')->select();
		return new json(json::OK,NULL,$user);
	}
	
	/**
	 * 删除用户
	 */
	function remove()
	{
		$id = request::post('id',0,'int','i');
		if (!empty($id))
		{
			if($this->model('accounts')->where('id=?',array($id))->delete())
			{
				return new json(json::OK);
			}
			else
			{
				return new json(json::FAILED,'删除失败');
			}
		}
		else
		{
			return new json(json::FAILED,'参数错误');
		}
	}
	
	/**
	 * 更改用户密码
	 */
	function changePwd()
	{
		$id = request::post('id',0,'int','i');
		if (empty($id))
		{
			$id = application\entity\user::getLoginUserId();
		}
		$password = request::post('password');
		
		if($this->model('user')->where('id=?',array($id))->limit(1)->update(array(
			'password' => md5($password),
			'last_changepwd_time' => date('Y-m-d H:i:s')
		)))
		{
			return new json(json::OK);
		}
		return new json(json::FAILED,'密码更新太频繁了');
	}
	
	/**
	 * 配置访问权限
	 */
	function __access()
	{
		return array(
			array(
				'deny',
				'actions' => array('register','remove','changePwd','lists'),
				'express' => \application\entity\user::getLoginUserId()===NULL,
				'message' => new json(array('code'=>2,'result'=>'尚未登陆')),
			)
		);
	}
}