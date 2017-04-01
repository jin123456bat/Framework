<?php
namespace framework\lib;

use framework\core\base;

class error extends base
{

	private $_error = array();

	private $_has_error = false;

	/**
	 * 是否有错误
	 *
	 * @return boolean
	 */
	function hasError()
	{
		return $this->_has_error;
	}

	/**
	 * 添加错误信息
	 *
	 * @param string $code
	 *        	错误代码
	 * @param string $message
	 *        	错误信息
	 */
	function addError($code, $message)
	{
		$this->_has_error = true;
		$this->_error[] = array(
			'code' => $code,
			'message' => $message
		);
	}

	/**
	 * 删除错误信息
	 *
	 * @param string $code
	 *        	错误代码
	 */
	function delError($code)
	{
		foreach ($this->_error as $index => $error)
		{
			if ($error['code'] == $code)
			{
				$this->_error = array_slice($this->_error, $index, 1);
				break;
			}
		}
		if (empty($this->_error))
		{
			$this->_has_error = false;
		}
	}

	/**
	 * 清除所有错误信息
	 */
	function clearError()
	{
		$this->_error = array();
		$this->_has_error = false;
	}

	/**
	 * 获取错误信息
	 *
	 * @return [['code','message']]
	 */
	function getError()
	{
		return $this->_error;
	}
}
