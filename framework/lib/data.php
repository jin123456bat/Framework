<?php
namespace framework\lib;

use framework\lib\error;

class data extends error implements \ArrayAccess
{
	function __construct($data = array())
	{
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
	 * 验证数据集合是否符合规则
	 */
	function validate()
	{
		$rules = $this->__rules();
		foreach ($rules as $rule)
		{
			$do = '';
			$fields = array();
			$message = '';
			
			if (isset($rule['required']) && !empty($rule['required']))
			{
				if (is_string($rule['required']))
				{
					$fields = explode(',', $rule['required']);
				}
				else if (is_array($rule['required']))
				{
					$fields = $rule['required'];
				}
				$do = 'required';
			}
			
			if (isset($rule['unique']) && !empty($rule['unique']))
			{
				if (is_string($rule['unique']))
				{
					$fields = explode(',', $rule['unique']);
				}
				else if (is_array($rule['unique']))
				{
					$fields = $rule['unique'];
				}
				$do = 'unique';
			}
			
			$rule['message'] = isset($rule['message'])?$rule['message']:'';
			
			switch ($do)
			{
				case 'required':
					foreach ($fields as $index => $value)
					{
						if (!isset($this->$value) || $this->isEmpty($this->$value))
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
						$result = $model->where($value.'=?',array($this->$value))->find();
						if(!empty($result))
						{
							$message = str_replace('{field}', $value, $rule['message']);
							$this->addError('000100', $message);
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