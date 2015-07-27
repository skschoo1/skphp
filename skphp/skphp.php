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
// SKPHP公共入口文件
//----------------------------------

// 版本信息
const SK_VERSION     =   '1.0.0';

// 类文件后缀
const EXT               =   '.class.php'; 

// 系统常量定义
define('SK_PATH', dirname(__FILE__));
define('IS_CLI', PHP_SAPI=='cli' ? 1 : 0);

define('LIB_PATH', SK_PATH.'/library/');					//系统核心基类库目录
define('MODEL_PATH', APP_PATH.'model/');
define('CONTROLLER_PATH', APP_PATH.'controller/');
define('VIEW_PATH', APP_PATH.'view/');

define('SYS_COMMON_PATH',    APP_PATH.'common/'); 		// 应用公共目录
define('COMMON_PATH',    SYS_COMMON_PATH.'common/');	// 应用方法目录
define('CONF_PATH',      SYS_COMMON_PATH.'conf/'); 		// 应用配置目录
define('LANG_PATH',      SYS_COMMON_PATH.'lang/'); 		// 应用语言目录

define('RUNTIME_PATH',   APP_PATH.'cache/');   		// 系统运行时目录
define('LOG_PATH',       RUNTIME_PATH.'logs/'); 	// 应用日志目录
define('TEMP_PATH',      RUNTIME_PATH.'temp/'); 	// 应用缓存目录
define('DATA_PATH',      RUNTIME_PATH.'data/'); 	// 应用数据目录
define('CACHE_PATH',     RUNTIME_PATH.'tpl/'); 		// 应用模板缓存目录


final class Application {
	
	// 核心配置
	static public $_config	= array();
	
    // 类映射
	static public $_lib		= array();

	// 实例化对象（静态变量保存全局实例）
	static public $_instance = array();
    
	
	/**
	 * 初始化
	 * @access	public
	 * @return 	void
	 */
	static public function init() {
		require SK_PATH.'/library/sk/model.class.php';
		require SK_PATH.'/library/sk/controller.class.php';
		// 加载应用配置和方法
		self::mergeConfig();
		self::mergeCommon();				
		if(is_object(self::$_instance['cache']))
		{
			self::$_instance['cache']->init(
				DATA_PATH,
				self::$_config['cache_prefix'],
				self::$_config['cache_time'],
				self::$_config['cache_mode']
			);
		}
	}
	
	
	/**
	 * 类库自动加载
	 * @access	public
     * @return 	void
	 */
	static public function autoload() {
		// 设置映射
		self::$_lib = array(
			'route'		 =>      LIB_PATH.'sk/route.class.php',
			'view'	 	 =>      LIB_PATH.'sk/view.class.php',
			'cache'    	 =>      LIB_PATH.'sk/cache.class.php',
		);
		foreach (self::$_lib as $key => $value){
			require (self::$_lib[$key]);
			$lib = ucfirst($key);
			self::$_instance[$key] = new $lib;
		}
	}
	
	
	/**
	 * 合并配置文件
	 * @access	public
	 * @return 	void
	 */
	static public function mergeConfig() {
		$other_config_path = CONF_PATH.'config.php';
		if(file_exists($other_config_path))
		{
			$config = array_merge(require SK_PATH.'/conf/config.php', require $other_config_path);
			if(!empty($config['load_ext_config']))
			{
				$arr_config = explode(',', $config['load_ext_config']);
				foreach ($arr_config as $v)
				{
					if(!empty($v)) $config = array_merge($config, require CONF_PATH.$v.'.php');
				}
			}
		}else{
			$config = array_merge(require SK_PATH.'/conf/config.php', array());
		}
		self::$_config = $config;
	}
	
	
	/**
	 * 合并方法
	 * @access	public
	 * @return 	void
	 */
	static public function mergeCommon() {
		require SK_PATH.'/common/function.php';
		is_file(COMMON_PATH.'function.php') ? require COMMON_PATH.'function.php' : '';
		if(!empty(self::$_config['load_ext_common']))
		{
			$arr_common = explode(',', self::$_config['load_ext_common']);
			foreach ($arr_common as $v)
			{
				if(!empty($v)) require COMMON_PATH.$v.'.php';
			}
		}
	}
	

