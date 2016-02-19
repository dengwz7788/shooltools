<?php
return array(
	//'=>'
	'LAYOUT_ON'=>true,
	'LAYOUT_NAME'=>'Layout/layout',
		
	/* */
	'DB_TYPE'               => 'mysql',     // 
	'DB_HOST'               => 'localhost', // 
	'DB_NAME'               => 'cjcx',          // 
	'DB_USER'               => 'root',      // 
	'DB_PWD'                => '',
	'DB_PORT'               => '3306',        // 
	'DB_PREFIX'             => '',    // 
	'URL_ROUTER_ON' => true, //URL
	'URL_MODEL'      => 2,
	'SERVER_URL'      => $_SERVER['SERVER_NAME']."__APP__/index.php/Index/index",
	//'School_url' => 'http://125.89.69.234/',
	'School_url' => 'http://www.hut.edu.cn:83/',
	//'School_url'=>'http://202.116.160.170/',
	
	//URL
	'Get_garde'=>'xscjcx_dq.aspx?xh=',
	//'Get_garde'=>'xscj_gc.aspx?xh=',
	
	//
	'Get_info'=>'xsgrxx.aspx?xh=',
		
	//课表
	'Get_schedule'=>'tjkbcx.aspx?xh=',
);
?>