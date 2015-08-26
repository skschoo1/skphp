<?php
return array(
	'load_ext_config' => 'db',
		
	/* URL设置 */
	'url_model'             =>  'pathinfo', 	// URL访问模式（ pt 、 pathinfo 、 rewrite ）
	
		
	/** rewrite模式去掉注视测试
	'url_pathinfo_depr'     =>  '-',	 	// URL访问模式下，各参数之间的分割符号
	'url_route_rules' => array(
			'rewrite-:p'=>'index/index/rewrite',
			'yzm'=>'index/index/yzm'
	),*/
		
	/* 模板解析设置 */
	'tmpl_parse_string'		=> array(
		'__PUBLIC__'			=> '/public',
	),
		
);

