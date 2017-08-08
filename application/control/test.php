<?php
namespace application\control;

use framework\core\socketControl;
use framework\core\response\json;
use framework\core\request;

class test extends socketControl
{

	public function message()
	{
		$message = $this->getParam('message');
		self::sendAllClient(new json(array(
			'message' => $message . 'ABC'
		)));
		return new json(array(
			'message' => strrev($message)
		));
	}

	function __access()
	{
		return array(
			array(
				'deny',
				'express' => (request::php_sapi_name() != 'socket'),
				'message' => '只能通过socket方式连接'
			)
		);
	}
}