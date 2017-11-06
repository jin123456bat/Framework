<?php
namespace framework\core\protocal;

use framework\core\connection;

interface protocal
{
	/**
	 * 当第一次连接的时候执行的方法
	 * 以websocket为例，这里可以验证握手机制
	 * @param string $request
	 * $param connection $connection
	 * @return boolean 返回false的时候代码执行完毕，不在继续执行下面的代码
	 */
	function init($request,$connection);
	
	/**
	 * 编码方法
	 * 通过socket发送的数据需要通过encode方法进行编码，然后在发送
	 * @param string $string
	 */
	function encode($string);
	
	/**
	 * 解码方法
	 * 从socket收到的数据需要解码然后才能读取
	 * @param string $buffer
	 */
	function decode($buffer);
	
	
	/**
	 * 获取$_GET
	 * @param string $buffer
	 */
	function get($buffer);
	
	/**
	 * 获取$_POST
	 * @param unknown $buffer
	 */
	function post($buffer);
	
	/**
	 * 获取$_COOKIE
	 * @param unknown $buffer
	 */
	function cookie($buffer);
	
	/**
	 * 获取$_SERVER
	 * @param unknown $buffer
	 */
	function server($buffer);
	
	/**
	 * 获取$_FILES
	 * @param unknown $buffer
	 */
	function files($buffer);
	
	/**
	 * 获取$_REQUEST
	 * @param unknown $buffer
	 */
	function request($buffer);
	
	/**
	 * $_ENV
	 * @param unknown $buffer
	 */
	function env($buffer);
	
	/**
	 * 获取$_SESSION
	 * @param unknown $buffer
	 */
	function session($buffer);
}