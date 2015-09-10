<?php

	require_once( 'config.php' );
	//require_once( 'api.php' );
		
	//$api = new cardApi( $config );
/*
	//login
	$str = '{"sign":"7b3233c9f22739adf77c9513a762858a467b233b","n":"153807","action":"login","stu_no":"201520152015","t":"20150902153807","password":"111111"}';
	$request_result = http_post_json( $str );
	

	$str2 = substr( $request_result[1], 3 );
	$data = json_decode( $str2 );

	$token = $data->stoken;
	echo "$token\r\n";
	
	// getDeviceList
	//$str = '{"sign":"74accf498f9fd695084b61b29a4d59b990f27fe7","n":"153801","action":"getDeviceList","stu_no":"201520152015","t":"20150902153801","token":"20152015201511D1C0D64AA326B9CC95B76FC7A1DA67"}';
	//http_post_json( $str );
	
	// getSubsidyList
	$str = '{"sign":"74accf498f9fd695084b61b29a4d59b990f27fe7","page_index":"1","page_size":"10","begin_date":"20150908010101","end_date":"20150910010101","n":"153801","action":"getSubsidyList","stu_no":"201520152015","t":"20150902153801","token":"'.$token.'"}';
	http_post_json( $str );
	exit;
*/	
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
	echo "close the shower\r\n";
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
		curl_setopt( $ch, CURLOPT_URL, "http://218.6.163.88:50000/service.php" );
		//curl_setopt( $ch, CURLOPT_URL, "http://127.0.0.1/web-server/service.php" );
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

		/** 
	* $string 明文或密文 
	* $operation 加密ENCODE或解密HX_DECODE 
	* $key 密钥 
	* $expiry 密钥有效期 
	*/ 
	function authcode( $string, $operation='HX_DECODE', $key='', $expiry=0 ) { 
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



?>