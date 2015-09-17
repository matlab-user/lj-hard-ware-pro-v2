<?php

	echo count_shower_fee( 5*60+29 )."\r\n";
	echo count_shower_fee( 29 )."\r\n";
	echo count_shower_fee( 13*60 )."\r\n";
	
	// $t - 使用时间，单位：秒
	// 返回费用值，单位：分
	function count_shower_fee( $t ) {
		
		$fee = 0;
		
		if( $t>=600 ) {				// 大于10分钟
			$rest = $t - 600;
			$fee = 300;				// 10分钟共3元
			// 超出10分钟的，够5分钟收1元	
			$fee += floor( $rest/300 ) * 100;
			
		}
		else {						// 小于10分钟
			$fee = floor( $t/30 ) * 15;
		}	
		
		return $fee;
	}


?>