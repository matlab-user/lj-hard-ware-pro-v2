<?php

	// 用于验证微信接口配置信息的Token，可以任意填写
	$config['api_server_url'] = 'http://10.71.29.13:8080/service.do'; 			// 一卡通接口地址;
		
	$config['token'] = '07A4A8DAC4D7C27AFF893F2208B0D60B'; 
	$config['school_id'] = '13816'; 											// 学校ID
	$config['echostr'] = '权限验证来源';

	$config['dbhost'] = 'localhost'; 											// 数据库主机
	$config['dbuser'] = 'root'; 											// 数据库用户名
	$config['dbpwd'] = 'scgkapp'; 												// 数据库用户名密码
	//$config['dbpwd'] = 'blue';
	$config['database'] = 'yunkadb'; 											// 数据库名

	$config["build_map"] = array("J"=>"娇子","H"=>"鸿儒");
	$config["build_device_name"] = array("1"=>"热水器","2"=>"洗衣机","3"=>"公用设备");

	$config["build_device_icon"] = array("1"=>"http://10.71.29.51/card/images/shower.png","3"=>"http://10.71.29.51/card/images/shower.png","2"=>"http://10.71.29.51/card/images/washer.png");

	$config["token_invaild"] = "1666";//登录失败的错误码
	$config["token_invaild_str"] = "登录令牌无效";//登录失败的定位码
	$config["default_img"] = "http://10.71.29.51/card/images/header.png";//默认头像
	$config["carrier_account"]='[{
						"carrier_name":"中国移动",
						"carrier_id":"1",
						"carrier_package":[{"package_name":"移动宽带版48元","package_id":"1","package_info":"移动校园云卡宽带版48元套餐"},{"package_name":"移动宽带版68元","package_id":"2","package_info":"移动校园云卡宽带版68元套餐"},{"package_name":"移动全能版58元","package_id":"3","package_info":"移动校园云卡全能版58元套餐"},{"package_name":"移动全能版78元","package_id":"4","package_info":"移动校园云卡全能版78元套餐"}]
					}]';//运营商套餐

	$config["hx_auth_key"] = "C27AFF893F2207A4A8DAC4D708B0D60B";//token明文传输的时候的加密秘钥
?>
