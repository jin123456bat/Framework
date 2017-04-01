<?php
namespace application\control;

use framework\core\control;
use framework\core\request;
use framework\core\response\json;
use framework\core\session;
use application;

/**
 * 用户相关
 *
 * @author fx
 *        
 */
class user extends control
{

	/**
	 * 登陆
	 *
	 * @return \framework\core\response\json
	 */
	function login()
	{
		$username = request::post('username', '');
		$password = request::post('password', '');
		
		$user = new \application\entity\user(array(
			'username' => $username,
			'password' => $password
		));
		
		if ($user->validate())
		{
			if ($user->auth())
			{
				$user->saveUserSession();
				$this->model('log')->add(\application\entity\user::getLoginUserId(), "登陆了系统");
				return new json(json::OK, null, $user);
			}
			else
			{
				return new json(json::FAILED, '账号或密码错误');
			}
		}
		else
		{
			return new json(json::FAILED, $user->getError());
		}
	}

	/**
	 * 注销
	 */
	function logout()
	{
		session::destory();
		return new json(json::OK);
	}

	/**
	 * 用户添加
	 *
	 * @return \framework\core\response\json
	 */
	function register()
	{
		$username = request::post('username', '');
		$password = request::post('password', '');
		$email = request::post('email', '');
		$type = request::post('type', 0);
		
		$user = new \application\entity\user(array(
			'username' => $username,
			'password' => $password,
			'email' => $email,
			'type' => $type
		), 'insert');
		
		if ($user->validate())
		{
			if ($user->save())
			{
				if ($type == 0)
				{
					$cds_group_id = request::post('cds_group_id', array(), null, 'a');
					foreach ($cds_group_id as $id)
					{
						$this->model('admin_cds_group')->insert(array(
							'uid' => $user->id,
							'cds_group_id' => $id
						));
					}
				}
				
				$this->model('log')->add(\application\entity\user::getLoginUserId(), "添加了用户" . $username);
				return new json(json::OK);
			}
			else
			{
				return new json(json::FAILED);
			}
		}
		return new json(json::FAILED, $user->getError());
	}

	/**
	 * 用户列表
	 */
	function lists()
	{
		/*
		 * $uid = \application\entity\user::getLoginUserId();
		 * $pageNum = $this->model('accounts')->where('id=?',array($uid))->scalar('pageNum');
		 * if (empty($pageNum))
		 * {
		 * $pageNum = 10;
		 * }
		 *
		 * $page = request::param('pageNum',1,'','i');
		 * if ($page<1)
		 * {
		 * $page = 1;
		 * }
		 *
		 * $start = ($page-1)*$pageNum;
		 * $length = $pageNum;
		 */
		$start = request::param('start', 0, '', 'i');
		$length = request::param('length', 10, '', 'i');
		
		$user = $this->model('accounts')
			->limit($start, $length)
			->select(array(
			'id',
			'username',
			'email',
			'type'
		));
		
		foreach ($user as &$u)
		{
			$u['cds_group_id'] = array();
			if ($u['type'] != 1)
			{
				$cds_group_id = $this->model('admin_cds_group')
					->where('uid=?', array(
					$u['id']
				))
					->select('cds_group_id');
				foreach ($cds_group_id as $id)
				{
					$u['cds_group_id'][] = $id['cds_group_id'];
				}
			}
		}
		
		$total = $this->model('accounts')->count();
		
		return new json(json::OK, null, array(
			'total' => $total,
			'data' => $user,
			'length' => count($user)
		));
	}

	/**
	 * 获取或设置当前页码
	 *
	 * @return \framework\core\response\json
	 */
	function pageNum()
	{
		$pageNum = request::param('pageNum', 0, '', 'i');
		$uid = \application\entity\user::getLoginUserId();
		if (empty($pageNum))
		{
			return new json(json::OK, null, $this->model('accounts')
				->where('id=?', array(
				$uid
			))
				->scalar('pageNum'));
		}
		else
		{
			$this->model('accounts')
				->where('id=?', array(
				$uid
			))
				->limit(1)
				->update('pageNum', $pageNum);
		}
	}

	/**
	 * 删除用户
	 */
	function remove()
	{
		$id = request::post('id', 0, 'int', 'i');
		if (! empty($id))
		{
			if ($this->model('accounts')
				->where('id=?', array(
				$id
			))
				->delete())
			{
				return new json(json::OK);
			}
			else
			{
				return new json(json::FAILED, '删除失败');
			}
		}
		else
		{
			return new json(json::FAILED, '参数错误');
		}
	}

	/**
	 * 修改保存用户信息
	 */
	function save()
	{
		$id = request::post('id');
		$username = request::post('username');
		$password = request::post('password');
		$email = request::post('email');
		$type = request::post('type');
		if (empty($id))
		{
			return new json(json::FAILED, 'id不能为空');
		}
		$user = $this->model('accounts')
			->where('id=?', array(
			$id
		))
			->find();
		
		$user = new \application\entity\user($user, 'save');
		if (! empty($username))
		{
			$user->username = $username;
		}
		if (! empty($password))
		{
			$user->password = $user->encrypt($password);
		}
		if (! empty($email))
		{
			$user->email = $email;
		}
		if (! empty($type))
		{
			$user->type = $type;
		}
		
		if ($user->validate())
		{
			$user->save();
			$this->model('admin_cds_group')
				->where('uid=?', array(
				$id
			))
				->delete();
			if ($type != 1)
			{
				$cds_group_id = request::post('cds_group_id', array(), null, 'a');
				foreach ($cds_group_id as $id)
				{
					$this->model('admin_cds_group')->insert(array(
						'uid' => $user->id,
						'cds_group_id' => $id
					));
				}
			}
			
			$this->model('log')->add(\application\entity\user::getLoginUserId(), "修改了用户信息" . $username);
			return new json(json::OK);
		}
		return new json(json::FAILED, $user->getError());
	}

	/**
	 * 更改用户密码
	 */
	function changePwd()
	{
		$id = request::post('id', 0, 'int', 'i');
		if (empty($id))
		{
			$id = application\entity\user::getLoginUserId();
		}
		$password = request::post('password');
		
		if ($this->model('user')
			->where('id=?', array(
			$id
		))
			->limit(1)
			->update(array(
			'password' => md5($password),
			'last_changepwd_time' => date('Y-m-d H:i:s')
		)))
		{
			return new json(json::OK);
		}
		return new json(json::FAILED, '密码更新太频繁了');
	}

	/**
	 * 配置访问权限
	 */
	function __access()
	{
		return array(
			array(
				'deny',
				'actions' => array(
					'register',
					'lists',
					'remove',
					'save',
					'logout',
					'pageNum'
				),
				'express' => \application\entity\user::getLoginUserId() === null,
				'message' => new json(array(
					'code' => 2,
					'result' => '尚未登陆'
				))
			)
		);
	}
}
