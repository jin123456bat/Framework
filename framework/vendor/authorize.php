<?php
namespace framework\vendor;
use framework\core\component;

/**
 * 不管你使用什么模式，用户名密码？   ID？  appid和appsecret？
 * 用户授权验证的类
 * @author fx
 *
 */
class authorize extends component
{
	private $_data = array();
	
	/**
	 * 检查数据库
	 * {@inheritDoc}
	 * @see \framework\core\component::initlize()
	 */
	function initlize()
	{
		$this->model('authorize')->create(array(
			''
		));
	}
	
	/**
	 * 设置用户数据
	 * @param array $data
	 */
	function setData($data = array())
	{
		$this->_data = array_merge($this->_data,$data);
	}
	
	/**
	 * 验证用户授权是否通过
	 * @param array $data
	 */
	function authorize($data = array())
	{
		
	}
	
	/**
	 * 当验证通过之后通过这个函数可以获取验证成功的用户ID
	 */
	function getAuthedData()
	{
		
	}
}