	/**
	 * 创建应用
	 * @access      public
	 * @param       array   $config
	 */
	static public function run()
	{		
		$file1 = SK_PATH.'/tpl/demo';
		$file2 = APP_PATH;
		if (!file_exists(CONTROLLER_PATH)) self::initCopy($file1,$file2,1);
			
		self::autoload();
		// 设定错误和异常处理
		register_shutdown_function('Application::fatalError');
		set_error_handler('Application::appError');
		set_exception_handler('Application::appException');			
		// init
		self::init();
		self::dispatch(self::$_instance['route']->getUrlArray(C('url_model')));
	}
	
	
	/**
	 * SKPHP内置的类Dispatcher
	 * 完成URL解析、路由和调度
	 * @param	array	$url_array
	 */
	static public function dispatch($url_array = array()) {
		$module = $controller = $action = $model = $params = '';
		$module = isset($url_array['m']) ? $url_array['m'] : C('default_module');
		if(!file_exists(CONTROLLER_PATH.$module)) Application::appError('', $errmessage='模块不存在', '', 0);
		if(!empty($url_array['c']))
		{
			$controller = $model = $url_array['c'];
			if($module){
				$controller_file = CONTROLLER_PATH.$module.'/'.$controller.'Controller.php';
				$model_file = MODEL_PATH.$module.'/'.$model.'Model.php';
			}else{
				$controller_file = CONTROLLER_PATH.$controller.'Controller.php';
				$model_file = MODEL_PATH.$model.'Model.php';
			}
		}else{
			$controller = $model = C('default_controller');
			if($module){
				$controller_file = CONTROLLER_PATH.$module.'/'.$controller.'Controller.php';
				$model_file = MODEL_PATH.$module.'/'.$model.'Model.php';
			}else{
				$controller_file = CONTROLLER_PATH.$controller.'Controller.php';
				$model_file = MODEL_PATH.$model.'Model.php';
			}
		}
		$action = !empty($url_array['a']) ? $url_array['a'] : C('default_action');		
		$params = isset($url_array['params']) ? $url_array['params'] : '';		
		if(file_exists($controller_file))
		{
			define('MODULE_NAME', $module);
			define('CONTROLLER_NAME', $controller);
			if (file_exists($model_file)) require $model_file;
			require $controller_file;
			$controller = $controller.'Controller';
			$controller = new $controller;
			if(!empty($action)){
				if(method_exists($controller, $action))
				{
					define('ACTION_NAME', $action);
					isset($params) ? $controller ->$action($params) : $controller ->$action();
				}else{
					Application::appError('', $errmessage='控制器方法不存在', $controller_file, 0);
				}
			}else{
				Application::appError('', $errmessage='控制器方法不存在', $controller_file, 0);
			}
		}else{
			Application::appError('', $errmessage='控制器不存在', $controller_file, 0);
		}
	}
	
	
	/**
	 * 错误输出
	 * @param mixed $error 错误
	 * @return void
	 */
	static public function halt($error) {
		$e = array();
		if (APP_DEBUG || IS_CLI) {
			//调试模式下输出错误信息
			if (!is_array($error)) {
				$trace          = debug_backtrace();
				$e['message']   = $error;
				$e['file']      = $trace[0]['file'];
				$e['line']      = $trace[0]['line'];
				ob_start();
				debug_print_backtrace();
				$e['trace']     = ob_get_clean();
			} else {
				$e              = $error;
			}
			if(IS_CLI){
				exit(iconv('UTF-8','gbk',$e['message']).PHP_EOL.'FILE: '.$e['file'].'('.$e['line'].')'.PHP_EOL.$e['trace']);
			}
		} else {
			$error_page = C('error_page');	//否则定向到错误页面
			if (!empty($error_page)) {
				header("Location: " . $error_page);exit;
			} else {
				$message        = is_array($error) ? $error['message'] : $error;
				$e['message']   = false ? $message : '页面错误！请稍后再试～';
			}
		}
		// 包含异常页面模板
		$exceptionFile =  SK_PATH.'/tpl/sk_exception.tpl';
		include $exceptionFile;
		exit;
	}
	
	
	/**
	 * 致命错误捕获
	 * @desc error_get_last - 函数获取最后发生的错误
	 * 返回的数组包含 4 个键和值：
	 * [type] - 错误类型
	 * [message] - 错误消息
	 * [file] - 发生错误所在的文件
	 * [line] - 发生错误所在的行
	 */
	static public function fatalError() {
		if (@$e = error_get_last()) {
			switch($e['type']){
				case E_ERROR:
				case E_PARSE:
				case E_CORE_ERROR:
				case E_COMPILE_ERROR:
				case E_USER_ERROR:
					ob_end_clean();
					self::halt($e);
					break;
			}
		}
	}
	
	
	/**
	 * 自定义错误处理
	 * @access public
	 * @param 	int 	$errno		错误类型
	 * @param 	string	$errmessage	错误信息
	 * @param 	string 	$errfile	错误文件
	 * @param 	int 	$errline	错误行数
	 * @return	void
	 */
	static public function appError($errno='', $errmessage='', $errfile='', $errline='') {
		if (!APP_DEBUG) 
		{
			$error_page = C('error_page');
			if (!empty($error_page)) {
				header("Location: " . $error_page);exit;
			} else {
				$errmessage = 'APP_DEBUG CLOSE, Please setting redirect URL';
			}
		}
		@ob_end_clean();
		if(empty($errmessage) || !isset($errfile) || !isset($errline))
		{
			echo '傻x、自定义错误跑外星了，自己挨个查吧！';die;
		}
		$e['message']   = $errmessage;
		$e['file']      = $errfile;
		$e['line']      = $errline;
		$exceptionFile =  SK_PATH.'/tpl/sk_exception.tpl';
		include $exceptionFile;
		exit;
	}
	
	
	/**
	 * 自定义异常处理
	 * @access 	public
	 * @param 	mixed $e 异常对象
	 */
	static public function appException($e) {
		$error = array();
		$error['message']   =   $e->getMessage();
		$trace              =   $e->getTrace();
		if('E'==$trace[0]['function']) {
			$error['file']  =   $trace[0]['file'];
			$error['line']  =   $trace[0]['line'];
		}else{
			$error['file']  =   $e->getFile();
			$error['line']  =   $e->getLine();
		}
		$error['trace']     =   $e->getTraceAsString();
		// 发送404信息
		header('HTTP/1.1 404 Not Found');
		header('Status:404 Not Found');
		self::halt($error);
	}
	
	

	/**
	 * init skphp running
	 * $param	string	   $source	 源目录名
	 * $param	string	   $source2	 目的目录名
	 * $param	tingyint   $child	 复制时，是不是包含的子目录
	 */
	static public function initCopy($source, $source2, $child) {
		
		if(!is_dir($source))
		{
			echo("Error:the $source is not a direction!");
			return 0;
		}
	
		if(!is_dir($source2)) mkdir($source2,0777);
	
		$handle=dir($source);
		while($entry=$handle->read())
		{
			if(($entry!=".")&&($entry!=".."))
			{
				if(is_dir($source."/".$entry))
				{
					if($child) self::initCopy($source."/".$entry,$source2."/".$entry,$child);
				}else{
					copy($source."/".$entry,$source2."/".$entry);
				}
			}
		}
		return 1;
	}
	
	
}

// 应用初始化
Application::run();

