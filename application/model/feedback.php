<?php
namespace application\model;
use framework\core\model;

class feedback extends model
{
	function __construct($table)
	{
		parent::__construct($table);
	}
	
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['_temp_cds_v2'];
	}
	
	function __tableName()
	{
		return '_feedback_history';
	}
}