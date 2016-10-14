<?php
namespace application\control;
use framework\core\control;
use framework\data\collection;
class index extends control
{
	private $_start_time = '2016-08-01 00:00:00';
	
	private $_end_time = '2016-10-01 00:00:00';
	
	private $_sn = array(
		'CAS0530000002',
		'CAS0530000003',
		'CAS0530000004',
		'CAS0530000005',
		'CAS0530000006',
		'CAS0530000007',
		'CAS0530000008',
		'CAS0530000009',
	);
	
	function index()
	{
		
		$data = array(
			'A',
			'B',
			'C',
			'D',
			'B',
		);
		$collection = new collection($data);
		$collection->unique();
		
		//ok
		/* $collection['a'] = 'asdasf';
		var_dump($collection['a']);
		 */
		
		//ok
		//var_dump($collection['a']);
		
		//failed
		/* if (is_array($collection))
		{
			echo "ok";
		}
		else
		{
			echo "failed";
		} */
		
		//ok
		foreach ($collection as $key => $value)
		{
			echo $key.'=>'.$value.'<br>';
		}
	}
}