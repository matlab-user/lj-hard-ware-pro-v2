<?php
	header( 'Content-type:text/html;charset=utf-8' );
	
	require_once( 'logger.php' );
	
	$log = new logger();
	$log->write('this is content', 'test');
?>
