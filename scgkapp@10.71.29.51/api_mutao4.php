<?php
	header( 'Content-type:text/html;charset=utf-8' );
	
	require_once( 'db.php' );

	class cardApi {
		private $api_server_url;
		private $socket_server_url;
		private $socket_server_port;
		private $socket;
		private $token;
		private $user_login_token;
		private $config;
		private $db;
		
		public function __construct( $config ) {
			$this->config = $config; 												// 载入配置文件
			$this->api_server_url = $this->config['api_server_url']; 				//一卡通接口地址
			$this->washer_fee = $this->config['washer_fee']; 						//洗衣机费
			$this->water_fee = $this->config['water_fee']; 							//水费
			$this->token = $this->config['token']; 									//水费
			$this->school_id = $this->config['school_id']; 							//学校ID
			$this->socket_server_url = $this->config['socket_server_url']; 			//智控系统地址
			$this->socket_server_port = $this->config['socket_server_port'];		//智控系统端口	
			$this->db = new db( $this->config );				
		}
		

		//绑定设备
		public function bind_device_by_qrcode( $student_no, $qrcode, $token ) {
			
			$device_id = $qrcode;
			if( !preg_match('/^(H|J)[A-Z0-9]{5}$/', $qrcode) ) {
				echo '{
					"resp_desc" : "非法设备号",
					"resp_code" : "1",
					"data"      : "{}"
				}';
				return false;
			} 
			
			$row = $this->db->get_one( "select * from devices where student_no='$student_no' and device_id='$device_id' limit 1" );
			if( $row ) {
				echo '{
						"resp_desc" : "您已经绑定过了",
						"resp_code" : "1",
						"data"      : "{}"
					 }';
			}
			else {
				$data = array( 'student_no'=>$student_no, 'device_id'=>$device_id );
				$result = $this->db->insert( 'devices', $data );
				if( $result ) {
					echo '{
							"resp_desc" : "绑定成功",
							"resp_code" : "0",
							"data"      : "{}"
						 }';
				}
				else{
					echo '{
							"resp_desc" : "绑定失败",
							"resp_code" : "1",
							"data"      : "{}"
						 }';
				}
			}		
			return true;
		}

		//解除设备
		public function unbind_device( $student_no, $device_id, $token ) {

			$row = $this->db->get_one( "SELECT * FROM devices WHERE student_no='$student_no' AND device_id='$device_id' LIMIT 1" );
			if( $row ) {
				if( $row['flag']==1 ) {
					echo '{
							"resp_desc" : "设备正在使用中，无法删除",
							"resp_code" : "1",
							"data"      : "{}"
						 }';
				}
				else {
					$result = $this->db->delete( "devices","device_id='$device_id' AND student_no='$student_no'" );
					if( $result ) {
						echo '{
								"resp_desc" : "删除成功",
								"resp_code" : "0",
								"data"      : "{}"
							 }';
					}
					else {
						echo '{
								"resp_desc" : "删除失败",
								"resp_code" : "1",
								"data"      : "{}"
							 }';
					}
				}
				
			}
			else {
				echo '{
						"resp_desc" : "您没有绑定该设备",
						"resp_code" : "1",
						"data"      : "{}"
					 }';
			}		
			return true;
		}

			//获取设备列表
		public function get_device_list( $student_no, $token='' ) {
			// 获取用户所有绑定设备
			// 此时获得的 device_id 为 devices_ctrl 表中的 dev_locate; 
			$con = '';
			$sql = "SELECT device_id FROM devices WHERE student_no='$student_no'";
			$query = $this->db->get_all( $sql );
			foreach( $query as $v ) {
				$con .= "dev_locate='".$v['device_id']."' OR ";
			}
			$con = rtrim( $con, " OR" );	
		
			// 在 devices_ctrl 表中获得 指定设备状态
			$devices = array();
			
			$res = $this->db->get_all( 'SELECT student_no, dev_id, dev_locate, ins, dev_type, open_t FROM devices_ctrl WHERE '.$con );
			foreach( $res as $v ) {
				$building_info = $this->parse_device_by_device_id( $v['dev_locate'] );
				$item['building'] = $this->config['build_map'][$building_info['building']];
				$item['floor'] = $building_info['floor'];
				$item['room'] = $building_info['room'];
				
				$item['lt'] = 0;
				
				if( $v['dev_type']=='shower' )
					$item['deviceName'] = '淋浴房';
				
				if( $v['dev_type']=='washer' )
					$item['deviceName'] = '洗衣机';
				
				$item['deviceId'] = $v['dev_locate'];
				$item['deviceDesc'] = $item['building'].$building_info['device_desc'];
				
				$item['deviceStatus'] = 2;					// 被他人占用
				if( $v['student_no']=='-1' || $v['ins']=='NONE' ) {
						$item['deviceStatus'] = 0;			// 设备空闲
				}
				else {
					if( $v['student_no']==$student_no ) {
						if( $v['ins']=='OPEN' ) {
							if( $v['open_t']>0 ) {
								$item['deviceStatus'] = 1;			// 设备被自己占用(开启中)
								$item['lt'] = time() - $v['open_t'];
							}
							else {
								$item['deviceStatus'] = 4;			// 设备开启中
							}
						}
						
						if( $v['ins']=='CLOSE' ) {				// 设备关闭中
							$item['deviceStatus'] = 3;
							$item['lt'] = time() - $v['open_t'];
						}
					}
				}
				
				$item['deviceType'] = $building_info['device_type'];
				$item['deviceIcon'] = $this->config['build_device_icon'][$building_info['device_type']];
				$devices[] = $item;
			}

			$result['resp_code'] = '0';
			$result['resp_desc'] = '';

			if( is_array($devices) ) {
				$result['data'] = $devices;
			}
			else{
				$result['data'] = '[]';
			}
	
			echo json_encode( $result );
			return true;
		}

		public function get_version_info($os) {
				echo '{
                               	 "resp_desc" : "获取成功",
                               	 "resp_code" : "0",
                               	 "data":{
                                        "android":{
                                                "version":"1",
                                                "release_notes":"1:功能升级\n2：BUG修复",
                                                "download_url":"http://www.etzk.com/card/res/appnewest.apk"
                                        },
                                        "ios":{
                                                "version":"1.0.0",
                                                "release_notes":"1:功能升级\n2：BUG修复",
                                                "download_url":"http://pre.im/a01e"
                               	        }
                	       	 }
				}';
			}
		public function get_carrier_info( $token ) {
			echo '{
					"resp_desc" : "获取成功",
					"resp_code" : "0",
					"data":'.$this->config['carrier_account'].'
				 }';
		}

		//修改用户信息
		public function edit_user_info( $student_no, $user_info, $token ) {		
		
			if( $user_info['phone'] ) {
				$values['phone'] = $user_info['phone'];
			}
			
			if( $user_info['wash_setting'] ) {
				$values['wash_setting'] = $user_info['wash_setting'];
				$values['wash_setting'] = str_replace( "'", '"', $values['wash_setting'] );
			}
			
			if( $user_info['carrier_account'] ) {
				$values['carrier_account'] = $user_info['carrier_account'];
			}
			
			if( $user_info['email'] ) {
				$values['email'] = $user_info['email'];
			}
			
			if( $user_info['nickName'] ) {
				$values['nickName'] = $user_info['nickName'];
			}
			
			$condition = "studentNo='$student_no'";
			
			$query = $this->db->update( 'user_info', $values, $condition );
			if( $query ) {
				$this->get_user_info( $student_no, $token );
			}
			else{
				echo '{
						"resp_desc" : "更新失败",
						"resp_code" : "1001",
						"data"      : "{}"
					 }';
			}
			
			return true;
		}

		//获取用户信息
		public function get_user_info( $student_no, $token ) {
			
			$sql = "SELECT `id`,`studentNo`,`sex`,`phone`,`department`,`school_zone`,`from`,`graduated`,`home_address`,`nation`,`carrier_account`,`wash_setting`,`cardNo`,`userName`,`nickName`,`headImg`,`email`,`cardBalance`,`monthlyAmt`,`grade`,`major`,`token` FROM `user_info` WHERE `studentNo`='$student_no'";
			$row = $this->db->get_one( $sql );
			$result['resp_desc'] = '';
			$result['resp_code'] = '0';
			$result['data'] = $row;
			
			echo json_encode( $result );
			return true;
		}

		//保修及意见反馈
		public function feedback( $student_no, $msg, $device_id, $desc, $type, $token ) {
	
			$data['student_no'] = $student_no;
			$data['device_id'] = $device_id;
			$data['post_desc'] = $desc;
			$data['msg'] = $msg;
			$data['type'] = $type;
			$data['post_time'] = time();
			$data['post_ip'] = '127.0.0.1';
			$query = $this->db->insert('feedback',$data);
			if($query){
				echo '{
					"resp_desc" : "提交成功",
					"resp_code" : "0",
					"data"      : ""
				}';
			}else{
				echo '{
					"resp_desc" : "提交失败",
					"resp_code" : "1001",
					"data"      : ""
				}';
			}
			
			return true;
		}

		//保修及意见反馈
		public function get_feedback_list( $student_no, $type, $token ) {
				
			$sql = "SELECT * FROM feedback  WHERE student_no='$student_no' and type='$type'";
			$query = $this->db->query( $sql );
			
			while( $row=$this->db->fetch_array($query) ) {
				$item['student_no'] = $row['student_no'];
				$item['device_id'] = $row['device_id'];
				$item['desc'] = $row['post_desc'];
				$item['msg'] = $row['msg'];
				$item['reply'] = $row['reply'];
				$item['post_time'] = $row['post_time'];
				$item['type'] = $row['type'];
				$result[] = $item;
			}
			if( is_array($result) ) {
				$result['resp_code'] = '0';
				$result['resp_desc'] = '';
				$result['data'] = $result;
			}
			else {
				$result['resp_code'] = '1003';
				$result['resp_desc'] = '还没有数据';
				$result['data'] = '';
			}
			
			echo json_encode( $result );
			return true;
		}

		//修改密码
		public function changePassword( $student_no, $student_password, $new_password ) {
			
			$schoolId=$this->school_id;
			$row=$this->db->get_one("select card_token from user_info where studentNo = ".$student_no." limit 1");
			
			$card_token=$row['card_token'];
			$post_data=array("body"=>array("studentNo"=>$student_no,"token"=>$card_token,"oldPassword"=>$student_password,"newPassword"=>$new_password,"schoolId"=>$schoolId));
			$response=$this->http_post_json('changePassword',json_encode($post_data));
			if($response['resp_code']=='0'){
				$condition = "studentNo='$student_no'";
				$values = array( 'password'=>$new_password );
				$query = $this->db->update( 'user_info', $values, $condition );
				if( $query ) {
					$result['resp_code'] = '0';
					$result['resp_desc'] = '修改成功';
					$result['data'] = '';
				}
			}
			else {
				$result['resp_code'] = '1002';
				$result['resp_desc'] = '修改失败';
				$result['data'] = '';
			}
			
			echo json_encode( $result );
			return true;
		}

		// 开淋浴房
		// $device_id - 设备位置信息，不是设备硬件id
		
		public function open_shower( $student_no, $device_id, $time, $delay_open=0, $delay_close, $token, $password='' ) {
			$begin_time = time() + $delay_open;
			$this->operate_device_with_fee( $student_no, $device_id, 'OPEN', 0, $password, $token, $begin_time, $pre_end_time );
		}
		// 关淋浴房
		// $device_id - 设备位置信息，不是设备硬件id
		public function close_shower( $student_no, $device_id, $token, $password ) {
			$this->operate_device_with_fee( $student_no, $device_id, 'CLOSE', 0, $password, $token, 0, $end_time );
		}
		
		// 开洗衣机
		// $device_id - 设备位置信息，不是设备硬件
		public function open_washer( $student_no, $device_id, $token, $password ) {
			$this->operate_device_with_fee( $student_no,$device_id, 'OPEN', 0, $password, $token, $begin_time, $pre_end_time );
		}

		// 关洗衣机
		// $device_id - 设备位置信息，不是设备硬件id
		public function close_washer( $student_no, $device_id, $token, $password ) {	
			$this->operate_device_with_fee( $student_no, $device_id, 'CLOSE', 0, $password, $token, 0, $end_time );
		}
		
		// $device_id - 设备位置信息，不是设备硬件id
		// 此函数返回的计费信息，是还没有形成真实支付账单的信息
		// 真实的支付账单，由 hardware_server.php 生成
		// 真实的支付，由专门程序负责
		private function operate_device_with_fee( $student_no, $device_id, $operate='OPEN', $fee, $password='', $token='', $begin_time=0, $end_time=0 ) {
			
			$buff = '';
			$query = FALSE;
			$resp_code = 0;
			$now = 0;
			
			// 根据 device_id 获取设备是否可以使用，并且获得设备硬件控制id
			$res = $this->db->get_all( "SELECT * FROM devices_ctrl WHERE dev_locate='$device_id'" );
			$res = $res[0];
			
			switch( $operate ) {
				case 'OPEN':
					switch( $res['student_no'] ) {
						case '-1':
							// 写数据库(加上条件，保证合法抢占)，socket发送指令
							$data = array( 'student_no'=>$student_no, 'ins'=>'OPEN', 'ins_recv_t'=>$begin_time );
							$query = $this->db->update( 'devices_ctrl', $data, "dev_id='".$res['dev_id']."' AND student_no='-1'" );
							break;
						
						default:
							if( $res['student_no']==$student_no ) {			// 自己多次按 OPEN, 或 自己关闭后又按 OPEN
								if( $res['ins']=='OPEN' )
									$msg = '您已开启该设备';
								else
									$msg = '设备正在关闭，请稍后再试';
							}
							else {
								$msg = '设备正被别人占用，请稍后再试';
								$resp_code = 1;
							}
							
							echo '{ "resp_desc" : "'.$msg.'",
									"resp_code" : "'.$resp_code.'",
									"data"      : {}
								 }';
							return;						 
							break;
					}
					break;
				
				case 'CLOSE':
					if( $res['dev_type']=='washer' ) {				// 洗衣机只能50分钟后自动断电
						$msg = '洗衣机50分钟后自动关闭，不能中途关闭';
					}
					else {
						if( $res['student_no']=='-1' ) {
							$msg = '请先开启设备';
						}
						elseif( $res['student_no']==$student_no ) {						// 自己开的设备，自己关掉
							$now = time();
							$data = array( 'ins'=>'CLOSE', 'ins_recv_t'=>$now );
							$query = $this->db->update( 'devices_ctrl', $data, "dev_id='".$res['dev_id']."' AND student_no='$student_no'" );
							// 如数据库写入成功，则开始计费
						}
						else {
							$msg = '设备正被别人占用';
							$resp_code = 1;
						}
					}
					
					if( $msg!='' ) {
						$str = '{ "resp_desc":"'.$msg.'","resp_code":"'.$resp_code.'","data":"{}"}';	
						echo $str;
						return;
					}
					break;
			}
			
			if( $query ) {	
			
				$buff = "[web,$operate,".$res['dev_id']."]";
				
				if( $buff!='' ) {
					$this->socket = stream_socket_client( 'tcp://'.$this->socket_server_url.':'.$this->socket_server_port, $errno, $errstr, 15 );
					$this->send_message( $buff );
				}
				
				if( $operate=='CLOSE' ) {					// 计算本次费用
					
					if( $res['open_t']<=0 ) {
						$display_fee_time = 0;
						$total_fee = 0;
					}
					else {
						if( $res['dev_type']=='washer' ) {
							$display_fee_time = 50;
							$total_fee = 4;
							$price = '4元/50分钟';
						}
						else {
							$display_fee_time = round( ($now-$res['open_t']-$res['break_t'] )/60, 2 );
							$total_fee = round( $display_fee_time * $res['price']/100, 2 );
						}
					}
					
					$price = ( $res['price']/100 ).'元/分钟';
					$fee_data = '{"fee_rate":"'.$price.'","time":"'.$display_fee_time.'分钟","total_fee":"'.$total_fee.'元"}';
					
					echo '{ "resp_desc" : "计费成功",
							"resp_code" : "0",
							"data"      : '.$fee_data.'
						 }';		 
				}
				else {
					echo '{ "resp_desc" : "设备开启成功",
							"resp_code" : "0",
							"data"      : {}
						 }';
				}
			}
			else {		
				echo '{
						"resp_desc" : "设备控制失败",
						"resp_code" : "0",
						"data"      : {}
					 }';
			}
		}
		
		// $device_id - 设备位置信息，不是设备硬件id
		public function read_device_status( $student_no, $device_id ) {
			
			$res = $this->db->get_all( "SELECT ins, student_no, state_recv_t FROM devices_ctrl WHERE dev_locate='$device_id'" );
			$res = $res[0];
			
			switch( $res['ins'] ) {
				case 'NONE':
					$st = 'ON';
					$st_c = 1;
					break;
					
				default:
					$st = 'OFF';
					$st_c = 0;
					break;
			}

			echo '{
					"resp_desc" : "当前设备状态是"'.$st.',
					"resp_code" : "0",
					"data"      : {\"status\":\"'.$st_c.'\"}
				 }';
		}

		public function login( $student_no, $student_password ) {
			
			$response = $this->signInAndGetUser( $student_no, $student_password );
			$result['resp_desc'] = $response['resp_desc'];
			$result['resp_code'] = $response['resp_code'];
				
			if( $result['resp_code']>0 ) {
				$result['data'] = $response['data'];
				echo json_encode( $result );
			}
			elseif( $result['resp_code']==0 ) {
				//同步用户信息
				$user_map = $response['data']['userMap'];
				$row = $this->db->get_one( "select `id`,`studentNo`,`sex`,`phone`,`department`,`school_zone`,`from`,`graduated`,`home_address`,`nation`,`carrier_account`,`wash_setting`,`cardNo`,`userName`,`nickName`,`headImg`,`email`,`cardBalance`,`monthlyAmt`,`grade`,`major` FROM `user_info`  where studentNo='$student_no' limit 1" );
				//更新
				if( $user_map['cardNo'] ) {
					$user_info['cardNo'] = $user_map['cardNo'];
				}
					
				if( $user_map['userName'] ) {
					$user_info['userName'] = $user_map['userName'];
				}
					
				if( $user_map['nickName'] ) {
					$user_info['nickName'] = $user_map['nickName'];
				}
				
				if( $user_map['cardBalance'] ) {
					$user_info['cardBalance'] = $user_map['cardBalance'];
				}
				
				if( $user_map['monthlyAmt'] ) {
					$user_info['monthlyAmt'] = $user_map['monthlyAmt'];
				}
				
				if( $student_password ) {
					$user_info['password'] = $student_password;
				}
			
				if( $user_map['token'] ) {
					$user_info['card_token'] = $user_map['token'];
				}
	
				//加密token
				//$user_info["token"] = $user_map["token"];
				$auth_token = $student_no.'|>|'.$student_password.'|>|'.$user_map['token'];
				$encode_auth_token = $this->authcode( $auth_token, 'ENCODE', $config['hx_auth_key'] );		// $encode_auth_key 没有赋值
				$user_info['token'] = $encode_auth_token;
	
				if( $row ) {
					$condition = "studentNo='$student_no'";
					$query = $this->db->update( 'user_info', $user_info, $condition );
				}
				else {
					//插入
					$user_info['studentNo'] = $student_no;
					$user_info['headImg'] = $user_map['headImg'];
					$user_info['phone'] = $user_map['phone'];
					$user_info['email'] = $user_map['email'];
					$user_info['card_token'] = $user_map['token'];
					$query = $this->db->insert( 'user_info', $user_info );
				}
				
				if( $query ) {
					$sql = "SELECT `id`,`studentNo`,`sex`,`phone`,`department`,`school_zone`,`from`,`graduated`,`home_address`,`nation`,`carrier_account`,`wash_setting`,`cardNo`,`userName`,`nickName`,`headImg`,`email`,`cardBalance`,`monthlyAmt`,`grade`,`major`,`token` FROM `user_info` WHERE `studentNo`='$student_no'";
					$row = $this->db->get_one($sql);
					$result['resp_desc'] = '';
					$result['resp_code'] = '0';
					$result['data'] = $row;
					$result['stoken'] = $user_map['token']; 
					echo json_encode( $result );
				}
				else {
					$result['data'] = $response['data']['userMap'];
					echo json_encode( $result );
				}			
			}
			else {
				echo '{
					"resp_desc" : "一卡通通信失败,请重新登录",
					"resp_code" : "1666",
					"data"      : "{}"
				}';
			}	
		}

		public function get_card_transaction( $student_no, $token, $page_index=1, $page_size=10, $begin_date=0, $end_date=0 ) {
			$row = $this->db->get_one( "select card_token from user_info where studentNo = ".$student_no." limit 1" );
			$token = $row['card_token'];

			$response = $this->getCardTransaction( $student_no, $token, $page_index, $page_size, $begin_date, $end_date );
			echo json_encode( $response );
		}

		public function hand_lost( $student_no, $token, $card_no, $password, $opt_type ) {
			
			$row = $this->db->get_one( "select card_token from user_info where studentNo = ".$student_no." limit 1" );
			$token = $row['card_token'];
				
			if( empty($card_no) ) {
				$row = $this->db->get_one( "select cardNo from user_info where studentNo='".$student_no."' limit 1" );
				$card_no = $row['cardNo'];	
			}

			$response = $this->handLost( $student_no, $token, $card_no, $password, $opt_type );
			echo json_encode( $response );
		}

		public function get_subsidy_list( $student_no, $token, $page_index, $page_size, $begin_date, $end_date ) {
			$row = $this->db->get_one( "select card_token from user_info where studentNo = ".$student_no." limit 1" );
			$token = $row['card_token'];

			$response = $this->getSubsidyList( $student_no, $token, $page_index, $page_size, $begin_date, $end_date );
			echo json_encode( $response );
		}

		//充值
		public function recharge( $student_no, $token, $password, $money, $name, $type='' ) {
			$deposit_no = $student_no . date('YmdHis') . $type . rand(1000,9999);

			$row = $this->db->get_one( "select card_token from user_info where studentNo = ".$student_no." limit 1" );
			$card_token = $row['card_token'];

			$response = $this->tpdeposit( $student_no, $card_token, $deposit_no, $password, $money, $name );
			echo json_encode( $response );
		}

		public function buyElect($student_no, $room, $loudongId, $password,$trade_money){
			$school_id = $this->school_id;
			/**
			*先扣费,扣费成功就写日志,再买电
			*/
			//获取token
			$row= $this->db->get_one("select card_token from user_info where studentNo = ".$student_no." limit 1");
			$card_token=$row['card_token'];
			//一卡通扣费
			$trade_no = date("YmdHis").rand(1000,9999);
			//一卡通充费单位是分，电控系统是元
			$money=$trade_money/100;
			$post_data=array("body"=>array("studentNo"=>$student_no,"token"=>$card_token,"password"=>$password,
				"tradeBranchId"=>"0400002","tradeNo"=>$trade_no,"tradeMoney"=>$trade_money,"schoolId"=>$school_id));
			$response=$this->http_post_json("tptrade",json_encode($post_data));
			//一卡通付电费成功
			if($response['resp_code']=="0"){
				error_log("$student_no 购电 $trade_money 流水号 $trade_no \r\n",'3','buyelect.txt');
				//电控系统划电
				$sql="select room_id from kd_room where loudong_id=$loudongId and room=$room";
				$response=$this->http_get_json("http://10.71.29.33:8080/sims/msquery","$sql");
				$response=json_decode($response,true);
				$room_id=$response[0]['room_id'];
				$response =$this->http_get_json("http://10.71.29.33:8080/sims/msquery","insert into kd_tmp (buyer_id,xiaoqu_id,room_id,tranamt,endatatime,custsn) values ($student_no,'1',$room_id,$money,Getdate(),$trade_no)");
			//划电成功
				if($response==1){
					$response=json_encode(array("resp_desc"=>"充值成功","resp_code"=>"0","data"=>array("loudongId"=>$loudongId,"room"=>$room,"trade_money"=>$trade_money)));
					echo $response;
			}
				else{
					$response=json_encode(array("resp_desc"=>"充值失败","resp_code"=>"1099","data"=>""));
					echo $response;
				}
		}
			else{
				$response=json_encode(array("resp_desc"=>"充值失败,请退出登录后重试$resp_desc","resp_code"=>"1099","data"=>""));
                                        echo $response;
			}
	}

		//读取房间剩余电量
		public function readElect($room,$loudongId){
			$sql="select usedAmp,allAmp from kd_room where room=$room and loudong_id=$loudongId";
			$response=$this->http_get_json("http://10.71.29.33:8080/sims/msquery",$sql);
			$response=json_decode($response,true);
			$response[0]['allAmp']=floor($response[0]['allAmp']);
			$response[0]['usedAmp']=floor($response[0]['usedAmp']);
			$restElect=$response[0]['allAmp']-$response[0]['usedAmp'];
			$result=array("resp_desc"=>"查询成功","resp_code"=>"0","data"=>array("restElect"=>$restElect,"room"=>
				$room,"loudongId"=>$loudongId));
			echo json_encode($result);
		}

		 /**
		 * 验证配置接口信息
		 * @param array 从微信接口发送来的信息，通过$_POST获得
		 */
		public function interface_valid( $get_request ) {
				$signature = $get_request['sign'];
				$timestamp = $get_request['t'];
				$nonce = $get_request['n'];        

				$token = $this->token;
				$tmpArr = array( $token, $timestamp, $nonce );
				$tmpStr = md5(implode( $tmpArr ));
				$tmpStr = sha1( $tmpStr );
				//echo  $signature."||".$tmpStr;
				if( $tmpStr != $signature ){
					echo '{
						"resp_desc" : "鉴权失败",
						"resp_code" : "1098",
						"data"      : "{}"
					}';
				   exit;
				}else{
					return true;
				}
		}

		/*
			* 与一卡通通信接口
			* @param student_no 
		*/
		private function signInAndGetUser( $student_no, $password ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'password'=>$password,'schoolId'=>$school_id)) );
			$response = $this->http_post_json( 'signInAndGetUser',$post_data );
			//update token
			$token = $response['data']['userMap']['token'];
			if($token){
				$response = $this->http_post_json( 'signInAndGetUser', $post_data );
				//update token
				$token = $response['data']['userMap']['token'];
			}
			$this->token = $token;
			return $response;
		}
		
		
		/*
			* 消费记录查询
			* @param student_no 
		*/
		private function getCardTransaction( $student_no, $token, $page_index, $page_size, $begin_date, $end_date ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'token'=>$token,'pageIndex'=>$page_index,'pageSzie'=>$page_size,'beginDate'=>$begin_date,'endDate'=>$end_date,'schoolId'=>$school_id)) );
			$data = $this->http_post_json('getCardTransaction',$post_data);
			if( is_array($data['data']) ) {
				$data['data'] = $data['data']['list'];	
			}
			else {
				$data['data'] = array();
				$data['resp_code'] = '0';
				$data['resp_desc'] = '暂无消费记录';
			}
			return $data;
		}

		/*
			* 充值记录查询
			* @param student_no 
		*/
		private function getSubsidyList( $student_no, $token, $page_index, $page_size, $begin_date, $end_date ) {
			$school_id = $this->school_id;
			$card_token=$this->db->getone("select card_token from user_info where studentNo = ".$student_no." limit 1");
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'token'=>$card_token,'pageIndex'=>$page_index,'pageSzie'=>$page_size,'beginDate'=>$begin_date,'endDate'=>$end_date,'schoolId'=>$school_id)) );
			var_dump($post_data);
			$data = $this->http_post_json( 'getSubsidyList', $post_data );
			if( is_array($data['data']) ) {
				$data['data'] = $data['data']['list'];	
			}

			return $data;
		}

		/*
			* 充值交易
			* @param student_no 
		*/
		private function tpdeposit( $student_no, $token, $deposit_no, $password, $money, $name ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'token'=>$token,'password'=>$password,'depositNo'=>$deposit_no,'depositMoney'=>$money,'schoolId'=>$school_id)) );
			$data = $this->http_post_json( 'tpdeposit', $post_data );
			$sign = md5( $deposit_no.$summary.$trade_money.$school_id.$this->config['pay_token'] );
			if( $data['resp_code']==0 ) {
				$trade_money = number_format( (intval( $money)/100), 2 );
				$summary = '充值' . $trade_money . '元';
				$data['data'] = array(
					'order_no'=>$deposit_no,
					'summary'=>$summary,
					'amount'=>$money,
					'school_code'=>$school_id,
					//'school_account'=>$this->config['school_account'],
					'schoole_code'=>$student_no,
					'name'=>$name,
					'sign'=>$sign
				);

			}
			return $data;
		}

		/*
			* 消费交易
			* @param student_no 
		*/
		private function tptrade( $student_no, $token, $trade_no, $password, $trade_branch_id,  $trade_money ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'token'=>$token,'password'=>$password,'tradeBranchId'=>$trade_branch_id,'tradeNo'=>$trade_no,'tradeMoney'=>$trade_money,'schoolId'=>$school_id)) );
			$data = $this->http_post_json( 'tptrade',$post_data );
			return $data;
		}

		/*
			* 消费交易
			* @param student_no 
		*/
		private function handLost( $student_no, $token, $card_no, $password, $opt_type ) {
			$school_id = $this->school_id;
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'cardNo'=>$card_no,'token'=>$token,'password'=>$password,'optType'=>$opt_type,'schoolId'=>$school_id)) );
			$data = $this->http_post_json( 'handLost',$post_data );
			return $data;
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
		
		//get 传输数据
		private function http_get_json($getAddress,$sql){
		$sql=urlencode($sql);
		$url=$getAddress.'?sql='.$sql;
		$data=file_get_contents($url);
		return $data;
		}
		//与智控设备通信
		private function send_message( $message ) {
			
			if( !$this->socket ) {
				$response = "erreur : $errno - $errstr<br />n";
			}
			else{
				stream_set_timeout( $this->socket, 3 );
				fwrite( $this->socket, $message );
				//stream_set_blocking( $this->socket, 1 );
				$response = fread( $this->socket, 1024 );
				//stream_set_blocking( $this->socket, 0 );
			}
			return $response;
		}

		//智控设备编码解码
		private function parse_device_by_device_id( $device_id ) {
			preg_match( '/^([HJ])([A-Z0-9])([A-Z0-9])([A-Z0-9])([A-Z0-9])([A-Z0-9])$/', $device_id, $matchs );
			if( $matchs[1]=='J' ) {				//表明是J
				$result['building'] = 'J';
				$result['floor'] = $matchs[2];
				if( $matchs[4]=='L' ) {				//洗衣机
					$result['device_type'] = '2';
					$slide = $matchs[7] == 1? '左边':'右边';
					$result['room'] = $matchs[3].'楼';
					$result['device_desc'] =  $result['floor'].'栋'.$matchs[3].'楼'.$slide.'洗衣机';          
				}
				else {
					$result['device_type'] = '1';
					$result['room'] = $matchs[3].$matchs[4].$matchs[5];
					$result['device_desc'] = $result['floor'].'栋'.$matchs[4].$matchs[5].$matchs[6].'热水器';
				}
			}
			elseif( $matchs[1]=='H' ) {
				$result['building'] = 'H';
				$result['floor'] = $matchs[2];
				$room = hexdec($matchs[3]) - 9;
				$room_id = $room.'单元'.$matchs[4].'0'.$matchs[5];
				$result['room'] = $matchs[4].'0'.$matchs[5];
				if( $matchs[6]=='A' ) {						//洗衣机
					$result['device_type'] = '2';
					$result['device_desc'] = $result['floor'].'栋'.$room_id.'洗衣机';
				}
				else {
					$result['device_type'] = '1';
					$result['device_desc'] = $result['floor'].'栋'.$room_id.'热水器';
				}
			}
			//print_r($matchs);
			return $result;
		}

		/** 
		* $string 明文或密文 
		* $operation 加密ENCODE或解密HX_DECODE 
		* $key 密钥 
		* $expiry 密钥有效期 
		*/ 
		public function authcode( $string, $operation='HX_DECODE', $key='', $expiry=0 ) { 
			// 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙 
			// 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。 
			// 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方 
			// 当此值为 0 时，则不产生随机密钥 
			$ckey_length = 4; 
			
			// 密匙 这里可以根据自己的需要修改 
			$key = md5($key); 
			
			// 密匙a会参与加解密 
			$keya = md5(substr($key, 0, 16)); 
			// 密匙b会用来做数据完整性验证 
			$keyb = md5(substr($key, 16, 16)); 
			// 密匙c用于变化生成的密文 
			$keyc = $ckey_length ? ($operation == 'HX_DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : ''; 
			// 参与运算的密匙 
			$cryptkey = $keya.md5($keya.$keyc); 
			$key_length = strlen($cryptkey); 
			// 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，解密时会通过这个密匙验证数据完整性 
			// 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确 
			$string = $operation == 'HX_DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string; 
			$string_length = strlen($string); 
			$result = ''; 
			$box = range(0, 255); 
			$rndkey = array(); 
			// 产生密匙簿 
			for($i = 0; $i <= 255; $i++) { 
				$rndkey[$i] = ord($cryptkey[$i % $key_length]); 
			} 
			// 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上并不会增加密文的强度 
			for($j = $i = 0; $i < 256; $i++) { 
				$j = ($j + $box[$i] + $rndkey[$i]) % 256; 
				$tmp = $box[$i]; 
				$box[$i] = $box[$j]; 
				$box[$j] = $tmp; 
			} 
			// 核心加解密部分 
			for($a = $j = $i = 0; $i < $string_length; $i++) { 
				$a = ($a + 1) % 256; 
				$j = ($j + $box[$a]) % 256; 
				$tmp = $box[$a]; 
				$box[$a] = $box[$j]; 
				$box[$j] = $tmp; 
				// 从密匙簿得出密匙进行异或，再转成字符 
				$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256])); 
			} 
			if($operation == 'HX_DECODE') { 
			// substr($result, 0, 10) == 0 验证数据有效性 
			// substr($result, 0, 10) - time() > 0 验证数据有效性 
			// substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16) 验证数据完整性 
			// 验证数据有效性，请看未加密明文的格式 
				if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 	16)) { 
					return substr($result, 26); 
				} else { 
					return ''; 
				} 
			} else { 
				// 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因 
				// 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码 
				return $keyc.str_replace('=', '', base64_encode($result)); 
			} 
		} 
	}
?>
