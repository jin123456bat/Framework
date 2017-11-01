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
	 * 获取get参数
	 * @param string $buffer
	 */
	function get($buffer);
	
	/**
	 * 获取post参数
	 * @param unknown $buffer
	 */
	function post($buffer);
	
	/**
	 * 获取cookie
	 * @param unknown $buffer
	 */
	function cookie($buffer);
}