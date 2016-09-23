<?php
namespace framework\lib;

use framework\lib\error;

class data extends error implements \ArrayAccess
{
	protected $_scene = '';
	
	private $_safe_fileds = array();
	
	function __construct($data = array(),$scene = '')
	{
		$this->_scene = $scene;
		foreach ($data as $name => $value)
		{
			$this->$name = $value;
		}
	}
	
	function initlize()
	{
		parent::initlize();
	}
	
	/**
	 * 默认的delete
	 */
	function remove()
	{
		$pk = $this->__primaryKey();
		$model = $this->__model();
		return $this->model($model)->where($pk.'=?',array($this->$pk))->delete();
	}
	
	/**
	 * 默认的save
	 */
	function save()
	{
		$pk = $this->__primaryKey();
		$model = $this->__model();
		$data = array();
		foreach ($this as $index => $value)
		{
			if ($index !== $pk)
			{
				$data[$index] = $value;
			}
		}
		if (!empty($pk) && !empty($this->$pk))
		{
			$this->model($model)->where($pk.'=?',array($this->$pk))->update($data);
		}
		else
		{
			$this->model($model)->insert($data);
		}
	}
	
	/**
	 * 验证数据集合是否符合规则
	 */
	function validate()
	{
		$rules = $this->__rules();
		
		foreach ($rules as $index => $rule)
		{
			if (!isset($rule['on']) || (!empty($this->_scene) && $this->_scene == $rule['on']))
			{
				if (isset($rule['safe']) && !empty($rule['safe']))
				{
					$this->_safe_fileds = $this->parseFileds($rule['safe']);
				}
			}
			else
			{
				unset($rules[$index]);
			}
		}
		
		foreach ($rules as $rule)
		{
			$do = '';
			$fields = array();
			$message = '';
			
			if (isset($rule['required']) && !empty($rule['required']))
			{
				$fields = $this->parseFileds($rule['required']);
				$do = 'required';
			}
			
			if (isset($rule['unique']) && !empty($rule['unique']))
			{
				$fields = $this->parseFileds($rule['unique']);
				$do = 'unique';
			}
			
			$rule['message'] = isset($rule['message'])?$rule['message']:'';
			
			switch ($do)
			{
				case 'required':
					foreach ($fields as $index => $value)
					{
						if (!in_array($value, $this->_safe_fileds) && (!isset($this->$value) || $this->isEmpty($this->$value)))
						{
							$message = str_replace('{field}', $value, $rule['message']);
							$this->addError('000100', $message);
						}
					}
					break;
				case 'unique':
					$model = $this->model($this->__model());
					foreach ($fields as $index => $value)
					{
						if(!in_array($value, $this->_safe_fileds))
						{
							$result = $model->where($value.'=?',array($this->$value))->find();
							if(!empty($result))
							{
								$message = str_replace('{field}', $value, $rule['message']);
								$this->addError('000100', $message);
							}
						}
					}
					break;
					
			}
			
		}
		if ($this->hasError())
		{
			return false;
		}
		return true;
	}
	
	/**
	 * 判断变量是否为空
	 * @param unknown $value
	 */
	private function isEmpty($value)
	{
		if (is_array($value))
		{
			foreach ($value as $val)
			{
				if (!$this->isEmpty($val))
				{
					return false;
				}
			}
			return true;
		}
		else if (is_string($value) && strlen($value)!==0)
		{
			return false;
		}
		else if (is_int($value) && $value!==0)
		{
			return false;
		}
		else if (is_float($value) && $value!=0)
		{
			return false;
		}
		return true;
	}
	
	private function parseFileds($string)
	{
		if (is_string($string))
		{
			return explode(',', $string);
		}
		if (is_array($string))
		{
			return $string;
		}
	}
	
	/**
	 * @example
	 * [
	 * 		[
	 * 			'required' => 'a,b,c,d...',
	 * 			'message' => 'this field can't be empty'
	 * 		],
	 * 		[
	 * 			'string' => 'e,f,g..'
	 * 			'maxlength' => 100,
	 * 			'minlength' => 10,
	 * 			'message' => ''
	 * 		],
	 * 		[
	 * 			'int' => 'x,y,z',
	 * 			'max' => 10,
	 * 			'min' => 0,
	 * 			'message' => '{field}'
	 * 		],
	 * 		[
	 * 			'number' => 'h,j',
	 * 			'message'=> ''
	 * 		]
	 * ]
	 */
	function __rules()
	{
		return array();
	}
	
	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetExists()
	 */
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetGet()
	 */
	public function offsetGet($offset)
	{
		return isset($this->$offset)?$this->$offset:NULL;
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetSet()
	 */
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}

	/**
	 * {@inheritDoc}
	 * @see ArrayAccess::offsetUnset()
	 */
	public function offsetUnset($offset)
	{
		unset($this->$offset);
	}	
}