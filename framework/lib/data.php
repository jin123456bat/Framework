<?php
namespace framework\lib;

use framework\lib\error;
use framework;

class data extends error implements \ArrayAccess
{
	/**
	 * 原始数据存储
	 * @var array
	 */
	protected $_data = array();
	
	function __construct($data = array())
	{
		$this->_data = $data;
	}

	function initlize()
	{
		parent::initlize();
	}
	
	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->_data[$offset]);
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		$this->_data[$offset] = $value;
	}

	/**
	 *
	 * {@inheritdoc}
	 *
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		unset($this->_data[$offset]);
	}
}
