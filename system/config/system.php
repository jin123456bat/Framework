<?php
namespace framework\config;

use framework\lib\config;

class system extends config
{
	public $_version = 1.0;
	
	public $_charset = 'utf-8';
	
	public $_default_control = 'index';
	
	public $_default_action = 'index';
}