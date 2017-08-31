<?php
namespace framework\core\cache;

/**
 * cache的接口
 * 
 * @author fx
 */
interface cache
{

	/**
	 * 添加数据 数据存在会添加失败并返回false
	 * 
	 * @param unknown $name        
	 * @param unknown $value        
	 * @param number $expires        
	 */
	function add($name, $value, $expires = 0);

	/**
	 * 添加或保存数据
	 * 
	 * @param string $name        
	 * @param mixed $value        
	 * @param number $expires        
	 */
	function set($name, $value, $expires = 0);

	/**
	 * 获取数据 过期或者不存在返回null
	 * 
	 * @param string $name        
	 */
	function get($name);

	/**
	 * 自增
	 * 
	 * @param unknown $name        
	 * @param number $amount        
	 */
	function increase($name, $amount = 1);

	/**
	 * 自减
	 * 
	 * @param unknown $name        
	 * @param number $amount        
	 */
	function decrease($name, $amount = 1);

	/**
	 * 判断缓存是否存在
	 * 
	 * @param unknown $name        
	 */
	function has($name);

	/**
	 * 删除缓存
	 * 
	 * @param unknown $name        
	 */
	function remove($name);

	/**
	 * 清空缓存
	 */
	function flush();
}
