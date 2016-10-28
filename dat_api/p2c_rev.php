<?php
require_once('base.php');
define('ERR_FOR_API', -3);
define('ERR_FOR_DAT', -4);
define('ERR_FOR_FORMAT', -5);

$FORM = array(
	'operation_stat' => array(
		'format_0' => array( # default
			'sqlfmt' => '(make_time,class,category,mode,cache_size,proxy_cache_size,cache_hit_size,service_size,service_time,task_size,play_cnt,hit_cnt,service_cnt,sn)',
			'tb_name'=> 'operation_stat',
		),
	),
	'cdn_traffic_stat' => array(
		'format_0' => array( # default
			'sqlfmt' => '(make_time,cache,service,monitor,cpu,mem,sn)',
			'tb_name'=> 'cdn_traffic_stat',
		),
	),
	'xvirt_traffic_stat' => array(
		'format_0' => array( # default
			'sqlfmt' => '(make_time,cache,service,sn)',
			'tb_name'=> 'xvirt_traffic_stat',
		),
	),
	'cdn_node_stat' => array(
		'format_0' => array( # default
			'sqlfmt' => '(make_time,host,name,regist_time,update_time,pn_title,status,type,sn)',
			'tb_name'=> 'cdn_node_stat',
		),
	),
);

$name_cnt = 0;
$datas = array();

foreach($FORM as $k=>$v){
	$form_name = $k;
	if(isset($_POST[$form_name])){
		$d = $_POST[$form_name];
		$name_cnt++;

		if(isset($_POST[$form_name.'_format'])){
			$format = $_POST[$form_name.'_format'];
			if(is_numeric($format)){
				$sqlfmt = $v["format_$format"]['sqlfmt'];
				$tb_name = $v["format_$format"]['tb_name'];
			}
		}else {
			$sqlfmt = $v["format_0"]['sqlfmt'];
			$tb_name = $v["format_0"]['tb_name'];
		}

		$o = array(
			'format' => $sqlfmt,
			'tb_name' => $tb_name,
			'data' => $d,
		);

		$datas[$form_name] = $o;
	}
}

if($name_cnt>0)
	connect_db();
else{
	exit_false('no input data', -1);
}

$arr_err = array();
$last_err = 0;

foreach ($datas as $k=>$v){
	$data = $v['data'];
	$VAL = stripslashes($data);
	$OP_FORMT = $v['format'];
	$tb_name = $v['tb_name'];

	if(empty($OP_FORMT) || empty($tb_name)){
		$arr_err[$tb_name . '_tb_error'] = ERR_FOR_FORMAT;
		$arr_err[$OP_FORMT . '_format_error'] = ERR_FOR_FORMAT;
		$last_err = ERR_FOR_FORMAT;
	}else {
		$sql = "insert into $tb_name $OP_FORMT VALUES $VAL";

		$success = mysql_query($sql);
		if (!$success) {
			$last_err = mysql_errno();
			$arr_err[$tb_name . '_error'] = $last_err;
		} else {
			$arr_err[$tb_name . '_success'] = true;
		}
	}
}

if($last_err) {
	if($name_cnt==1) {
		$rlt = array("success" => false);
		$rlt[error] = $last_err;
	}else {
		$rlt = array("success" => false);
		$rlt[error] = $last_err;
	}
}
else
	$rlt = array("success" => true);

foreach($arr_err as $k=>$v) {
	$rlt[$k] = $v;
}
echo json_encode($rlt);
exit;

?>
