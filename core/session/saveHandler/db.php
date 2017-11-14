<?php
namespace framework\core\session\saveHandler;

use framework\core\session\saveHandler;

/**
 * 
 * @author fx
 */
class db extends saveHandler
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
		return $this->_db->where('session_id=?', array(
			$session_id,
		))->scalar('content');
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
		$this->write($session_id, '');
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
		return true;
	}
}