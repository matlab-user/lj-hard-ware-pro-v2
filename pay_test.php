<?php 

	$link=mysql_connect("localhost","root","scgkapp");
	$sql="use yunkadb";
	mysql_query($sql,$link);

	$sql="select distinct student_no from fee_record where fee_flag='0'";
	$res=mysql_query($sql);
	while(mysql_fetch_assoc($res)){
		$mid = mysql_fetch_assoc($res);
		$row[]=$mid['student_no'];
	}

	$arr=array();
	foreach ($row as $key => $value) {
		$sql="select password from user_info where studentNo=$value";
		$tempRow=mysql_query($sql);
		$tempRow = mysql_fetch_assoc( $tempRow );
		$arr[$value]=$tempRow['password'];
	}

	foreach ($arr as $key => $value) {
		$post=signInAndGetUser($key,$value);
		if( isset($post['body']['data']['userMap']['cardBalance']) )
			$money=$post['body']['data']['userMap']['cardBalance'];
		else
			$money = $post['body']['resp_desc'];

		echo $key."\t".$money."\r\n";

	}


	function signInAndGetUser( $student_no, $password ) {
			$school_id ="13816";
			$post_data = json_encode( array('body'=>array('studentNo'=>$student_no,'password'=>$password,'schoolId'=>$school_id)) );
			$response = http_post_json( 'signInAndGetUser', $post_data );

			return $response;
		}

	function http_post_json( $key, $jsonStr ) {
			
		  $ch = curl_init();
		  curl_setopt( $ch, CURLOPT_POST, 1 );
		  curl_setopt( $ch, CURLOPT_URL, 'http://10.71.29.13:8080/service.do'.'?key='.$key );
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
		  $response=json_decode($response,true);
		  return $response;
		}
?>