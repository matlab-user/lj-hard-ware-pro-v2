<?php

	require_once( 'config.php' );
	//require_once( 'api.php' );
		
	//$api = new cardApi( $config );

	//login
	//$str = '{"sign":"7b3233c9f22739adf77c9513a762858a467b233b","n":"153807","action":"login","stu_no":"201520152015","t":"20150902153807","password":"111111"}';
	//http_post_json( $str );
	
	// getDeviceList
	//$str = '{"sign":"74accf498f9fd695084b61b29a4d59b990f27fe7","n":"153801","action":"getDeviceList","stu_no":"201520152015","t":"20150902153801","token":"20152015201511D1C0D64AA326B9CC95B76FC7A1DA67"}';
	//http_post_json( $str );
	//exit;

	
	
/*
{
  "action" : "getDeviceList",
  "stu_no" : "201520152015",
  "n" : "ac5067",
  "sign" : "7cf735feba19f6d128a457edf57886f2dceca35a",
  "token" : "cef6xc41UZC\/xjvK4r61E+lqVTR+A227j3eB7yeZNFuF63RXg6mRtRiJwW\/N4rjHw03POhonZiasuCQ0fFqtp5\/SCPkeL\/aLnQkx1imOIb5oH9LR584eNlV+DqN02L5ppw",
  "t" : "1441171538"
}
		$student_no = '201520152015';
		$student_password = '111111';
		$post["token"] = "cef6xc41UZC\/xjvK4r61E+lqVTR+A227j3eB7yeZNFuF63RXg6mRtRiJwW\/N4rjHw03POhonZiasuCQ0fFqtp5\/SCPkeL\/aLnQkx1imOIb5oH9LR584eNlV+DqN02L5ppw";
		
		$auth_token = $student_no."|>|".$student_password."|>|".$post["token"];
		$encode_auth_token = $api->authcode( $auth_token, "ENCODE", $config['hx_auth_key'] );
		$user_info["token"] = $encode_auth_token;
		echo $encode_auth_token."\r\n";
		
		$authcode = $api->authcode( $encode_auth_token, 'HX_DECODE', $config['hx_auth_key'] );
		echo "$authcode\r\n";
		
		exit;
*/			
//		$post["token"] = "cef6xc41UZC\/xjvK4r61E+lqVTR+A227j3eB7yeZNFuF63RXg6mRtRiJwW\/N4rjHw03POhonZiasuCQ0fFqtp5\/SCPkeL\/aLnQkx1imOIb5oH9LR584eNlV+DqN02L5ppw";
//		$authcode = $api->authcode( $post["token"], 'HX_DECODE', $config['hx_auth_key']='' );
//		echo "$authcode\r\n";
//		exit;
	
/*
		$ta = array( $config['token'], "1441175367", "a69224" );
		$ta = md5( implode($ta) );
		$sign = sha1( $ta );
		echo $sign."\r\n";
		exit;


	
	$str = '{
		  "action" : "getDeviceList",
		  "stu_no" : "201521040196",
		  "n" : "85ae8f",
		  "sign" : "c5f2e02331b878eb076156e1516ac2c5c0995cfb",
		  "token" : "201521040196AE22A25AE01824BD061C423436DF3089",
		  "t" : "1440482594"
		}';
	
*/
	$str = '{
			  "action" : "openShower",
			  "stu_no" : "201520152015",
			  "t" : "1440489421",
			  "device_id" : "J61021",
			  "time" : 300,
			  "delay_close" : "0",
			  "delay_open" : 0,
			  "n" : "717733",
			  "sign" : "3960d1f4ff1fd7fa3c8894e3389d52759b0d26db",
			  "token" : "822bb2juldeXDtYGvNT6hH1rUUD2Jtrx\/hJC2mJfO\/DXdHkPKUgsvwmq1m6dG+MxSiVTBfPX3BYVZg3lp5DeXtE9yerXXwPPUHRrnRGiXW0TsxOqV0wzb44LXQiUx1Sr0g"
			}';

		
	http_post_json( $str );
	
	sleep( 10 );
	$str = '{
	  "action" : "closeShower",
	  "stu_no" : "201520152015",
	  "t" : "1440489421",
	  "device_id" : "J61021",
	  "time" : 300,
	  "delay_close" : "0",
	  "delay_open" : 0,
	  "n" : "717733",
	  "sign" : "3960d1f4ff1fd7fa3c8894e3389d52759b0d26db",
	  "token" : "822bb2juldeXDtYGvNT6hH1rUUD2Jtrx\/hJC2mJfO\/DXdHkPKUgsvwmq1m6dG+MxSiVTBfPX3BYVZg3lp5DeXtE9yerXXwPPUHRrnRGiXW0TsxOqV0wzb44LXQiUx1Sr0g"
	}';
	
	$res = http_post_json( $str );

	//$res[1] = '{ "resp_desc" : "计费成功","resp_code" : "0","data":{"fee_rate":"0.3元/分钟","time":"0.15分钟","total_fee":"0.05元"}}';

	//var_dump( json_decode( $res[1] ) );
	
	exit;
