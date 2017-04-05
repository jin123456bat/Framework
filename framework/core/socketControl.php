<?php
namespace framework\core;
class socketControl extends control
{
	/**
	 * 当链接开始的时候会触发这个函数
	 * @param unknown $client
	 */
	function __open($client)
	{
		console::log('socket connect success '.$client);
	}
	
	/**
	 * 当链接中断的时候会触发这个函数
	 * @param unknown $socket
	 */
	function __disconnect($socket)
	{
		console::log('socket connect disconnect '.$socket);
	}
	
	/**
	 * 当socket底层出现错误的时候会触发这个函数
	 * @param unknown $level
	 * @param unknown $errno
	 * @param unknown $error
	 */
	public function __error($level,$errno,$error)
	{
		console::log('['.date('Y-m-d H:i:s').']['.$level.'] socket error: ('.$errno.') '.$error,console::TEXT_COLOR_RED);
	}
}