<?php
namespace application\control;
use framework\core\control;
use framework\core\response;
use framework\core\session;
class index extends control
{
	function index()
	{
		session::set('a',898979);
		return new response(session::get('a'));
	}
}