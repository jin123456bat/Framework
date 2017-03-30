<?php
namespace application\extend;

use framework\core\database\sql;

class model extends \framework\core\model
{

	function __construct($table)
	{
		parent::__construct($table);
	}

	function query($sql, $array = array())
	{
		$app = $this->getConfig('app');
		if (! isset($app['query_cache']) || ! $app['query_cache'])
		{
			return parent::query($sql, $array);
		}
		if (in_array(strtolower(substr(trim($sql), 0, 6)), array(
			'select'
		), true))
		{
			if (is_string($sql))
			{
				$key = md5($sql . json_encode($array));
			}
			else if ($sql instanceof sql)
			{
				$key = md5($sql->__toString() . json_encode($sql->getParams()));
			}
			$result = $this->model('query_cache')
				->where('md5=?', array(
				$key
			))
				->scalar('result');
			if (! empty($result))
			{
				// 当缓存被利用之后增加记录
				// $this->model('query_cache')->where('md5=?',array($key))->update(array(
				// 'call_num+='=>1,
				// 'call_time'=>time(),
				// ));
				if ($sql instanceof sql)
				{
					$sql->clear();
				}
				return json_decode($result, true);
			}
			else
			{
				$query_string = ($sql instanceof sql) ? $sql->__toString() : $sql;
				$params = ($sql instanceof sql) ? $sql->getParams() : $array;
				$result = parent::query($sql, $array);
				
				$this->model('query_cache')->insert(array(
					'md5' => $key,
					'query_string' => $query_string,
					'param' => json_encode($params),
					'result' => json_encode($result),
					'create_time' => time(),
					'call_num' => 0,
					'call_time' => time()
				));
				return $result;
			}
		}
		else
		{
			return parent::query($sql, $array);
		}
	}
}
