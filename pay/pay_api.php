<?php

	require_once( 'config.php' );
	require_once( 'db.php' );
	
	class payapi {
		private $api_server_url;
		public $pay_token = '';
		private $user_login_token;
		private $config;
		public $db;
		private $school_id;
			
		public function __construct( $config ) {
			$this->config = $config; 												// 载入配置文件
			$this->api_server_url = $this->config['api_server_url']; 				//一卡通接口地址								
			$this->school_id = $this->config['school_id']; 							//学校ID
			$this->db = new db( $this->config );				
		}
		
		/*
			* 与一卡通通信接口
			* @param student_no 
		*/
		public function signInAndGetUser( $student_no, $password ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'password'=>$password,'schoolId'=>$school_id)) );
			$response = $this->http_post_json( 'signInAndGetUser', $post_data );
			
			$token = '';
			//update token
			if( !empty($response) )
				$token = $response['data']['userMap']['token'];
			
			if($token){
				$response = $this->http_post_json( 'signInAndGetUser', $post_data );
				//update token
				$token = $response['data']['userMap']['token'];
			}
			
			$this->pay_token = $token;
			return $response;
		}
		
		public function get_token_from_database( $student_no ) {
			$row = $this->db->get_one( "select card_token from user_info where studentNo=$student_no" );
			$this->pay_token = $row['card_token'];
			return true;
		}
		
		// 查询 fee_record 中未支付的账单 
		public function get_unpay_fee() {
			$res = $this->db->get_all( "SELECT * FROM fee_record WHERE fee_flag=0 " );
			return $res;
		}
		
		// 获取指定学生的密码
		public function get_stu_password( $stu_no ) {
			$res = $this->db->get_all( "SELECT password FROM user_info WHERE studentNo='$stu_no'" );
			return $res[0]['password']; 
		}
			
		// 一卡通支付接口
		public function tptrade( $student_no, $token, $trade_no, $password, $trade_branch_id, $trade_money ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array("body"=>array("studentNo" => $student_no,"token" => $token,"password"=>$password,"tradeBranchId"=>$trade_branch_id,"tradeNo"=>$trade_no,"tradeMoney"=>$trade_money,"schoolId"=>$school_id)) );
			$data = $this->http_post_json( 'tptrade', $post_data );
			return $data;
		}

		/*
			* json post 传输
			* @param student_no 
		*/
		private function http_post_json( $key, $jsonStr ) {
			
		  $ch = curl_init();
		  curl_setopt( $ch, CURLOPT_POST, 1 );
		  curl_setopt( $ch, CURLOPT_URL, $this->api_server_url.'?key='.$key );
		  curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonStr );
		  curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		  curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			  'Content-Type: application/json; charset=utf-8',
			  'Content-Length: ' . strlen($jsonStr),
			  'resTime'=>time(),
			  'key'=>$key
			)
		  );
		  
		  $response = curl_exec( $ch );
		  $httpCode = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
		  $request_result = array( $httpCode, $response );
		  $result = $this->parse_card_data( $request_result );
		  return $result;
		}
		
		private function parse_card_data( $data ) {
			$data_obj = json_decode( $data[1], true );
			$data_obj_body = $data_obj['body'];
			$data_obj_data = $data_obj_body['data'] == '' ? $data_obj_body['log']:$data_obj_body['data'];
			if( $data_obj_body['resp_code']>0 && strpos($data_obj_data,$this->config['token_invaild_str'])>0 ) {
				$data_obj_body['resp_code'] = $this->config['token_invaild'];
			}
			$result = array( 'resp_desc'=>$data_obj_body['resp_desc'], 'resp_code'=>$data_obj_body['resp_code'], 'data'=>$data_obj_data );
			return $result;
		}
	
	}
	
?>