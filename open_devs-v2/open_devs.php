<?php

	require_once( 'config.php' );
	require_once( 'db.php' );

	pcntl_signal( SIGCHLD, SIG_IGN );
	date_default_timezone_set( 'Asia/Chongqing' );
	
	$except_devs = array();			// 需要正常使用的设备房间号，记录在此
	$db = new db( $config );
	
	$except_devs = array('J61021','J63211','J65031','J65091','J65171','J65211','J62011', 'J62021', 'J62031', 'J62041', 'J62051', 'J62061', 'J62071', 'J62081', 'J62091', 'J62101', 'J62111', 'J62121', 'J62131', 'J62141', 'J62151', 'J62161', 'J62171', 'J62181', 'J62191', 'J62201', 'J62211', 'J62221', 'J62231', 'J62241', 'J62251', 'J62261');

	$ea1 = array('J65241', 'J65011','J65021','J65041','J65051','J65061','J65071','J65081','J65101','J65111','J65121','J65131','J65141','J65151','J65161','J65181','J65191','J65201','J65221','J65231','J65251','J65261');
	$except_devs = array_merge( $except_devs, $ea1 );

	$ea2 = array('J61011', 'J61021', 'J63011','J63021','J63031','J63041','J63051','J63061','J63071','J63081','J63091','J63101','J63111','J63121', 'J63131','J63141','J63151','J63161','J63171','J63181','J63191','J63201','J63211','J63221','J63231','J63241','J63251','J63261' );
	$except_devs = array_merge( $except_devs, $ea2 );

	$ea3 = array('J64011', 'J64021', 'J64031', 'J64041', 'J64051', 'J64061', 'J64071', 'J64081', 'J64091', 'J64101', 'J64111', 'J64121', 'J64131', 'J64141', 'J64151', 'J64161', 'J64171', 'J64181', 'J64191', 'J64201', 'J64211', 'J64221', 'J64231', 'J64241', 'J64251', 'J64261' );
	$except_devs = array_merge( $except_devs, $ea3 );

	$ea4 = array('J67011', 'J67021', 'J67031', 'J67041', 'J67051', 'J67061', 'J67071', 'J67081', 'J67091', 'J67101', 'J67111', 'J67121', 'J67131', 'J67141', 'J67151', 'J67161', 'J67171', 'J67181', 'J67191', 'J67201', 'J67211', 'J67221', 'J67231', 'J67241', 'J67251', 'J67261' );
	$except_devs = array_merge( $except_devs, $ea4 );

	$ea5 = array('J66011', 'J66021', 'J66031', 'J66041', 'J66051', 'J66061', 'J66071', 'J66081', 'J66091', 'J66101', 'J66111', 'J66121', 'J66131', 'J66141', 'J66151', 'J66161', 'J66171', 'J66181', 'J66191', 'J66201', 'J66211', 'J66221', 'J66231', 'J66241', 'J66251', 'J66261' );
	$except_devs = array_merge( $except_devs, $ea5 );

	$ea6 = array('H3A111', 'H3A211', 'H3A311', 'H3A411', 'H3A511', 'H3A611', 'H3A711', 'H3A112', 'H3A212', 'H3A312', 'H3A412', 'H3A512', 'H3A612', 'H3A712', 'H3A113', 'H3A213', 'H3A313', 'H3A413', 'H3A513', 'H3A613', 'H3A713', 'H3A121', 'H3A221', 'H3A321', 'H3A421', 'H3A521', 'H3A621', 'H3A721', 'H3A122', 'H3A222', 'H3A322', 'H3A422', 'H3A522', 'H3A622', 'H3A722', 'H3A123', 'H3A223', 'H3A323', 'H3A423', 'H3A523', 'H3A623', 'H3A723' );
	$except_devs = array_merge( $except_devs, $ea6 ) ;

    $ea7 = array('H3C111', 'H3C211', 'H3C311', 'H3C411', 'H3C511', 'H3C611', 'H3C711', 'H3C112', 'H3C212',	'H3C312', 'H3C412', 'H3C512', 'H3C612', 'H3C712', 'H3C113', 'H3C213', 'H3C313', 'H3C413', 'H3C513', 'H3C613', 'H3C713', 'H3C121', 'H3C221', 'H3C321', 'H3C421', 'H3C521', 'H3C621', 'H3C721', 'H3C122', 'H3C222', 'H3C322', 'H3C422', 'H3C522', 'H3C622', 'H3C722', 'H3C123', 'H3C223', 'H3C323', 'H3C423', 'H3C523', 'H3C623', 'H3C723' );
    $except_devs = array_merge( $except_devs, $ea7 ) ;

	$ea8 = array('J61011', 'J61021', 'J61051', 'J61061', 'J61071', 'J61081', 'J61091', 'J61101', 'J61111', 'J61121', 'J61131', 'J61141', 'J61151', 'J61161', 'J61171', 'J61181', 'J61191', 'J61201', 'J61211', 'J61221', 'J61231', 'J61241', 'J61251', 'J61261');	
	$except_devs = array_merge( $except_devs, $ea8 );
	
	$except_devs = array_unique( $except_devs );
	
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
	echo "$con\r\n";

	$data = array();
	$data['open_t'] = 0;
	$data['close_t'] = 0;
	$data['remark'] = '';
	$data['ins_send_t'] = 0;
	

	$data['ins_recv_t'] = 0;
	$data['ins'] = 'NONE';
	$data['student_no'] = '-1';
	$sql_res = $db->update( 'devices_ctrl', $data, $con2." AND student_no='201520152015'" );
	$sql_res = $db->update( 'devices_ctrl', $data, "dev_type='washer'" );
		
	$data['student_no'] = '201520152015';
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
