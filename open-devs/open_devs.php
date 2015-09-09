<?php

	require_once( 'config.php' );
	require_once( 'db.php' );

	pcntl_signal( SIGCHLD, SIG_IGN );
	date_default_timezone_set( 'Asia/Chongqing' );
	
	$except_devs = array();			// 需要正常使用的设备房间号，记录在此
	$db = new db( $config );
	
	
	$except_devs = array('J61021','J63211','J65031','J65091','J65171','J65211');
	$ea1 = array('J65011','J65021','J65041','J65051','J65061','J65071','J65081','J65101','J65111','J65121','J65131','J65141','J65151','J65161','J65181','J65191','J65201','J65221','J65231','J65251','J65261');
	$except_devs = array_merge( $except_devs, $ea1 );
	
	$con = '';
	$con2 = '';
	
	$sql_res = $db->get_all( 'SELECT dev_locate FROM devices_ctrl' );
	foreach( $sql_res as $v )
		$dev_ids[] = $v['dev_locate'];
		
	foreach( $except_devs as $v ) {
		$con2 .= 'dev_locate="'.$v.'" OR ';
	}
	
	$mid = array_diff( $dev_ids, $except_devs );
	foreach( $mid as $v ) {
		$con .= 'dev_locate="'.$v.'" OR ';
	}
	
	$con = rtrim( $con, 'OR ' );
	$con2 = rtrim( $con2, 'OR ' );
	//echo "$con\r\n";

	$data = array();
	$data['open_t'] = 0;
	$data['close_t'] = 0;
	$data['remark'] = '';
	$data['ins_send_t'] = 0;
	

	$data['ins_recv_t'] = 0;
	$data['ins'] = 'NONE';
	$data['student_no'] = '-1';
	$sql_res = $db->update( 'devices_ctrl', $data, $con2 );
		
	$data['student_no'] = '201520152015';
	$s1 = microtime( true );
	while( 1 ) {
		
		$data['ins_recv_t'] = time();
		$data['ins'] = 'OPEN';
		$sql_res = $db->update( 'devices_ctrl', $data, $con );

		echo "this loop cost: ".(microtime(true)-$s1)." s\r\n";
		sleep( 26*60 );					// 每26分钟执行一次
	}
	
	$db->close();
?>
