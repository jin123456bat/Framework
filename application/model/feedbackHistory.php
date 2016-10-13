<?php
namespace application\model;
use \application\extend\model;

class feedbackHistory extends model
{
	function __construct($table)
	{
		parent::__construct($table);
	}
	
	function __config()
	{
		$db = $this->getConfig('db');
		return $db['cloud_web_v2'];
	}
	
	function __tableName()
	{
		return '_feedback_history';
	}
}