/*
	http_post_json( '{
						"password" : "111111",
						"stu_no" : "201520152015",
						"t" : "1439825959",
						"n" : "f0bdff",
						"sign" : "a606396017e1fb72c3f7ff004e81cbda9c3f7a05",
						"action" : "login"
					}' );


	$str = '{
			  "action" : "getVersionInfo",
			  "stu_no" : "201521040196",
			  "n" : "47fc7a",
			  "sign" : "3d5f93f13ffe1d1f7cbda266906b83806e96bb1b",
			  "token" : "2015210401964035FDA0E6147C304B021C4CE9283B00",
			  "t" : "1440478013"
			}';
	
	$str = str_replace("\\\"", "'", $str);
	$post = json_decode( $str, true );
	$api->interface_valid( $post );

	$action = $post["action"];
	$post["token"] = str_replace(" ","+",$post["token"]);
	
	//authcode 的 加密 在 login方法里面，验证了一卡通的账号有效性后实现
	$authcode = authcode( $post["token"], 'HX_DECODE', $config['hx_auth_key'] );
	
	echo "$authcode\r\n";
		
	//token的组合规则 stu_no|>|password|>|token
	//$auth = explode("|>|", $authcode);
	//$stu_no = $auth[0];
	//$password = $auth[1];
	//$token = $auth[2];
*/

/*
	{
	  "action" : "getDeviceList",
	  "stu_no" : "201521040196",
	  "n" : "ae2bbf",
	  "sign" : "e4a1a638f77b5d381ebdaf75fef351c61219e9b1",
	  "token" : "201521040196B8BB471A0B2873590FFA1000B7088346",
	  "t" : "1440478015"
	}
*/
/*
	$ta = array( $config['token'], '1440478013', '47fc7a' );
	$ta = md5( implode($ta) );
	$sign = sha1( $ta );
	echo $sign."\r\n";
*/	
/*
	$post['token'] = '201521040196B8BB471A0B2873590FFA1000B7088346';
	$authcode = authcode( $post['token'], 'HX_DECODE', $config['hx_auth_key'] );
	var_dump( $authcode );
*/	
//---------------------------------------------------------

	//authcode的组合规则 stu_no|>|password|>|token	
	//$auth = explode( "|>|", $authcode );
	//$stu_no = $auth[0];
	//$password = $auth[1];
	//$token = $auth[2];
	
	/*		
	$stu_no = '201520152015';
	$passwd = '1111111';
	
	// 'f75ddaaeeeea79acf2fe1aa7200bf8c0f1ebc670'
	$ta = array( $config['token'], $stu_no, $passwd );
	$ta = md5( implode($ta) );
	$sign = sha1( $ta );
	
	*/

	function http_post_json( $jsonStr ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		//curl_setopt( $ch, CURLOPT_URL, "http://218.6.163.88:50000/card/service.php" );
		curl_setopt( $ch, CURLOPT_URL, "http://127.0.0.1/web-server/service.php" );
		//curl_setopt( $ch, CURLOPT_URL, "http://10.71.29.51:50000/service.php" );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $jsonStr );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json; charset=utf-8',
													 'Content-Length: '.strlen($jsonStr),
													 'resTime'=>time() ) 
					);
					
		$response = curl_exec( $ch );
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$request_result = array($httpCode, $response);
		
		$order = array("\r\n", "\n", "\r", "\t"," ");
		print_r( str_replace($order,'',$request_result) );
		//$result = $this->parse_card_data( $request_result );
		return $request_result;
	}
	
	
//$api->bind_device_by_qrcode("201520152015","J65011");
//2015201520155D324367938AC9BCF93AE9BA6C19D0FD

//$api->oprate_device("J65011","OPEN");echo "sadf";

//$api->read_device_status("201520152015",'J65011');

//$api->login("201520152015", "111111");//{("000020300032","J65011");
//exit;20152705012420F7663B6DFDCEEC34C9B6656BB1E60A

/*http_post_json('{
  "stu_no" : "201520152015",
  "token" : "3a8bBpTxJifllJm+uj9I3omQb42wgGuXVZ2AZnfJIciQjMg6GUgtfcT84gacgDssBk9UQS3cB+d8OIx49uyNd3pOpqv2Ko3HeIYkGLTFlgEZSg2rZg5Q1m8KFnn20xneVQ",
  "device_id":"J61051",
  "time" :20,
  "delay_open":0,
  "delay_close":0,
  "t" : "1439825959",
  "n" : "f0bdff",
  "sign" : "a606396017e1fb72c3f7ff004e81cbda9c3f7a05",
  "action" : "closeShower"
}');*/
/*http_post_json('{
  "password" : "111111",
  "token":"000020300032439534BE304B925FD4089D69116C5205",
  "stu_no" : "201527050124",
  "page_index":1,
  "page_szie":10,
  "begin_date":"20150101000000",
  "end_date":"20150801000000",
  "t" : "1439825959",
  "n" : "f0bdff",
  "sign" : "a606396017e1fb72c3f7ff004e81cbda9c3f7a05",
  "action" : "getCardTransaction"
}');


*/



?>