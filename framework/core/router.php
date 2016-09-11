<?php
namespace framework\core;

class router extends component
{
	function __construct()
	{
		parent::__construct();
	}
	
	function initlize()
	{
		$this->parse();
		parent::initlize();
	}
	
	private function parse()
	{
		$config = $this->getConfig('router');
		if ($config['mode'] == 'normal')
		{
			var_dump($_SERVER['PATH_TRANSLATED']);
		}
	}
}