<?php
/*
	web端指令格式为：[web,operate,dev_id]
	其中，OPEN CLOSE操作已经事先写入数据库中
	
	收到web端指令，立即返回 [web,GOT]
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
	
	class heart_beat {
		public $st_arr;								// 之前的状态值,应该从数据库中确定状态，先默认为全关
		public $cur_st_arr;							// 当前的状态值
		public $recv_t = 0;
		
		public function __construct() {
			$this->recv_t = time();
			$this->st_arr[0] = '0';
		}
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
	$case_stat = array();
	
	
	$dev_ids = array();			// 记录接收到的所有控制指令涉及的控制板id
	$db = new db( $config );
	$sql_res = $db->get_all( 'SELECT DISTINCT ctrl FROM devices_ctrl' );
	$db->close();
	
	foreach( $sql_res as $v )
		$dev_ids[] = $v['ctrl'];
/*	
	$s1 = microtime( true );
	foreach( $dev_ids as $v ) {
		read_hw_state( $v );
	}
	echo "cost ".(microtime(true)-$s1)." s\r\n";
	exit;
*/	
    while( 1 ) {
		
		$send_ins_dev_id = array();
		
		$read = gen_sock_chain( $sock_ids, $sock );
		
        socket_select( $read, $write=NULL, $except=NULL, 5 );
		
		$s1 = microtime( true );
		
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
					
					if( $one_client_order[0]->id=='web' ) {		// 仅处理来自web的指令，真正命令发送在 后面的 check_db 函数  
						$send_ins_dev_id[] = substr( $one_client_order[0]->dev_id, 0, 3 );	
						$buff = "[web,GOT]";
						socket_write( $read_sock, $buff );
					}
					else {		// 仅处理硬件事务，以控制箱为单位
						pro_ins( $one_client_order[0], $read_sock );
					}		
					
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
		
		// 主要处理web控制指令（实际发送控制指令）
		$send_ins_dev_id = array_unique( $send_ins_dev_id );
		echo "web ins: ".count($send_ins_dev_id)."\r\n";

		if( count($send_ins_dev_id)>4 )
			$send_ins_dev_id = array_slice( $send_ins_dev_id, 0, 4 );
		if( count($send_ins_dev_id)>0 )
			check_db( $send_ins_dev_id );
		
		// 轮询除 web 控制外的其它控制箱
		$mid = array_diff( $dev_ids, $send_ins_dev_id );
		echo "rest cases: ".count($mid)."\r\n";
		if( count($mid)>0 )
			check_db( $mid );
		
		echo "case state num: ".count($case_stat)."\r\n";
		echo "cost ".(microtime(true)-$s1)." s\r\n";
		
		// 检查清理 socket 超时（不操作数据库，不发送指令）
		clear_timeout_socket( $sock_ids );
		//echo "\t\tafter clear sockets num:".count($sock_ids)."\r\n";
	}
	
	socket_close( $sock );

?>