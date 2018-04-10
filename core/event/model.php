<?php
namespace framework\core\event;
use framework\core\event;

interface model extends event
{
	function create($tableName,array $data);
	
	function delete($tableName,$where);
	
	function update($tableName,$key,$value);
	
	function select($tableName,$data);
}