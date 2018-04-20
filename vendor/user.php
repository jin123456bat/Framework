<?php
namespace framework\vendor;

use framework\core\base;
use framework\core\model;
use framework\core\cookie;
use framework\core\session;
use framework\core\application;
use framework\core\entity;

class user extends base
{
	/**
	 * 已经通过的用户列表的属性
	 * @var array
	 */
	static private $_attributes = array();
	
	/**
	 * 上次登录的用户信息
	 * @var unknown
	 */
	static private $_attribute = null;
	
	/**
	 * 和用户数据相关的model
	 * @var model
	 */
	static private $_model;
	
	static private $_entity;
	
	/**
	 * 初始化
	 */
	static private function init()
	{
		$config = self::getConfig('user');
		self::$_model = self::model($config['model']);
	}
	
	/**
	 * 保存用户的信息
	 * @param unknown $data
	 */
	static private function saveUserData($data,$remember = false)
	{
		self::$_attributes[] = $data;
		self::$_attribute = $data;
		$config = self::getConfig('user');
		//保存用户的登录信息
		if($config['use_cookie'])
		{
			cookie::set('__framework_user_identity_list', self::$_attributes);
			cookie::set('__framework_user_identity', self::$_attribute);
		}
		if ($config['use_session'])
		{
			session::set('__framework_user_identity_list', self::$_attributes);
			session::set('__framework_user_identity', self::$_attribute);
		}
	}
	
	/**
	 * 添加验证信息
	 * @param array $data
	 * @param boolean $remember 是否记住用户信息
	 * @param string $message 当登陆失败的时候保存失败原因
	 */
	static public function addVerify(array $data,$remember = false,&$message = array())
	{
		self::init();
		$config = self::getConfig('user');
		
		if (isset($data[$config['primary_key']]))
		{
			//通过主键登录
			$user = self::$_model->where(array(
				$config['primary_key'] => $data[$config['primary_key']],
			))->limit(1)->find();
		}
		else if (isset($data[$config['password_key']]))
		{
			//通过账号和密码登录
			$password = $data[$config['password_key']];
			if (empty($password))
			{
				$message[$config['password_key']][] = '密码不能为空';
				return false;
			}
			
			$verify_key = array();
			settype($config['verify_key'], 'array');
			foreach ($config['verify_key'] as $key)
			{
				if (isset($data[$key]))
				{
					$verify_key[$key] = $data[$key];
				}
			}
			//上面的foreach会导致内部指针超出去  导致下面的current返回false
			reset($config['verify_key']);
			
			if (empty($verify_key))
			{
				$message[current($config['verify_key'])][] = '请填写用户名';
				return false;
			}
			
			$user = self::$_model->where($verify_key,array(),'or')->limit(1)->find();	
			
			if (empty($user))
			{
				$message[current($config['verify_key'])][] = '用户不存在';
				return false;
			}
			
			if(encryption::password_verify($password,$user[$config['password_key']]))
			{
				self::saveUserData($user,$remember);
				return true;
			}
			else
			{
				$message[$config['password_key']][] = '密码错误';
				return false;
			}
		}
		$message[][] = '信息不全';
		return false;
	}
	
	/**
	 * 获取已经登录的用户列表
	 */
	static public function getVerifiedList()
	{
		$config = self::getConfig('user');
		if ($config['use_cookie'])
		{
			return cookie::get('__framework_user_identity_list');
		}
		if ($config['use_session'])
		{
			return session::get('__framework_user_identity_list');
		}
	}
	
	/**
	 * 获取当前登录的用户的信息
	 */
	static public function getLastVerified()
	{
		if ($config['use_cookie'])
		{
			return cookie::get('__framework_user_identity');
		}
		if ($config['use_session'])
		{
			return session::get('__framework_user_identity');
		}
	}
	
	/**
	 * 设置当前登录的用户的信息
	 * @param unknown $user
	 */
	static public function setLastVerified($user)
	{
		self::$_attribute = $user;
		$config = self::getConfig('user');
		if ($config['use_cookie'])
		{
			cookie::set('__framework_user_identity', self::$_attribute);
		}
		if ($config['use_session'])
		{
			session::set('__framework_user_identity', self::$_attribute);
		}
	}
	
	/**
	 * 注册用户
	 * @param unknown $data
	 * @param string &$message
	 * @return boolean
	 */
	static public function register($data,&$message) 
	{
		self::init();
		$config = self::getConfig('user');
		self::$_entity = application::load(entity::class,$config['entity'],array($data));
		if (self::$_entity->validate())
		{
			if(self::$_entity->save())
			{
				$user = self::$_entity->getData();
				self::saveUserData($user);
				return true;
			}
			return false;
		}
		else
		{
			$message = self::$_entity->getError();
			return false;
		}
	}
	
	/**
	 * 退出当前登录的用户
	 * @return boolean
	 */
	static public function logout()
	{
		self::$_attribute = null;
		$config = self::getConfig('user');
		if ($config['use_cookie'])
		{
			return cookie::delete('__framework_user_identity');
		}
		if ($config['use_session'])
		{
			return session::delete('__framework_user_identity');
		}
		return false;
	}
}
