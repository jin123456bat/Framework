<?php
namespace framework\core\response;

use framework\core\response;
use framework\core\view;

/**
 * 提示消息
 * 跳转地址
 * @author jin
 */
class message extends response
{
	private $_template = SYSTEM_ROOT.'/assets/template/message.html';
	
	function __construct($message,$url = '',$timeout = 3)
	{
		$view = new view($this->_template);
		$view->assign('message', $message);
		$view->assign('url', $url);
		$view->assign('timeout', $timeout);
		parent::__construct($view->getBody());
	}
}