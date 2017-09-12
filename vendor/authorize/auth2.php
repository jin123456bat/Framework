<?php
namespace framework\vendor\authorize;
use framework\core\component;

class auth2 extends component
{
	function __construct()
	{
		$config = $this->getConfig('auth2');
		
	}
	
	/**
	 * 安装数据库的一些操作
	 * {@inheritDoc}
	 * @see \framework\core\component::initlize()
	 */
	function initlize()
	{
		
	}
	
	/**
	 * 验证token是否有效
	 * @param unknown $token
	 */
	function authToken($token)
	{
		
	}
	
	/**
	 * 验证用户名和密码是否有效
	 * @param string $user
	 * @param string $pass
	 * @param string $code
	 * @return array('token','expires')
	 * 	token是验证的token，expires是token有效期0永久，
	 */
	function authUser($user,$pass,$code = '')
	{
		
	}
}