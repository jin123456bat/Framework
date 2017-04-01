<?php
namespace application\control;

use framework\core\socket\websocket;
use framework\core\console;

class test extends websocket
{
	/**
	 * {@inheritDoc}
	 * @see \framework\core\socket\websocket::message()
	 */
	public function message($message,$socket)
	{
		$message = json_decode($message,true);
		$message['content'] = '你是不是在说:'.$message['content'];
		$this->write(json_encode($message),$socket);
	}
}