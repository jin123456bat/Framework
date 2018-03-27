<?php
namespace framework\core\database\sphinx;

class sql extends \framework\core\database\sql
{
	/**
	 * 临时信息
	 * @var array
	 */
	private $_temp = array(
		'do' => '',
		'select_fields' => array(
			
		),
	);
	
	function select($fields = '*')
	{
		$this->_temp['do'] = 'select';
		if (empty($fields))
		{
			$this->_temp['select_fields'] = array('*');
		}
		else if (is_string($fields))
		{
			$this->_temp['select_fields'] = explode(',', $fields);
		}
		else if (is_array($fields))
		{
			$this->_temp['select_fields'] = $fields;
		}
	}

	function insert($data = array())
	{
		$this->_temp['do'] = 'insert';
		$this->_temp['insert_data'] = $data;
	}

	function delete()
	{
		$this->_temp['do'] = 'delete';
	}

	function update($name,$value = NULL)
	{
		$this->_temp['do'] = 'update';
		
		if (is_scalar($name))
		{
			$this->_temp['update_data'] = array(
				$name => $value
			);
		}
		else if (is_array($name) && !array_key_exists(0, $name))
		{
			$this->_temp['update_data'] = $name;
		}
	}
	
	function where($condition,$params = array())
	{
		
	}
	
	function from($table)
	{
		$this->_temp['table'] = $table;
	}
	
	function __toString()
	{
		switch ($this->_temp['do'])
		{
			case 'insert':
				break;
			case 'delete':
				break;
			case 'select':
				break;
			case 'update':
				break;
		}
	}
}