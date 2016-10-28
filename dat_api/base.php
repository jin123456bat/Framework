<?php

function connect_db() {
	global $query_link;

	$mysql_host = "localhost";
	$mysql_user = "root";
	$query_link = mysql_pconnect($mysql_host, $mysql_user);
	mysql_select_db('cds_v2', $query_link);
	# mysql_select_db('cache', $query_link);
	mysql_query('set names utf8', $query_link);
}

function dblog($event, $accounts) {
	connect_db();

	$escape_event = mysql_real_escape_string($event);
	if (!isset($accounts)) $accounts = $_SESSION[$_SERVER["HTTP_HOST"]]["accounts"];
	$sql = "INSERT INTO admin_log VALUES (NOW(), '$_SERVER[REMOTE_ADDR]', '$accounts', '$escape_event')";
	mysql_query($sql);
}

function exit_success() {
	echo json_encode(array("success" => true));
	exit(0);
}

function exit_false($err, $err_code) {
	$rlt = array("success" => false);
	if ($err != '') {
		$rlt['info'] = trim($err);
		if(isset($err_code)) $rlt['error'] = $err_code;
	}
	echo json_encode($rlt);
	exit(1);
}

function quote($path_name){
	return preg_replace('/([^\w\/\.])/','\\\\$1',$path_name);
}

function get_count($sql){
	global $query_link;
	$result = mysql_query($sql, $query_link);
	$record = mysql_fetch_row($result);
	$count = $record[0];
	mysql_free_result($result);
	return $count;
}
//计算grid的数据
function get_data($sql, $target){
	global $query_link;
	$result = mysql_query($sql, $query_link);

	$col_count = mysql_num_fields($result);
	for($i=0; $i < $col_count; $i++){
		$names[$i] = mysql_fetch_field($result, $i)->name;
	}
	$row = 0;
	$aoData = array();
	while($record = mysql_fetch_row($result)){
		$aoData[$row] = array();
		for($i=0; $i<$col_count;$i++){
			$aoData[$row][$names[$i]] = $record[$i];
		}
		$row++;
	}
	mysql_free_result($result);
	if($target!='') return $aoData;
	return json_encode($aoData);
}
?>
