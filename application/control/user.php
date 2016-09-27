<?php
namespace application\control;

use framework\core\control;
use framework\core\request;
use framework\core\response\json;
use framework\core\session;

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
		
		$user = new \application\entity\user(array(
			'username' => $username,
			'password' => $password,
		),'insert');
		
		if($user->validate())
		{
			if($user->save())
			{
				return new json(json::OK);
			}
			else
			{
				return new json(json::FAILED);
			}
		}
		return new json(json::FAILED,$user->getError());
	}
}