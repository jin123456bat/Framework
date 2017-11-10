<?php
namespace framework\core\protocal;

use framework\core\connection;

interface protocal
{	
	/**
	 * 当第一次连接的时候执行的方法
	 * 以websocket为例，这里可以验证握手机制
	 * @param connection $connection
	 * @return boolean 返回false的时候代码执行完毕，不在继续执行下面的代码
	 */
	public function init($connection);
	
	/**
	 * 编码方法
	 * 通过socket发送的数据需要通过encode方法进行编码，然后在发送
	 * @param string $string
	 */
	public function encode($string);
	
	/**
	 * 解码方法
	 * 从socket收到的数据需要解码然后才能读取
	 * @param string $buffer
	 */
	public function decode($buffer);
	
	
	public function parse($string);
	
	/**
	 * 发送完数据后是否需要关闭连接
	 * @return boolean
	 */
	public function closeAfterWrite();
}