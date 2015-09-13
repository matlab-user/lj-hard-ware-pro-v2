<?php

	header( 'Content-type:text/html;charset=utf-8' );
	
	require_once( 'db.php' );
	require_once( 'config.php' );

	if( !isset($_POST['json']) || !isset($_POST['signature']) ) {
		echo 'result=data invalid';
		exit;
	}

	$post_str = $_POST['json'];
	//$post_str = '{"summary":"0.01","amt":"0.01","name":"\u6D4B\u8BD5\u5E10\u62372","school_account":"201520152015","order_no":"201520152015201509131846314782"}';
	$post = json_decode( $post_str, true );
	
	if( !isset($post['order_no']) || !isset($post['amt']) ) {
		echo 'result=lack data';
		exit;
	}
	
	// count MD5
	$mid = $post_str . $config['pay_token'];
	$mid2 = md5( $mid );

	if( $mid2==$_POST['signature'] ) {
		$db = new db( $config );
	
		$data['recv_t'] = time();		
		$db->update( 'hpay_table', $data, "order_no='".$post['order_no']."'" );
		
		// 调用一卡通转账
		$row = $db->get_one( "SELECT card_token, password FROM user_info WHERE studentNo='".$post['school_account']."' LIMIT 1" );
		$token = $row['card_token'];
		$password = $row['password'];
		
		$post_data = json_encode( array('body'=>array('studentNo'=>$post['school_account'],'token'=>$token,'password'=>$password,'depositNo'=>$post['order_no'],'depositMoney'=>$post['amt']*100,'schoolId'=>$config['school_id'])) );
		$res = http_post_json( 'tpdeposit', $post_data );
		
		// 在数据库中标记，此单已转
		if( $res['resp_code']==0 ) {
			$data2 = array();
			$data2['if_pay'] = 1;
			$data2['pay_t'] = time();
			
			$db->free_result();
			$db->update( 'hpay_table', $data2, "order_no='".$post['order_no']."' AND if_pay=0" );
		}
			
		echo 'result=00';
	}
	else {
		echo 'result=data sign is wrong';
	}

//--------------------------------------------------------------------------------------
	function http_post_json( $key, $jsonStr ) {
		
		global $config;
		
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_URL, $config['api_server_url'].'?key='.$key );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonStr );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json; charset=utf-8',
			'Content-Length: ' . strlen($jsonStr),
			'resTime'=>time(),
			'key'=>$key
		) );

		$response = curl_exec( $ch );
		$httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		$request_result = array( $httpCode, $response );
		$result = parse_card_data( $request_result );
		return $result;
	}
	
	function parse_card_data( $data ) {
		
		global $config;
		
		$data_obj = json_decode( $data[1], true );
		$data_obj_body = $data_obj['body'];
		$data_obj_data = $data_obj_body['data'] == '' ? $data_obj_body['log']:$data_obj_body['data'];
		if( $data_obj_body['resp_code']>0 && strpos($data_obj_data,$config['token_invaild_str'])>0 ) {
			$data_obj_body['resp_code'] = $config['token_invaild'];
		}
		
		$result = array( 'resp_desc'=>$data_obj_body['resp_desc'], 'resp_code'=>$data_obj_body['resp_code'], 'data'=>$data_obj_data );
		return $result;
	}

?>