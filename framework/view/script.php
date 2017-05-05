<?php
namespace framework\view;

class script extends dom
{
	private $_type = 'text/javascript';
	
	private $_charset = 'utf-8';
	
	private $_loading = '';
	
	protected $_needClose = true;
	
	const LOADING_ASYNC = 'async';
	
	const LOADING_DEFER = 'defer';
	
	const LOADING_NONE = '';
	
	function __construct($path,$attributes = array(),$loading = self::LOADING_NONE)
	{
		parent::__construct('',array(
			'type' => $this->_type,
			'charset' => $this->_charset,
		));
		$this->prop('src', $path);
		$this->_loading = $loading;
		$this->prop($attributes);
	}
	
	/**
	 * 设置script加载方式 async或者defer 只能设置为这2种
	 * @param LOADING_* $loading
	 */
	function loading($loading)
	{
		if (in_array($loading, array(
			self::LOADING_ASYNC,self::LOADING_DEFER,self::LOADING_NONE
		),true))
		{
			$this->_loading = $loading;
		}
	}
	
	function __toString()
	{
		$this->prop($this->_loading,$this->_loading);
		return parent::__toString();
	}
}