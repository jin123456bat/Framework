<?php
namespace framework\core;

use framework\lib\data;
use framework\core\database\mysql\field;

class entity extends data
{

	/**
	 * 删除之后执行的函数
	 */
	function __afterRemove()
	{
	}

	/**
	 * 删除之前执行的函数
	 * 这个函数可以返回false,
	 * 当返回false的时候则中断删除操作，并且remove函数返回false
	 * 返回值要么是false要么是其他，0或NULL不会认为是false
	 */
	function __preRemove()
	{
	}

	/**
	 * 保存之前执行的函数
	 * 这个函数可以返回false,
	 * 当返回false的时候则中断删除操作，并且save函数假如是update的时候返回false
	 * 返回值要么是false要么是其他，0或NULL不会认为是false
	 */
	function __preUpdate()
	{
	}

	/**
	 * 保存之后执行的函数
	 */
	function __afterUpdate()
	{
	}

	/**
	 * 执行插入之前执行的函数
	 * 这个函数可以返回false,
	 * 当返回false的时候则中断删除操作，并且save函数假如是insert的时候返回false
	 * 返回值要么是false要么是其他，0或NULL不会认为是false
	 */
	function __preInsert()
	{
	}

	/**
	 * 插入之后执行的函数
	 */
	function __afterInsert()
	{
	}

	/**
	 * 默认的情况下使用ID字段作为主键
	 * 
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
		if ($this->__preRemove() === false)
		{
			return false;
		}
		$result = $this->model($model)
			->where($pk . '=?', array(
			$this->$pk
		))
			->delete();
		$this->__afterRemove();
		return $result;
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
			if (! empty($relation))
			{
				$relation_data[$key] = $relation;
				unset($data[$key]);
			}
		}
		
		if (! empty($pk) && ! empty($this->$pk))
		{
			if (false === $this->__preUpdate())
			{
				return false;
			}
			$this->model($model)->transaction();
			
			$this->model($model)
				->where($pk . '=?', array(
				$this->$pk
			))
				->limit(1)
				->update($data);
			
			// 更新关联数据 更新关系数据必须在update后执行 防止外键索引导致添加失败
			foreach ($relation_data as $data)
			{
				foreach ($data as $tableName => $d_data)
				{
					// 先删除数据
					if (isset($d_data['delete']) && ! empty($d_data['delete']) && is_array($d_data['delete']))
					{
						foreach ($d_data['delete'] as $key => $value)
						{
							$this->model($model)->where($key . '=?', [
								$value
							]);
						}
						$this->model($model)->delete();
					}
					// 在添加关系
					if (isset($d_data['insert']) && ! empty($d_data['insert']) && is_array($d_data['insert']))
					{
						foreach ($d_data['insert'] as $insert)
						{
							if (! $this->model($tableName)->insert($insert))
							{
								$this->model($model)->rollback();
								return false;
							}
						}
					}
				}
			}
			
			$this->model($model)->commit();
			$this->__afterUpdate();
			return true;
		}
		else
		{
			if ($this->__preInsert() === false)
			{
				return false;
			}
			
			$this->model($model)->transaction();
			if ($this->model($model)->insert($data))
			{
				$this->$pk = $this->model($model)->lastInsertId();
				// 这里把其它的相关数据插入进去
				foreach ($relation_data as $key => $data)
				{
					foreach ($data as $tableName => $d_data)
					{
						if (isset($d_data['insert']) && ! empty($d_data['insert']) && is_array($d_data['insert']))
						{
							foreach ($d_data['insert'] as $insert)
							{
								if (! $this->model($tableName)->insert($insert))
								{
									$this->model($model)->rollback();
									return false;
								}
							}
						}
					}
				}
				$this->model($model)->commit();
				$this->__preInsert();
				return true;
			}
			$this->model($model)->rollback();
			return false;
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
	 * @param unknown $primaryKey
	 *        主键
	 * @param unknown $data
	 *        添加或者删除的时候的相关数据
	 * @example return array(
	 *          'field' => array(
	 *          'tableName' => array(
	 *          'insert' => array(
	 *          array(),
	 *          array(),
	 *          ),
	 *          'delete' => array(
	 *          'field' => $primaryKey,
	 *          ),
	 *          ),
	 *          )
	 *          );
	 */
	function __relation($field, $primaryKey, $data)
	{
		return array();
	}

	/**
	 * 错误消息中的变量替换
	 * 
	 * @param unknown $string        
	 * @param unknown $field        
	 * @param unknown $value        
	 * @return mixed
	 */
	private function message($string, $field, $value)
	{
		return preg_replace(array(
			'/{field}/',
			'/{value}/'
		), array(
			$field,
			$value
		), $string);
	}

