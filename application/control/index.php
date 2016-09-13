<?php
namespace application\control;
use framework\core\control;
use framework\core\response;
use framework\core\database\sql;
class index extends control
{
	function index()
	{
		/* $result = $this->model('access')->where('sn = :sn',array('sn'=>'1'))->scalar(array(
			'sn','disable'
		));
		
		$result = $this->model('access')->select(array(
			'disable','sn'
		));
 */		
		/* 
		 * $disable = new sql();
		 * $disable->from('access')->where('sn=1')->select('2,disable');
		 */
		/* var_dump($this->model('access')->ignore()->insert(array(
			'sn' => '1',
			'disable' => "1",
		)));  */
		
		//var_dump($this->model('access')->ignore()->insert($disable));
		
		/* var_dump($this->model('access')->debug_trace_sql()); */
		
		/* $this->model('access')->update('sn',2); */
		
		return new response("123");
	}
}