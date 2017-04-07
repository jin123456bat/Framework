<?php
namespace application\extend;

use framework\core\webSocket;
use framework\core\console;

class chat extends webSocket
{
	function initlize()
	{
		console::log('chat is running');
		parent::initlize();
	}
	
	/**
	 * 端口号
	 * @return number
	 */
	function __port()
	{
		return 2001;
	}
}