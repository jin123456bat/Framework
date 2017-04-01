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
}
