<?php

	require_once( 'config.php' );
	require_once( 'db.php' );
	
	$config['pay_token'] = 'zhaxpaycgi';
	
	//$str = '{"resp_desc":"\u4ea4\u6613\u5931\u8d25","resp_code":"1099","data":"\u7528\u6237\u5b66\u53f7201421080121\u6d88\u8d39\u4ea4\u6613[\u6d88\u8d39\u91d1\u989d\uff1a100\u5206,\u6d88\u8d39\u65f6\u95f4\uff1a2015-09-14 12:26:50]\u5931\u8d25,\u5931\u8d25\u539f\u56e0\uff1a[[\u4e00\u5361\u901a\u63a5\u53e3-\u6d88\u8d39\u4ea4\u6613]\u901a\u8fc7\u5b66\u53f7<201421080121>\u9a8c\u8bc1\u4e00\u5361\u901a\u8d26\u6237\u5bc6\u7801\u5931\u8d25,\u8f93\u5165\u5bc6\u7801\u53ef\u80fd\u9519\u8bef.]"}';
	//$str = '{"resp_desc":"\u767b\u5f55\u5931\u8d25","resp_code":"1098","data":"\u9274\u6743\u5931\u8d25,[\u4e00\u5361\u901a\u63a5\u53e3-\u767b\u9646]\u901a\u8fc7\u5b66\u53f7<201520152015>\u9a8c\u8bc1\u4e00\u5361\u901a\u8d26\u6237\u5bc6\u7801\u5931\u8d25,\u8f93\u5165\u5bc6\u7801\u53ef\u80fd\u9519\u8bef."}';
	
	//var_dump( json_decode( $str, true ) );
	//exit;
	//$db = new db( $config );
	//$str = '201520152015201509131846314782+0.01'.'+'.$config['pay_token'];
	//echo md5( $str )."\r\n";
	//exit;
	
	// MD5(订单号 + 摘要 + 金额 + 学校编号 + 签名KEY)
/*	
	$json = '{"summary":"50.00","amt":"50.0","name":"\u6D4B\u8BD5\u5E10\u62372","school_account":"201520152015","order_no":"201520152015201509131547418931"}';
	$signature = '3d3a9f25feb46ad5720355e0ac1f27da';
	$j = json_decode( $json, true );
	
	$str = $j['order_no'].$j['amt'].$config['pay_token'];
	echo $str.'     '.md5( $str )."\r\n";
	exit;
*/	

/*	
	echo "open device  ".date("Y-m-d H:i:s").'              '.time()."\r\n";
	$data['student_no'] = '210520152015';
	$data['ins'] = 'OPEN';
	$data['ins_recv_t'] = time() + 10;
	$db->update( 'devices_ctrl', $data, "dev_id=00101" );
	
	sleep( 17 );
	echo "close device  ".date("Y-m-d H:i:s").'                '.time()."\r\n";
	$data['ins'] = 'CLOSE';
	$data['ins_recv_t'] = time() + 14;
	$db->update( 'devices_ctrl', $data, "dev_id=00101" );
	exit;	
*/

	// login
	$str = '{"sign":"7b3233c9f22739adf77c9513a762858a467b233b","n":"153807","action":"login","stu_no":"201520152015","t":"20150902153807","password":"333333"}';
	$request_result = http_post_json( $str );
	
	//$str = '{"sign":"7b3233c9f22739adf77c9513a762858a467b233b","n":"153807","action":"getCardTransaction","stu_no":"201520152015","t":"20150902153807","page_index":"1","page_szie":"10","begin_date":"20150204010101","end_date":"20150211010101","type":"zhichu"}';
	//$request_result = http_post_json( $str );
	//exit;
	
	$str = '{
			  "action" : "openShower",
			  "stu_no" : "201520152015",
			  "t" : "1440489421",
			  "device_id" : "J61051",
			  "time" : 300,
			  "delay_close" : 5,
			  "delay_open" : 0,
			  "n" : "717733",
			  "sign" : "3960d1f4ff1fd7fa3c8894e3389d52759b0d26db",
			  "token" : "822bb2juldeXDtYGvNT6hH1rUUD2Jtrx\/hJC2mJfO\/DXdHkPKUgsvwmq1m6dG+MxSiVTBfPX3BYVZg3lp5DeXtE9yerXXwPPUHRrnRGiXW0TsxOqV0wzb44LXQiUx1Sr0g"
			}';

	http_post_json( $str );

	sleep( 17 );
	echo "close the shower\r\n";
	$str = '{
	  "action" : "closeShower",
	  "stu_no" : "201520152015",
	  "t" : "1440489421",
	  "device_id" : "J61051",
	  "time" : 300,
	  "delay_close" : "0",
	  "delay_open" : 0,
	  "n" : "717733",
	  "sign" : "3960d1f4ff1fd7fa3c8894e3389d52759b0d26db",
	  "token" : "822bb2juldeXDtYGvNT6hH1rUUD2Jtrx\/hJC2mJfO\/DXdHkPKUgsvwmq1m6dG+MxSiVTBfPX3BYVZg3lp5DeXtE9yerXXwPPUHRrnRGiXW0TsxOqV0wzb44LXQiUx1Sr0g"
	}';
	
	$res = http_post_json( $str );
	exit;

//-----------------------------------------------------------------------------------------------------------------------
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