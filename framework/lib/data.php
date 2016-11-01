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
			return $this->model($model)->where($pk.'=?',array($this->$pk))->update($data);
		}
		else
		{
			if($this->model($model)->insert($data))
			{
				$this->$pk = $this->model($model)->lastInsertId();
				return true;
			}
			return false;
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
			if (isset($rule['on']))
			{
				if (is_string($rule['on']))
				{
					$on = explode(',', $rule['on']);
				}
				else 
				{
					$on = $rule['on'];
				}
			}
			if (!isset($rule['on']) || (!empty($this->_scene) && in_array($this->_scene,$on,true)))
			{
				if (isset($rule['safe']) && !empty($rule['safe']))
				{
					$this->_safe_fileds = array_merge($this->_safe_fileds,$this->parseFileds($rule['safe']));
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
			if (isset($rule['lt']) && !empty($rule['lt']))
			{
				$fields = $this->parseFileds($rule['lt']);
				$do = 'compare';
				$method = '<';
			}
			if (isset($rule['gt']) && !empty($rule['gt']))
			{
				$fields = $this->parseFileds($rule['gt']);
				$do = 'compare';
				$method = '>';
			}
			if (isset($rule['ge']) && !empty($rule['ge']))
			{
				$fields = $this->parseFileds($rule['ge']);
				$do = 'compare';
				$method = '>=';
			}
			if (isset($rule['validate']) && !empty($rule['validate']))
			{
				$fields = $this->parseFileds($rule['fileds']);
				$do = 'validate';
			}
			if (isset($rule['enum']) && !empty($rule['enum']))
			{
				$fields = $this->parseFileds($rule['enum']);
				$do = 'enum';
			}
			if (isset($rule['email']) && !empty($rule['email']))
			{
				$fileds = $this->parseFileds($rule['email']);
				$do = 'email';
			}
			
			$rule['message'] = isset($rule['message'])?$rule['message']:'';
			
			switch ($do)
			{
				case 'email':
					$pattern = '$(\w+@\w+\.\w+)(\.[a-zA-Z]{2})?$';
					foreach ($fields as $index=>$value)
					{
						if (!preg_match($pattern, $this->$value))
						{
							$this->addError('000100', $this->message($rule, $value));
						}
					}
					break;
				case 'enum':
					$values = is_array($rule['values'])?$rule['values']:explode(',', $rule['values']);
					foreach ($fields as $index=>$value)
					{
						if (!in_array($this->$value, $values) || !isset($this->$value))
						{
							$this->addError('000100', $this->message($rule, $value));
						}
					}
					break;
				case 'required':
					foreach ($fields as $index => $value)
					{
						if (!in_array($value, $this->_safe_fileds) && (!isset($this->$value) || $this->isEmpty($this->$value)))
						{
							$this->addError('000100', $this->message($rule, $value));
						}
					}
					break;
				case 'unique':
					$model = $this->model($this->__model());
					foreach ($fields as $index => $value)
					{
						if(!in_array($value, $this->_safe_fileds))
						{
							$tempValue = $this->render($rule, $value);
							$result = $model->where($value.'=?',array($tempValue))->find();
							if(!empty($result))
							{
								$this->addError('000100', $this->message($rule, $value));
							}
						}
					}
					break;
				case 'compare':
					$firstValue = $fields[0];
					$secondValue = $fields[1];
					if (!(in_array($firstValue, $this->_safe_fileds) && in_array($firstValue, $this->_safe_fileds)))
					{
						$firstTempValue = is_numeric($firstValue)?$firstValue:$this->render($rule, $firstValue);
						$secondTempValue = is_numeric($secondValue)?$secondValue:$this->render($rule, $secondValue);
						switch ($method)
						{
							case '<':
								if ($firstTempValue >= $secondTempValue)
								{
									$this->addError('000100', $this->message($rule, $firstValue));
								}
							break;
							case '>':
								if ($firstTempValue <= $secondTempValue)
								{
									$this->addError('000100', $this->message($rule, $firstValue));
								}
							break;
							case '>=':
								if ($firstTempValue < $secondTempValue)
								{
									$this->addError('000100', $this->message($rule, $firstValue));
								}
							break;
						}
					}
					break;
				case 'validate':
					foreach ($fields as $index => $value)
					{
						if (is_callable($rule['validate']))
						{
							if(!call_user_func($rule['validate'],$this->render($rule, $value)))
							{
								$this->addError('000100', $this->message($rule, $value));
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
	 * 判断是否有render，并且进行一次render
	 */
	private function render($rule,$value)
	{
		if (isset($rule['render']) && is_callable($rule['render']))
		{
			return call_user_func($rule['render'],$this->$value);
		}
		return $this->$value;
	}
	
	/**
	 * 获取错误信息
	 * @param unknown $rule
	 */
	private function message($rule,$value)
	{
		if (isset($rule['message']))
		{
			$replacer = '{field}';
			return str_replace($replacer, $value, $rule['message']);
		}
		return '';
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