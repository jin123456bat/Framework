<?php
xhprof_start();
function xhprof_start() {
	VsmedProf::start();
	register_shutdown_function('xhprof_stop');
}
function xhprof_stop() {
	VsmedProf::stop();
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
			socket_sendto($sock, $sendData, $len, 0, 'api.xhprof.com', 80);
			socket_close($sock);
		}
		self::$stopped = true;
	}
}