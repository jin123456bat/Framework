<?php
namespace application\control;

use framework\core\socketControl;
use framework\core\response\json;

class test extends socketControl
{
	public function message()
	{
		$message = $this->getParam('message');
		return new json(array(
			'message' => strrev($message),
		));
	}
}