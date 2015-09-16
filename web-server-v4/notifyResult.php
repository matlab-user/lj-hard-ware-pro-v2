<?php
	header("Content-type:text/html;charset=utf-8");
	if(isset($_POST['order_no'])&&isset($_POST['amt'])&&isset($_POST['signature'])){
		$msg=$_POST['order_no'].' '.$_POST['amt'].' '.$_POST['signature']."\r\n";
		error_log($msg,3,'biil.log');
		echo 'result=00';
	}

?>


