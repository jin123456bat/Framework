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
	 * 配置
	 * @var array
	 */
	static private $_config = array(
		'model' => 'admin',
		'entity' => 'admin',
		
		//用户名的字段
		'verify_key' => array(
			'username',
			'email',
			'telephone',
		),
		
		//用户信息通过cookie保存
		'use_cookie' => false,
		//用户信息通过session保存
		'use_session' => true,
		
		
		//密码的字段
		'password_key' => 'password',
		
		//主键字段
		'primary_key' => 'id',
	);
	
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
		self::$_model = self::model(self::$_config['model']);
	}
	
	/**
	 * 保存用户的信息
	 * @param unknown $data
	 */
	static private function saveUserData($data)
	{
		self::$_attributes[] = $data;
		self::$_attribute = $data;
		//保存用户的登录信息
		if(self::$_config['use_cookie'])
		{
			cookie::set('__framework_user_identity_list', self::$_attributes);
			cookie::set('__framework_user_identity', self::$_attribute);
		}
		if (self::$_config['use_session'])
		{
			session::set('__framework_user_identity_list', self::$_attributes);
			session::set('__framework_user_identity', self::$_attribute);
		}
	}
	
	/**
	 * 添加验证信息
	 * @param unknown $data
	 * @param string $message
	 */
	static public function addVerify($data,&$message = '')
	{
		self::init();
		
		if (isset($data[self::$_config['primary_key']]))
		{
			//通过主键登录
			$user = self::$_model->where(array(
				self::$_config['primary_key'] => $data[self::$_config['primary_key']],
			))->limit(1)->find();
		}
		else if (isset($data[self::$_config['password_key']]))
		{
			//通过账号和密码登录
			$password = $data[self::$_config['password_key']];
			if (empty($password))
			{
				$message = '密码不能为空';
				return false;
			}
			
			$verify_key = array();
			settype(self::$_config['verify_key'], 'array');
			foreach (self::$_config['verify_key'] as $key)
			{
				if (isset($data[$key]))
				{
					$verify_key[$key] = $data[$key];
				}
			}
			
			if (empty($verify_key))
			{
				$message = '请填写用户名';
				return false;
			}
			
			$user = self::$_model->where($verify_key,array(),'or')->limit(1)->find();	
			
			if (empty($user))
			{
				$message = '用户不存在';
				return false;
			}
			
			if(encryption::password_verify($password,$user[self::$_config['password_key']]))
			{
				self::saveUserData($user);
				return true;
			}
			else
			{
				$message = '密码错误';
				return false;
			}
		}
		
		$message = '信息不全';
		return false;
	}
	
	/**
	 * 获取已经登录的用户列表
	 */
	static public function getVerifidList()
	{
		if (self::$_config['use_cookie'])
		{
			return cookie::get('__framework_user_identity_list');
		}
		if (self::$_config['use_session'])
		{
			return session::get('__framework_user_identity_list');
		}
	}
	
	/**
	 * 获取上一次登录的用户的信息
	 */
	static public function getLastVerifid()
	{
		if (self::$_config['use_cookie'])
		{
			return cookie::get('__framework_user_identity');
		}
		if (self::$_config['use_session'])
		{
			return session::get('__framework_user_identity');
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
		self::$_entity = application::load(entity::class,self::$_config['entity'],array($data));
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
}
