<?php
namespace framework\lib;

use framework\lib\error;

abstract class data extends error implements \ArrayAccess
{
	function __construct()
	{
		
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
		$rules = $this->rules();
		foreach ($rules as $rule)
		{
			$do = '';
			$fields = [];
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
			
			switch ($do)
			{
				case 'required':
					foreach ($fields as $index => $value)
					{
						if (!isset($this->$value))
						{
							$message = str_replace('{field}', $value, $rule['message']);
							$this->addError('000100', $message);
						}
					}
			}
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
	function rules()
	{
		return [];
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