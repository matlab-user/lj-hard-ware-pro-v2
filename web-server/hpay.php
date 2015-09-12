<?php
	require_once( 'db.php' );
	require_once( 'config.php' );
	
	if( !isset($_POST['order_no']) || !isset($_POST['amount']) || !isset($_POST['school_code']) || !isset($_POST['sign']) )
		exit;
	 
	// count MD5
	$mid = $_POST['order_no'] . $_POST['amount'] . $config['pay_token'];
	$mid = md5( $mid );

	if( $mid==$_POST['sign'] ) {
		$db = new db( $config );
	
		$data['order_no'] = $_POST['order_no'];
		$data['amount'] = $_POST['amount'];
		$data['school_code'] = $_POST['school_code'];
		$data['sign_str'] = $_POST['sign'];
		$data['summary'] = $_POST['summary'];
		$data['name'] = $_POST['name'];
		$data['stu_no'] = $_POST['school_account'];
		$data['recv_t'] = time();
				
		$db->insert( 'hpay_table', $data );
		
		// 调用一卡通转账
		$row = $db->get_one( "SELECT card_token,password FROM user_info WHERE studentNo='".$data['stu_no']."' LIMIT 1" );
		$token = $row['card_token'];
		$password = $row['password'];
		
		$post_data = json_encode( array('body'=>array('studentNo'=>$data['stu_no'],'token'=>$token,'password'=>$password,'depositNo'=>$data['order_no'],'depositMoney'=>$data['amount'],'schoolId'=>$data['school_code'])) );
		$res = http_post_json( 'tpdeposit', $post_data );
		
		// 在数据库中标记，此单已转
		if( $res['resp_code']==0 ) {
			$data2 = array();
			$data2['if_pay'] = 1;
			$data2['pay_t'] = time();
			
			$db->free_result();
			$db->update( 'hpay_table', $data2, "order_no='".$data['order_no']."' AND if_pay=0" );
		}
		
		$db->close();
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