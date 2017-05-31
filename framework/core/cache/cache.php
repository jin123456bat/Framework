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

	function set($name, $value, $expires = 0);

	function get($name);
	
	function find($name);
	
	function increase($name,$amount = 1);
	
	function decrease($name,$amount = 1);
	
	function has($name);
}
