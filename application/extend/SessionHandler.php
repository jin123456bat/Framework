<?php
namespace application\extend;

use framework\core\base;
use framework\core\model;

class SessionHandler extends base
{
	private $_db = NULL;
	
	function __construct()
	{
		
	}
	
	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::open()
	 */
	public function open($save_path, $name)
	{
		$this->_db = $this->model('session');
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::close()
	 */
	public function close()
	{
		// TODO Auto-generated method stub
		return true;
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::read()
	 */
	public function read($session_id)
	{
		// TODO Auto-generated method stub
		$result = $this->_db->where('session_id=?',array($session_id))->scalar('content');
		return $result;
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::write()
	 */
	public function write($session_id, $session_data)
	{
		// TODO Auto-generated method stub
		$result = $this->_db->where('session_id=?',array($session_id))->find();
		if(empty($result))
		{
			$result = $this->_db->insert(array(
				'session_id'=>$session_id,
				'content'=>$session_data
			));
			return $result;
		}
		else
		{
			$result = $this->_db->where('session_id=?',array($session_id))->update('content',$session_data);
			return $result;
		}
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::destroy()
	 */
	public function destroy($session_id)
	{
		// TODO Auto-generated method stub
		return $this->_db->where('session_id=?',array($session_id))->delete();
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		// TODO Auto-generated method stub
		$this->_db->where('UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(createtime) > ?',array($maxlifetime))->delete();
		return true;
	}


}