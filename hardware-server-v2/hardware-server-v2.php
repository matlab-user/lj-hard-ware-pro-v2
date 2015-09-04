<?php
/*
	web端指令格式为：[websend,operate,dev_id]
	其中，OPEN CLOSE操作已经事先写入数据库中
	
	收到web端指令，立即返回 [websend,GOT]
*/
	require_once( 'config.php' );
	require_once( 'net_pro.php' );
	
	class sock_info {
		public $sock = -1;
		public $lt = 0;
		public $id = '';					// 控制板编号, 或服务器连接编号
	}
	
	class order {
		public $id = '';
		public $op = '';
		public $state = '';
		public $dev_id = '';
	}
	
	pcntl_signal( SIGCHLD, SIG_IGN );
	date_default_timezone_set( 'Asia/Chongqing' );
	
	$l_ip = $config['ip'];
	$l_port = $config['port'];
		
	$sock = socket_create( AF_INET, SOCK_STREAM, 0 );
	socket_set_option( $sock, SOL_SOCKET, SO_RCVTIMEO, array("sec"=>3, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_SNDTIMEO, array("sec"=>3, "usec"=>0 ) );
	socket_set_option( $sock, SOL_SOCKET, SO_REUSEADDR, 1 );
	
	if( socket_bind($sock, $l_ip, $l_port)===FALSE ) {       		// 绑定 ip、port
		error_log( "hardware-server socket_bind failed!\t\t".date("Y-m-d H:i:s")."\r\n", 3, '/tmp/hardware-server.log' );
		exit;
	}
	
	socket_listen( $sock );      						// 监听端口
	echo "hareware_server is running!\t\t".date("Y-m-d H:i:s")."\r\n";
	
	$sock_ids = array();								// 对应每个连接进来的 sock
	$check_db_t = 0;
	
	$case_p = 0;
	
    while( 1 ) {
		
		$dev_ids = array();			// 记录接收到的所有控制指令涉及的控制板id
		
		$read = gen_sock_chain( $sock_ids, $sock );
		
        socket_select( $read, $write=NULL, $except=NULL, 5 );
		
        if( in_array($sock, $read) ) {
            $mid = new sock_info();
			$mid->sock = socket_accept( $sock );
			$mid->lt = time();
			$sock_ids[] = $mid;
			
            $key = array_search( $sock, $read );
            unset( $read[$key] );
        }

		// loop through all the clients that have data to read from
        foreach( $read as $read_sock ) {

            $data = socket_read( $read_sock, 1024*5, PHP_BINARY_READ );
			$key = search_sock( $sock_ids, $read_sock );
			$sock_ids[$key]->lt = time();

            // check if the client is disconnected
            if( $data===false ) {
				unset( $sock_ids[$key] );
				socket_close( $read_sock );

				echo "client disconnected!\t\t".date("Y-m-d H:i:s")."\r\n";	
                continue;
            }
			else {
				if( !empty($data) && strlen($data)>0 ) {				
					//echo "e1-\tclient send: ".$data."\t".date("Y-m-d H:i:s")."\r\n";
					// 处理接收到的指令
					$one_client_order = decode_order( $data );	
					if( $one_client_order[0]->id=='web' )				
						echo "\t\t".($one_client_order[0]->id).'--'.($one_client_order[0]->op).'--'.($one_client_order[0]->dev_id)."\r\n";
					
					if( empty($sock_ids[$key]->id) ) {				// 表明此socket是第一次发送数据
						$sock_ids[$key]->id = $one_client_order[0]->id;
						$sock_ids[$key]->sock = $read_sock;
						if( $sock_ids[$key]->id!='web' )
							error_log( "case-".$sock_ids[$key]->id." was online at\t".date('Y-m-d H:i:s')."\r\n", 3, 'error_log.txt' );
					}
												
					$sub_dev_ids = pro_ins( $one_client_order, $read_sock );
					$dev_ids = array_merge( $dev_ids, $sub_dev_ids );	
					unset( $one_client_order );	
				}
				else {
					socket_close( $read_sock );
					if( $sock_ids[$key]->id!='web' )
						error_log( "\tcase-".$sock_ids[$key]->id." was offline normally at\t".date('Y-m-d H:i:s')."\r\n", 3, 'error_log.txt' );
					unset( $sock_ids[$key] );
				}	
			}	
		}	
		
		$s1 = microtime( true );
		
		$dev_ids = array_unique( $dev_ids );
		if( count($dev_ids)>5 )
			$dev_ids = array_slice( $dev_ids, 0, 4 );
			
		if( $case_p==0 ) {
			$db = new db( $config );
			$sql_res = $db->get_all( 'SELECT DISTINCT ctrl FROM devices_ctrl' );
			$db->close();
		}	

		$dev_ids[] = $sql_res[$case_p]['ctrl'];
		$case_p++;
		
		if( $case_p>=count($sql_res) )
			$case_p = 0;
		
		$dev_ids = array_unique( $dev_ids );

		// 主要处理web控制指令（实际发送控制指令）
		if( count($dev_ids)>0 ) {
			//echo "\t\t\t\tcheck_db when ins recvied!\r\n";
			echo "\t\t".count( $dev_ids )."\r\n";
			check_db( $dev_ids );
		}
		echo "cost ".(microtime(true)-$s1)." s\r\n";
		
		// 检查清理 socket 超时（不操作数据库，不发送指令）
		clear_timeout_socket( $sock_ids );
		//echo "\t\tafter clear sockets num:".count($sock_ids)."\r\n";
	}
	
	socket_close( $sock );

?>