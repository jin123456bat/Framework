<?php
namespace framework\view;
class link extends dom
{
	protected $_needClose = false;
	
	private $_rel = 'stylesheet';
	
	private $_type = 'text/css';
	
	private $_media = 'screen';
	
	function __construct($path,$attributes = array())
	{
		parent::__construct('',array(
			'rel' => $this->_rel,
			'type' => $this->_type,
			'media' => $this->_media,
		));
		$this->prop('href', $path);
		$this->prop($attributes);
	}
}