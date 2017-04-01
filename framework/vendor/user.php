<?php
namespace framework\vendor;

use framework\core\base;

class user extends base
{

	/**
	 * 用户属性
	 *
	 * @var unknown
	 */
	private $_attributes;

	/**
	 * 是否自动登陆
	 *
	 * @var unknown
	 */
	public $_autologin = true;

	/**
	 * 自动登陆的有效期 0代表不限制 单位秒
	 *
	 * @var integer
	 */
	public $_logintimeout = 0;

	/**
	 * 是否使用cookie作为验证信息
	 *
	 * @var unknown
	 */
	public $_cookie = false;

	/**
	 * 是否使用session作为验证信息
	 *
	 * @var unknown
	 */
	public $_session = true;

	/**
	 * 登陆地址
	 *
	 * @var unknown
	 */
	public $_loginurl = null;

	function __construct($user)
	{
	}

	/**
	 * 判断用户是否已经登陆
	 */
	static function isLogin()
	{
	}

	/**
	 * 刷新用户信息，
	 */
	function refresh($callback = null)
	{
		if (is_callable($callback))
		{
			call_user_func_array($callback, [
				$this
			]);
		}
	}

	/**
	 * 消除用户信息
	 */
	function logout()
	{
	}

	/**
	 * 记录用户信息
	 */
	function login()
	{
	}

	/**
	 * 认证过程
	 */
	function auth($username, $password)
	{
	}

	/**
	 * 刷新认证信息
	 */
	function reauth()
	{
	}
}
