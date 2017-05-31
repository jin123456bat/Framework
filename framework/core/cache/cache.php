<?php
namespace framework\core\cache;

/**
 * cache的接口
 *
 * @author fx
 *        
 */
interface cache
{

	/**
	 * 添加或保存数据
	 * @param unknown $name
	 * @param unknown $value
	 * @param number $expires
	 */
	function set($name, $value, $expires = 0);

	/**
	 * 获取数据 过期返回null
	 * @param unknown $name
	 */
	function get($name);
	
	/**
	 * 忽略过期 获取数据
	 * @param unknown $name
	 */
	function find($name);
	
	/**
	 * 自增
	 * @param unknown $name
	 * @param number $amount
	 */
	function increase($name,$amount = 1);
	
	/**
	 * 自减
	 * @param unknown $name
	 * @param number $amount
	 */
	function decrease($name,$amount = 1);
	
	/**
	 * 判断缓存是否存在
	 * @param unknown $name
	 */
	function has($name);
	
	/**
	 * 删除缓存
	 * @param unknown $name
	 */
	function remove($name);
	
	/**
	 * 清空缓存
	 */
	function flush();
}
