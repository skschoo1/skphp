<?php
// +-------------------------------------------------------------------
// | SKPHP [ Because of love, we get together ]
// +-------------------------------------------------------------------
// | Copyright (c) 2012-2016 http://sk-school.com All rights reserved.
// +-------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +-------------------------------------------------------------------
// | Author: seven <seven@sk-school.com>
// +-------------------------------------------------------------------
// | Knowledge change destiny, share knowledge change you and me.
// +-------------------------------------------------------------------
// | To be successful
// | must first learn To face the loneliness,who can understand.
// +-------------------------------------------------------------------

//----------------------------------
// 模块[index]  控制器[index]
//----------------------------------

class indexController extends Controller {
        
       
        public function index() {

        	
        	// 一、GET取值
        	// echo $_GET['id']; 
        	 
        	
        	
        	/** 
        	 *  二、SQL CURL 原生SQL，DB仿TP（M方法）开发中...
        	 * 
	        	$list = DB()->query("select * from yqp_goods_brand ");
	        	p($list);
        	 */
			
        	
        	
        	/**
        	 * 三、数据缓存读取
        	 *
	        	$status = WC('aaa', $list);			// 写入缓存
	        	$arr_get_cache = WC('aaa');			// 读取缓存
	        	print_r($arr_get_cache);
        	*/
        	
        	

        	/**
        	 * 四、实例化数据模型
	         *
	        	$M = M('index');        		//示例化index模型
	        	$data1 = $M->test(); 		//调用index模型中 test()方法
	        	$data2 = $M->test_db(); 	//调用index模型中 test_db()方法 SELECT DATABASES;
	        	var_dump($data1);
        	 */
        	
        	
        	
        	/**
        	 * 五、模版变量输出
        	 */
        	$this->assign('who','this is skphp');
			$this->display();
        
        }
        
}

