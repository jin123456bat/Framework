<?php
namespace framework\core\log;
abstract class AbstractLogger implements LoggerInterface
{
	public function log($level, $message, array $context = array())
	{
		$data = '['.date('Y-m-d H:i:s').'] ['.$level.'] '.$message."\n\r";
		$log_path = $this->_log_path.'mysql.log';
		file_put_contents($log_path, $data,FILE_APPEND);
	}
}