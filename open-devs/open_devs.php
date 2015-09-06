<?php

	require_once( 'config.php' );
	require_once( 'db.php' );

	pcntl_signal( SIGCHLD, SIG_IGN );
	date_default_timezone_set( 'Asia/Chongqing' );
	
	$except_devs = array();			// 需要正常使用的设备房间号，记录在此
	$db = new db( $config );
	
	
	$except_devs[] = 'J61021';
	
	
	$con = '';
	foreach( $except_devs as $v ) {
		$con .= 'dev_locate<>"'.$v.'" OR ';
	}
	$con = rtrim( $con, 'OR ' );
	
	$data = array();
	$data['student_no'] = '201520152015';
	$data['open_t'] = 0;
	$data['close_t'] = 0;
	$data['remark'] = '';
	$data['ins_send_t'] = 0;
	$data['ins'] = 'OPEN';
	
	$s1 = microtime( true );
	while( 1 ) {
		
		$data['ins_recv_t'] = time();
		
		$sql_res = $db->update( 'devices_ctrl', $data, $con );
		
		echo "this loop cost: ".(microtime(true)-$s1)." s\r\n";
		sleep( 26*60 );					// 每26分钟执行一次
	}
	
	$db->close();
?>