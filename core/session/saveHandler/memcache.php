<?php
namespace framework\core\session\saveHandler;
use framework\core\session\saveHandler;

class memcache extends saveHandler
{
	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::open()
	 */
	public function open($save_path, $session_name)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::close()
	 */
	public function close()
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::read()
	 */
	public function read($session_id)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::write()
	 */
	public function write($session_id, $session_data)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::destroy()
	 */
	public function destroy($session_id)
	{
		// TODO Auto-generated method stub
		
	}

	/**
	 * {@inheritDoc}
	 * @see SessionHandlerInterface::gc()
	 */
	public function gc($maxlifetime)
	{
		// TODO Auto-generated method stub
		
	}

	
}