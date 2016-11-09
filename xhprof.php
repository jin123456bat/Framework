<?php
xhprof_start();
function xhprof_start() {
	if (defined('DEBUG') && DEBUG)
	{
		VsmedProf::start();
		register_shutdown_function('xhprof_stop');
	}
}
function xhprof_stop() {
	if (defined('DEBUG') && DEBUG)
	{
		VsmedProf::stop();
	}
}
class  VsmedProf {
	static $started = false;
	static $stopped = false;
	public static function start() {
		
		if (!extension_loaded('xhprof') || self::$started === true) {
			return;
		}
		
		xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
		xhprof_enable(XHPROF_FLAGS_NO_BUILTINS);
		self::$started = true;
	}

	public static function stop() {
		if(self::$stopped || self::$started === false) {
			return;
		}
		$xhprofData = xhprof_disable();
		
		$XHPROF_ROOT = __DIR__.'/xhprof';
		
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
		include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
		// save raw data for this profiler run using default
		// implementation of iXHProfRuns.
		$xhprof_runs = new XHProfRuns_Default();
		// save the run under a namespace "xhprof_foo"
		$run_id = $xhprof_runs->save_run($xhprofData, "xhprof_foo");
		//echo "<a href='http://www.pztai.com/xhprof/xhprof_html/?run=$run_id&source=xhprof_foo'>分析</a>";//
		
		$send_arr = array(
			'username' => 'jin123456bat',
			'password' => 'b99f1a1c875c4f200449dc55eb4b77a6',
		);
		$send_arr['domain']    = $_SERVER['HTTP_HOST'];
		$send_arr['uri']       = $_SERVER["REQUEST_URI"];
		$send_arr['xhprof_id'] = uniqid();
		$send_arr['xhprof_data'] = $xhprofData;
		$send_arr['xhprof_time'] = time();

		$sendData = serialize($send_arr)."\n";
		$len = strlen($sendData);
		if ($len >= 64500) {
			// send use tcp
			$fp = fsockopen('api.xhprof.com', 8005, $errno, $errstr, 1);
			if (!$fp) return;
			fwrite($fp, $sendData, $len);
		} else {
			// send use udp
			$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
			if(!socket_sendto($sock, $sendData, $len, 0, 'api.xhprof.com', 80))
			{
				echo "发送失败";
				exit();
			}
			socket_close($sock);
		}
		self::$stopped = true;
	}
}