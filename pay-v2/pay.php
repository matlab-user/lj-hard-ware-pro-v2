<?php

	require_once( 'config.php' );
	require_once( 'db.php' );
	require_once( 'pay_api.php' );

	$api = new payapi( $config );
	$t = time();
	
	while( 1 ) {
		// 查询 fee_record 中未支付的账单 
		$res = $api->get_unpay_fee();
		
		foreach( $res as $v ) {
			
			$data =array();
			
			$con = 'trade_no="'.$v['trade_no'].'"';
		
			if( $v['student_no']=='201520152015' ) {
				$api->db->delete( 'fee_record', $con );
				continue;
			}
					
			$password = $api->get_stu_password( $v['student_no'] );
			$api->get_token_from_database( $v['student_no'] );

			// 通过一卡通支付
			// 0400002   控水对接
			// 0400003   电控对接
			// 0400004   洗衣机对接
			
			if( $v['dev_type']=='shower' ) {
				if( $v['fee']<=0 ) {
					$v['fee'] = $api->count_shower_fee( $v['sum_t'] );
				}
				$trade_type_id = '0400002';
			}
			
			if( $v['dev_type']=='washer' ) {
				if( $v['fee']<=0 ) {
					if( $v['sum_t']>=2400 )
						$v['fee'] = 400;
					else
						$v['fee'] = round( $v['sum_t']/3000*400 );
				}
				$trade_type_id = '0400004';
			}
		
			//echo $v['sum_t'].'  '.$v['fee']."\r\n";
			//continue;
			
			if( $v['fee']>0 ) {
				$data['fee'] = $v['fee'];
				$api->db->update( 'fee_record', $data, $con );
				$response = $api->tptrade( $v['student_no'], $api->pay_token, $v['trade_no'], $password, $trade_type_id, $v['fee'] );
				
				//echo $response['resp_desc']."\t".$response['resp_code']."\t".$response['data']."\r\n";
				
				if( !empty($response) && $response['resp_code']==0 ) { 	// 支付成功
					$data = array( 'fee_flag'=>1 );
					$api->db->update( 'fee_record', $data, $con );
				}
			}
			else
				$api->db->delete( 'fee_record', $con );		
		}
		
		if( time()-$t>120 ) {
			$res = $api->db->query( "insert into fee_payed_record select * from fee_record where fee_flag=1" );
			if( $res )
				$api->db->query( "delete from fee_record where fee_flag=1" );
			$t = time();
		}
		
		sleep( 5 );
	} 
	

?>
