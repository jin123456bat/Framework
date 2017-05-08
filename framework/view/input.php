<?php
namespace framework\view;
class input extends dom
{
	const INPUT_TEXT = 'text';
	
	const INPUT_CHECKBOX = 'checkbox';
	
	const INPUT_PASSWORD = 'password';
	
	const INPUT_RADIO = 'radio';
	
	protected $_needClose = false;
	
	function __construct($type,$name,$attributes = array())
	{
		$this->prop('type',$type);
		$this->prop('name',$name);
		$this->prop($attributes);
	}
}