	/**
	 * 转化自定义rule
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
					if (isset($rule['message']))
					{
						$return = array_combine($keys, array_fill(0, count($keys), array(
							'message' => $rule['message']
						)));
					}
				}
				else if (is_array($rule['fields']))
				{
					$keys = $rule['fields'];
					if (array_key_exists(0, $rule['fields']))
					{
						$return = array_combine(array_values($rule['fields']), array_fill(0, count($rule['fields']), array(
							'message' => ''
						)));
					}
					else
					{
						$return = array();
						foreach ($rule['fields'] as $key => $value)
						{
							if (is_array($value))
							{
								$data = array(
									'message' => ''
								);
								if (isset($value['message']))
								{
									$data['message'] = $value['message'];
								}
								if (isset($value['on']))
								{
									$data['on'] = $value['on'];
								}
								if (isset($value['render']))
								{
									$data['render'] = $value['render'];
								}
								if (isset($value['data']))
								{
									$data['data'] = $value['data'];
								}
								if (isset($value['callback']))
								{
									$data['callback'] = $value['callback'];
								}
								$return[$key] = $data;
							}
							else if (is_string($value))
							{
								$return[$key] = array(
									'message' => $value
								);
							}
						}
					}
				}
				
				foreach ($return as $key => &$value)
				{
					if (isset($rule['data']) && ! isset($value['data']))
					{
						$value['data'] = $rule['data'];
					}
					
					if (isset($rule['on']) && ! isset($value['on']))
					{
						$value['on'] = $rule['on'];
					}
					
					if (isset($rule['render']) && ! isset($value['render']))
					{
						$value['render'] = $rule['render'];
					}
					
					if (isset($rule['message']) && empty($value['message']))
					{
						$value['message'] = $rule['message'];
					}
					
					if (isset($rule['callback']) && ! isset($value['callback']))
					{
						$value['callback'] = $rule['callback'];
					}
				}
				
				return $return;
			}
			else
			{
				$return = array();
				foreach ($rule as $key => $value)
				{
					if (is_array($value))
					{
						$data = array(
							'message' => ''
						);
						if (isset($value['message']))
						{
							$data['message'] = $value['message'];
						}
						if (isset($value['on']))
						{
							$data['on'] = $value['on'];
						}
						if (isset($value['render']))
						{
							$data['render'] = $value['render'];
						}
						if (isset($value['data']))
						{
							$data['data'] = $value['data'];
						}
						if (isset($value['callback']))
						{
							$data['callback'] = $value['callback'];
						}
						$return[$key] = $data;
					}
					else if (is_string($value))
					{
						$return[$key] = array(
							'message' => $value
						);
					}
				}
				return $return;
			}
		}
	}

	/**
	 * 根据某一个字段找一行内容
	 * 
	 * @param unknown $field
	 *        字段名
	 * @param unknown $value
	 *        字段值
	 * @return unknown array
	 */
	function findByFiled($field, $value)
	{
		$model = $this->__model();
		return $this->model($model)
			->where($field . '=?', array(
			$value
		))
			->limit(1)
			->find();
	}

	/**
	 * 验证数据集合是否符合规则
	 * 
	 * @param string $sence
	 *        场景名称 默认为空不使用场景名称
	 */
	function validate($sence = '')
	{
		$rules = $this->__rules();
		foreach ($rules as $key => $rule)
		{
			switch ($key)
			{
				// 大于等于
				case '>=':
					$key = 'ge';
				break;
				// 小于等于
				case '<=':
					$key = 'le';
				break;
				// 不等于
				case '!=':
					$key = 'ne';
				break;
				// 小于
				case '<':
					$key = 'lt';
				break;
				// 大于
				case '>':
					$key = 'gt';
				break;
				case '=':
					$key = 'eq';
				break;
			}
			
			if (! empty($key))
			{
				$validator = application::load('validator');
				if (is_callable(array(
					$validator,
					$key
				)) || in_array($key, array(
					'unique',
					'function'
				)))
				{
					// 获取数据
					$renderData = $this->render($rule);
					foreach ($renderData as $field => $val)
					{
						// 情景判断
						if (! empty($sence))
						{
							if (isset($val['on']) && ! empty($val['on']))
							{
								$break = true;
								if (is_array($val['on']) && in_array($sence, $val['on']))
								{
									$break = false;
								}
								else if (is_scalar($val['on']) && in_array($sence, explode(',', $val['on'])))
								{
									$break = false;
								}
								if ($break)
								{
									continue;
								}
							}
						}
						
						$value = isset($this->_data[$field]) ? $this->_data[$field] : NULL;
						
						if (isset($val['render']) && is_callable($val['render']))
						{
							$value = call_user_func($val['render'], $value);
						}
						
						// 其他的参数
						$data = isset($val['data']) ? $val['data'] : NULL;
						// 等于不等于 支持2个字段之间的对比
						// if (in_array($key, array('ge','le','ne','lt','gt','eq')))
						// {
						if (is_string($data) && $data[0] == '@')
						{
							if (isset($this->_data[substr($data, 1)]))
							{
								$data = $this->_data[substr($data, 1)];
							}
						}
						// }
						
						if ($key == 'unique')
						{
							if (! empty($this->findByFiled($field, $value)))
							{
								return false;
							}
						}
						else if ($key == 'function' && ! empty($val['callback']))
						{
							$callback = $val['callback'];
							if (! call_user_func($callback, $value, $data))
							{
								// 假如失败的错误消息
								$message = $this->message($val['message'], $field, $value);
								$this->addError($field, $message);
							}
						}
						else
						{
							if (! call_user_func(array(
								$validator,
								$key
							), $value, $data))
							{
								// 假如失败的错误消息
								$message = $this->message($val['message'], $field, $value);
								$this->addError($field, $message);
							}
						}
					}
				}
				else
				{
					trigger_error('错误的校验器');
				}
			}
		}
		
		if ($this->hasError())
		{
			return false;
		}
		return true;
	}
}
