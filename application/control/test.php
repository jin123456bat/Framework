<?php
namespace application\control;

use framework\core\socketControl;
use framework\core\response\json;

class test extends socketControl
{
	public function message()
	{
		$message = $this->getParam('message');
		self::sendAllClient(new json(array(
			'message' => $message.'ABC'
		)));
		return new json(array(
			'message' => strrev($message),
		));
	}
}