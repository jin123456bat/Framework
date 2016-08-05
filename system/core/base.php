<?php
namespace system\core;
class base
{
	protected $_SESSION;
	
	protected $_COOKIE;
	
	function __construct()
	{
		
	}
	
	public function initlize()
	{
		
	}
	
	public function hash()
	{
		return spl_object_hash($this);
	}
}