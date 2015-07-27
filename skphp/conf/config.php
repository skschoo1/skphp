<?php

/**
 * SKPHP惯例配置文件
 * 该文件请不要修改，如果要覆盖惯例配置的值，可在应用配置文件中设定和惯例不符的配置项
 * 配置名称大小写任意，系统会统一转换成小写
 * 所有配置参数都可以在生效前动态改变
 */

return  array (

	//加载配置文件
	'load_ext_config' => '',
	'load_ext_common' => '',

	/* 默认设定 */
	'default_module'        =>  'index',  			// 默认模块
	'default_controller'    =>  'index', 			// 默认控制器名称
	'default_action'        =>  'index', 			// 默认操作名称
	'default_charset'       =>  'utf-8', 			// 默认输出编码
	'default_timezone'      =>  'PRC',				// 默认时区
	'default_ajax_return'   =>  'JSON',  			// 默认AJAX 数据返回格式,可选JSON XML ...
	'default_jsonp_handler' =>  'jsonpReturn',		// 默认JSONP格式返回的处理方法
	'default_filter'        =>  'htmlspecialchars', // 默认参数过滤方法 用于I函数...
		
	/* 数据库设置 */
	'db_type'               =>  '',     		// 数据库类型
	'db_host'               =>  'locahost', 	// 服务器地址
	'db_name'               =>  '',     		// 数据库名
	'db_user'               =>  '',				// 用户名
	'db_pwd'                =>  '',				// 密码
	'db_port'               =>  '3306',			// 端口
	'db_prefix'             =>  '',				// 数据库表前缀
	
	/* URL设置 */
	'url_model'             =>  'rewrite', 	// URL访问模式（ pt 、 pathinfo 、 rewrite ）
	
	'url_pathinfo_depr'     =>  '/',	 	// PATHINFO模式下，各参数之间的分割符号
	'url_route_rules' => array(
		'test-:id'=>'index/index/index'
	),
	'url_html_suffix'       =>  'html',  	// URL伪静态后缀设置
	
	
	/* Cookie设置 */
	'cookie_expire'         =>  0,    	 // Cookie有效期
	'cookie_domain'         =>  '',      // Cookie有效域名
	'cookie_path'           =>  '/',     // Cookie路径
	'cookie_prefix'         =>  '',      // Cookie前缀 避免冲突
	'cookie_httponly'       =>  '',      // Cookie httponly设置
	
		
	'cache_prefix'         =>	'sk_cache_', //缓存文件名前缀
	'cache_time'           =>	1800,    	 //缓存时间默认1800秒
	'cache_mode'           =>	2,       	 //mode 1 为serialize ，model 2为保存为可执行文件
	
		
	/* 错误设置 */		
	'error_message'         =>  '页面错误！请稍后再试～',			// 错误显示信息,非调试模式有效
	'error_page' 			=> 'http://www.sk-school.com',	// APP_DEBUG关闭：定向到错误页面
	'show_error_msg'        =>  false,    					// 显示错误信息
	'trace_max_record'      =>  100,    					// 每个级别的错误信息 最大记录数
    'show_page_trace'      	=>	true,  						// 默认方法名次
		
);

