USE `yunkadb`;

CREATE TABLE IF NOT EXISTS hpay_table (
	`id` 			INT(11) NOT NULL AUTO_INCREMENT,
	`order_no`		CHAR(240) UNIQUE NOT NULL,				/* 订单唯一编号					 			*/
	`summary`		CHAR(240) DEFAULT '',					/* 交易摘要									*/	
	`amout` 		CHAR(24) NOT NULL,						/* 交易金额, 单位为元, 保留两位小数			*/
	`school_code` 	CHAR(24) NOT NULL DEFAULT '000',		/* 学校编号									*/
	`stu_no`		CHAR(24) NOT NULL DEFAULT '0000',		/* 学号										*/
	`name`			CHAR(24) NOT NULL,						/* 姓名										*/
	`sign_str`		CHAR(240) NOT NULL,						/* 数字签名									*/
	`if_pay` 		INT(2) NOT NULL DEFAULT 0,				/* 0 - 未支付  1 - 已支付					*/
	`pay_t` 		BIGINT NOT NULL DEFAULT 0,				/* 支付时间									*/
	`recv_t` 		BIGINT NOT NULL DEFAULT 0,				/* 收到安心付账单的时间					    */
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;