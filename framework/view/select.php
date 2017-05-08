<?php
namespace framework\view;

class select extends dom
{
	function __construct($string,$name = '',$attribute = array())
	{
		parent::__construct($string);
		$this->prop('name',$name);
		$this->prop($attribute);
	}
}