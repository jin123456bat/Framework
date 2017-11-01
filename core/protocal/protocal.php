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
	 * @return boolean 返回false的时候中断连接
	 */
	function init($request,$connection);
	
	/**
	 * 编码方法
	 * 通过socket发送的数据需要通过encode方法进行编码，然后在发送
	 * @param unknown $string
	 */
	function encode($string);
	
	/**
	 * 解码方法
	 * 从socket收到的数据需要解码然后才能读取
	 * @param unknown $buffer
	 */
	function decode($buffer);
	
	/**
	 * 解码完后的字符串转化为参数数组并且参数可以通过get的形式访问
	 * @param unknown $string
	 */
	function parse($string);
}