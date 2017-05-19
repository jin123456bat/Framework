<?php
namespace framework\core\database\mysql;
class table
{
	private $_struct = array();
	
	function varchar($name,$length)
	{
		return new field($name,'varchar',$length);
	}
		
	function char($name,$length)
	{
		return new field($name, 'char',$length);
	}
}

