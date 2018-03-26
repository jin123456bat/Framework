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

	function insert($data)
	{
		$this->_temp['do'] = 'insert';
	}

	function delete()
	{
		$this->_temp['do'] = 'delete';
	}

	function update()
	{
		$this->_temp['do'] = 'update';
	}
}