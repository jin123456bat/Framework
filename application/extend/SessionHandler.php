<?php
namespace application\extend;

use framework\core\base;
use framework\core\model;

/**
 * 重写的sessionHandler  必须继承SessionHandlerInterface接口
 * @author fx
 *
 */
class SessionHandler extends base implements \SessionHandlerInterface
{
	private $_db = null;

	function __construct()
	{
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::open()
	 */
	public function open($save_path, $name)
	{
		$this->_db = $this->model('session');
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::close()
	 */
	public function close()
	{
		// TODO Auto-generated method stub
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::read()
	 */
	public function read($session_id)
	{
		// TODO Auto-generated method stub
		$maxtime = ini_get('session.gc_maxlifetime');
		$result = $this->_db->where('session_id=? and UNIX_TIMESTAMP(now())-UNIX_TIMESTAMP(createtime)<?', array(
			$session_id,
			$maxtime
		))->scalar('content');
		return $result;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::write()
	 */
	public function write($session_id, $session_data)
	{
		$this->_db->duplicate(array(
			'content' => $session_data,
			'createtime' => date('Y-m-d H:i:s')
		))->insert(array(
			'session_id' => $session_id,
			'content' => $session_data,
			'createtime' => date('Y-m-d H:i:s')
		));
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::destroy()
	 */
	public function destroy($session_id)
	{
		// TODO Auto-generated method stub
		$this->_db->where('session_id=?', array(
			$session_id
		))->delete();
		return true;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see SessionHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		// TODO Auto-generated method stub
		$this->_db->where('UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(createtime) > ?', array(
			$maxlifetime
		))->delete();
		return true;
	}
}
