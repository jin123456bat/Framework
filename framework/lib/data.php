<?php
namespace framework\lib;

use framework\lib\error;
use framework;
use framework\core\application;

class data extends error implements \ArrayAccess
{
	/**
	 * 原始数据存储
	 * @var array
	 */
	private $_data = array();
	
	/**
	 * 场景
	 * @var string
	 */
	protected $_scene = '';

	/**
	 * validate的时候,不验证的字段
	 * @var array
	 */
	private $_safe_fileds = array();

	function __construct($data = array(), $scene = '')
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
	 * 默认的情况下使用ID字段作为主键
	 * @return string
	 */
	function __primaryKey()
	{
		return 'id';
	}
	
	/**
	 * 数据模型名称
	 */
	function __model()
	{
		return get_class($this);
	}

	/**
	 * 默认的delete
	 */
	function remove()
	{
		$pk = $this->__primaryKey();
		$model = $this->__model();
		return $this->model($model)
			->where($pk . '=?', array(
			$this->$pk
		))->delete();
	}
	
	/**
	 * 根据某一个字段找一行内容
	 * @param unknown $field 字段名
	 * @param unknown $value 字段值
	 * @return unknown array
	 */
	function findByFiled($field,$value)
	{
		$model = $this->__model();
		return $this->model($model)->where($field.'=?',array($value))->limit(1)->find();
	}

	/**
	 * 默认的save
	 */
	function save()
	{
		$pk = $this->__primaryKey();
		$model = $this->__model();
		
		$data = $this->_data;
		
		$relation_data = array();
		foreach ($data as $key => $value)
		{
			$relation = $this->__relation($key, $this->pk, $value);
			if (!empty($relation))
			{
				$relation_data[$key] = $relation;
				unset($data[$key]);
			}
		}
		
		if (! empty($pk) && ! empty($this->$pk))
		{
			$this->model($model)->transaction();
			
			$this->model($model)
				->where($pk . '=?', array(
				$this->$pk
			))->limit(1)->update($data);
			
			//更新关联数据  更新关系数据必须在update后执行 防止外键索引导致添加失败
			foreach ($relation_data as $data)
			{
				foreach ($data as $tableName => $d_data)
				{
					//先删除数据
					if (isset($d_data['delete']) && !empty($d_data['delete']) && is_array($d_data['delete']))
					{
						foreach ($d_data['delete'] as $key => $value)
						{
							$this->model($model)->where($key.'=?',[$value]);
						}
						$this->model($model)->delete();
					}
					//在添加关系
					if (isset($d_data['insert']) && !empty($d_data['insert']) && is_array($d_data['insert']))
					{
						foreach ($d_data['insert'] as $insert)
						{
							if(!$this->model($tableName)->insert($insert))
							{
								$this->model($model)->rollback();
								return false;
							}
						}
					}
				}
			}
			
			$this->model($model)->commit();
			return true;
		}
		else
		{
			$this->model($model)->transaction();
			if ($this->model($model)->insert($data))
			{
				$this->$pk = $this->model($model)->lastInsertId();
				//这里把其它的相关数据插入进去
				foreach ($relation_data as $key => $data)
				{
					foreach ($data as $tableName => $d_data)
					{
						if (isset($d_data['insert']) && !empty($d_data['insert']) && is_array($d_data['insert']))
						{
							foreach ($d_data['insert'] as $insert)
							{
								if(!$this->model($tableName)->insert($insert))
								{
									$this->model($model)->rollback();
									return false;
								}
							}
						}
					}
				}
				$this->model($model)->commit();
				return true;
			}
			$this->model($model)->rollback();
			return false;
		}
	}

	/**
	 * 验证数据集合是否符合规则
	 * @param string $sence 场景名称 默认为空不使用场景名称
	 */
	function validate($sence = '')
	{
		$rules = $this->__rules();
		foreach ($rules as $key => $rule)
		{
			switch ($key)
			{
				//不能存在
				case 'unsafe':
					$key = '';
					break;
				//大于等于
				case '>=':
					$key = 'ge';
					break;
					//小于等于
				case '<=':
					$key = 'le';
					break;
					//不等于
				case '!=':
					$key = 'ne';
					break;
					//小于
				case '<':
					$key = 'lt';
					break;
					//大于
				case '>':
					$key = 'gt';
					break;
					//等于  这个同时也要求可以验证2个字段的值
				case '=':
					$key = 'eq';
					break;
					//必须是整数，可以是负数
				case 'int':
					break;
					//可以是整数或者小数 可以是负数
				case 'decimal':
					break;
					//唯一
				case 'unique':
					break;
					//必须是手机号码
				case 'telephone':
					break;
					//必须是email
				case 'email':
					break;
					//必须是url 仅支持http或者https开头  假如没有协议名按照http来处理
				case 'url':
					break;
					//必须是IP地址  或者ip地址段
				case 'ip':
					break;
					//必须在多个中的一个
				case 'enum':
					break;
					//必须是日期时间 要求格式和有效性同时可以验证，要求可以自定义格式验证
				case 'datetime':
					break;
					//对自定义函数的支持
				case 'function':
					$key = '';
					break;
			}
			
			if (!empty($key))
			{
				$validator = application::load('validator');
				if (is_callable(array(
					$validator,
					$key
				)))
				{
					$render = $this->render($rule);
					
					$message = $this->message($rule);
					$data = $this->data($rule);
					
					call_user_func(array($validator,$key),$render,$data);
				}
			}
		}
		
		
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
			if (! isset($rule['on']) || (! empty($this->_scene) && in_array($this->_scene, $on, true)))
			{
				if (isset($rule['safe']) && ! empty($rule['safe']))
				{
					$this->_safe_fileds = array_merge($this->_safe_fileds, $this->parseFileds($rule['safe']));
				}
			}
			else
			{
				unset($rules[$index]);
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
	private function render($rule)
	{
		if (is_array($rule))
		{
			if (isset($rule['fields']))
			{
				if (is_string($rule['fields']))
				{
					$keys = explode(',', $rule['fields']);
				}
				else if (is_array($rule['fields'])) 
				{
					
				}
			}
			else
			{
				$keys = array_keys($rule);
			}
		}
	}

	/**
	 * 获取错误信息
	 *
	 * @param unknown $rule        	
	 */
	private function message($rule, $value)
	{
		if (isset($rule['message']))
		{
			$replacer = '{field}';
			return str_replace($replacer, $value, $rule['message']);
		}
		return '';
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
	 *
	 * @example 这里重写
	 */
	function __rules()
	{
		return array();
	}
	
	/**
	 * 
	 * @param unknown $primaryKey 主键
	 * @param unknown $data 添加或者删除的时候的相关数据
	 * @example
	 * return array(
	 *		'field' => array(
	 *			'tableName' => array(
	 *				'insert' => array(
	 *					array(),
	 *					array(),
	 *				),
	 *				'delete' => array(
	 *					'field' => $primaryKey,
	 *				),
	 *			),
	 *		)
	 *	);
	 */
	function __relation($field,$primaryKey,$data)
	{
		return array();
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
