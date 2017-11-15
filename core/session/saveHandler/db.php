<?php
namespace framework\core\session\saveHandler;

use framework\core\session\saveHandler;
use framework\core\database\mysql\table;

/**
 * 
 * @author fx
 */
class db extends saveHandler
{
	
	private $_db = null;
	
	function __construct()
	{
		$table = new table('session');
		if (!$table->exist())
		{
			$table->field('session_id')->charset('utf8')->char(128)->comment('session_id');
			$table->field('createtime')->datetime()->comment('创建或更新时间');
			$table->field('content')->charset('utf8')->longtext()->comment('session序列化后的内容');
			
			$table->primary()->add('session_id');
			$table->index('createtime')->add('createtime');
		}
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
		return (string)$this->_db->where('session_id=?', array(
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
		$this->_db->where('session_id=?', array(
			$session_id,
		))->limit(1)->delete();
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
		$this->_db->where('unix_timestamp(createtime) < ?',array(
			time()-$maxlifetime
		))->delete();
		return true;
	}
}