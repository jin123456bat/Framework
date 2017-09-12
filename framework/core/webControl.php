<?php
namespace framework\core;

use framework\core\control;

class webControl extends control
{

	function initlize()
	{
		if (request::php_sapi_name() != 'web')
		{
			return new response('not found', 404);
		}
		
		return parent::initlize();
	}
}