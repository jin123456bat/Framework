<?php
namespace framework\vendor;

use framework\core\component;
use framework\core\model;

/**
 * 分页的类
 * 支持model和数组
 * 
 * @author fx
 */
class paginate extends component
{

	/**
	 *
	 * @var model
	 */
	private $_model;

	/**
	 * 存储原始的model
	 * 
	 * @var model
	 */
	private $_clone;

	/**
	 * 数组形式的原始数据
	 * 
	 * @var array
	 */
	private $_data;

	private $_start;

	private $_length;

	/**
	 * 当使用数组分页的时候存储当前页的结果
	 * 
	 * @var array
	 */
	private $_result;

	function __construct($table)
	{
		if (is_scalar($table))
		{
			$this->_model = $this->model($table);
			$this->_clone = clone $this->_model;
		}
		else if (is_array($table))
		{
			$this->_data = $table;
		}
		else if ($table instanceof model)
		{
			$this->_model = $table;
			$this->_clone = clone $table;
		}
	}

	/**
	 * 设定分页的start和length
	 * 
	 * @param int $start        
	 * @param int $length        
	 */
	function limit($start, $length)
	{
		if ($start < 0)
		{
			$start = 0;
		}
		if ($length < 0)
		{
			$length = 0;
		}
		
		$this->_start = $start;
		$this->_length = $length;
		
		if (! empty($this->_model))
		{
			$this->_model->limit($start, $length);
		}
		else if (! empty($this->_data))
		{
			$this->_result = array_slice($this->_data, $start, $length);
		}
	}

	/**
	 * 提供了一个方法 可以不同过limit来确定分页 也可以通过page和pagesize来确定分页
	 * 
	 * @param int $page        
	 * @param int $size        
	 */
	function page($page, $size)
	{
		$this->limit(($page - 1) * $size, $size);
	}

	/**
	 * 获取数据
	 * 返回要获取的数据
	 * 
	 * @return array
	 */
	function fetch()
	{
		if (! empty($this->_model))
		{
			return $this->_model->select();
		}
		else
		{
			return $this->_result;
		}
	}

	/**
	 * 获取数据的实际长度
	 * 
	 * @return int
	 */
	function length()
	{
		if (! empty($this->_model))
		{
			return $this->_model->count();
		}
		else
		{
			return count($this->_result);
		}
	}

	/**
	 * 获取数据的总长度
	 * 
	 * @return int
	 */
	function total()
	{
		if (! empty($this->_model))
		{
			return $this->_clone->count();
		}
		else
		{
			return count($this->_data);
		}
	}

	/**
	 * 获取总共可以分几页
	 * 
	 * @param int $size
	 *        $size不能小于0
	 * @return int 总页数
	 */
	function pagesize($size)
	{
		if ($size <= 0)
		{
			return $size;
		}
		return ceil($this->total() / $size);
	}

	/**
	 * 当前页数
	 * 必须在调用limit或者page方法之后有效
	 * 
	 * @return int 当前页码
	 */
	function current()
	{
		return intval($this->_start / $this->_length) + 1;
	}
}