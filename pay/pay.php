<?php

	require_once( 'config.php' );
	require_once( 'db.php' );
	require_once( 'pay_api.php' );
	
	
	$api = new payapi( $config );
	
	while( 1 ) {
		// 查询 fee_record 中未支付的账单 
		//$res = $api->get_unpay_fee();
		$res[0] = array( 'student_no'=>'201520152015', 'fee'=>1, 'dev_id'=>'00101', 'trade_no'=>date("YmdHis") ) ;
		
		foreach( $res as $v ) {
			$password = $api->get_stu_password( $v['student_no'] );
			$api->signInAndGetUser( $v['student_no'], $password );			// 获取 student_no 的一卡通token， 存于$api->pay_token
			echo $api->pay_token."\t$password"."\r\n";
			
			// 通过一卡通支付
			$response = $api->tptrade( $v['student_no'], $api->pay_token, $v['trade_no'], $password, $v['dev_id'], $v['fee'] );
			echo $response['resp_desc']."\t".$response['resp_code']."\t".$response['data']."\r\n";
			
			if( $response['resp_code']==0 ) { 	// 支付成功
				$con = 'trade_no="'.$v['trade_no'].'"';
				$data = array( 'fee_flag'=>1 );
				$this->db->update( 'fee_record', $data, $con );
			}
		}
			
		break;
		//sleep( 3 );
	} 
	

?>