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
	 * @param string $name        
	 * @param mixed $value        
	 * @param number $expires
	 * @return boolean 成功返回true 失败返回false
	 */
	function add($name, $value, $expires = 0);

	/**
	 * 添加或保存数据
	 * 
	 * @param string $name        
	 * @param mixed $value        
	 * @param number $expires
	 * @return boolean 成功返回true 失败返回false
	 */
	function set($name, $value, $expires = 0);

	/**
	 * 获取数据 过期或者不存在返回null
	 * @param string $name
	 * @return mixed|NULL
	 */
	function get($name);

	/**
	 * 自增
	 * @param unknown $name        
	 * @param number $amount
	 * @return boolean 成功或者失败  true或者false
	 */
	function increase($name, $amount = 1);

	/**
	 * 自减
	 * @param unknown $name        
	 * @param number $amount
	 * @return boolean 成功或者失败  true或者false
	 */
	function decrease($name, $amount = 1);

	/**
	 * 判断缓存是否存在
	 * 
	 * @param unknown $name
	 * @return boolean 存在返回true失败返回false
	 */
	function has($name);

	/**
	 * 删除指定缓存
	 * @param string $name
	 * @return boolean 成功返回true失败返回false
	 */
	function remove($name);

	/**
	 * 清空全部缓存
	 * @return boolean 成功返回true失败返回false
	 */
	function flush();
}
