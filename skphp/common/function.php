<?php

/**
 * SKPHP 系统函数库
 */


/**
 * 格式化打印数据
 * @param  mixed 
 * @return mixed
 */
function p($data) {
	echo '<pre>';
	print_r($data);
	echo '<pre>';
}


/**
 * 获取配置文件
 * @param  string $name 配置变量
 * @return mixed
 */
function C($name='') {
	if (empty($name)) return '';
	if (is_string($name)) {
		$name = strtolower($name);
		$value = Application::$_config[$name];
	}else{
		$value = '';
	}
	return $value;
}


/**
 * 实例化模型
 * @param	string	$model  模型名称
 */
function M($model) {
	$module_name = MODULE_NAME == '' ? '' : MODULE_NAME.'/';
	$model_file = MODEL_PATH.$module_name.$model.'Model.php';
	if (empty($model)) {
		Application::appError('', $errmessage='不能实例化空模型', $model_file, 0);
	}else{
		if (file_exists($model_file)) 
		{
			require $model_file;
			$model_name = $model.'Model';
			return new $model_name;
		}else{
			Application::appError('', $errmessage='Class '.$model_file.' not found', $model_file, 0);
		}
	}
}


/**
 * 导入所需的类库 同java的Import
 * @param  string	$lib	类库命名空间字符串
 * @return 
 */
function import($lib) {
	if(empty($lib))
	{
		$controller_file = CONTROLLER_PATH.MODULE_NAME.CONTROLLER_NAME.'Controller.php';
		Application::appError('', $errmessage='undefined function ', $controller_file, 0);
	}else{
		$app_lib = $sys_lib = '';
		$sys_lib = LIB_PATH.'sk/'.$lib.'.class.php';
		$app_lib = LIB_PATH.'myclass/'.$lib.'.class.php';
		$tools_lib = LIB_PATH.'tools/'.$lib.'.class.php';

		if(file_exists($sys_lib))
		{
			//if(class_exists('mysql') || $lib != 'mysql')
			//{
				return Application::$_instance[$lib];
			//}else{
			//	require ($sys_lib);
			//	return new Mysql();
			//}
		}else if(file_exists($app_lib)){
			require ($app_lib);
			$class_name = ucfirst(substr($lib, 0,1)).ucfirst(substr($lib, 1));
			return new $class_name();
		}else if(file_exists($tools_lib)){
			require ($tools_lib);
			$class_name = ucfirst(substr($lib, 0,1)).ucfirst(substr($lib, 1));
			return new $class_name();
		}else{
			$controller_file = CONTROLLER_PATH.MODULE_NAME.CONTROLLER_NAME.'Controller.php';
			Application::appError('', $errmessage='undefined function ', $controller_file, 0);
		}
	}
}


/**
 * DB函数用于实例化一个没有模型文件的Model
 * @param  mixed   $connection	 数据库连接信息（服务器IP#端口#用户#密码#数据库名#编码） 
 * @return Model
 */
function DB($connection='') {
	header("Content-type:text/html;charset=utf-8");
	if(!empty($connection))
	{
		$arr_db_info = explode('#', $connection);
		Application::$_instance['mysql']->init($arr_db_info[0], $arr_db_info[1], $arr_db_info[2], $arr_db_info[3], $arr_db_info[4], C('conn'), $arr_db_info[5]);
		return Application::$_instance['mysql'];
	}
	if(C('db_host') != '' && C('db_user') != '' &&  C('db_database') != '' && C('db_charset') != '')
	{
		Application::$_instance['mysql']->init(C('db_host'), C('db_port'), C('db_user'), C('db_pwd'), C('db_database'), C('conn'), C('db_charset'));
		return Application::$_instance['mysql'];
	}else{
		Application::appError('', $errmessage='DATABASES DONT CONFIG', '', 0);
	}
}

/**
 * WC 写入读取数据缓存
 * @param  string	$name 		
 * @param  mixed	$value 	
 * @return 
 */
function WC($name, $value='') {
	if(!empty($name))
	{
		$cache = import('cache');
		if(empty($value))
		{
			$rs = $cache->get($name);
		}else{
			$rs = $cache->set($name, $value);
		}
		return $rs;
	}else{
		Application::appError('', $errmessage='IS NOT SETTING NAME', '', 0);
	}
}


/**
 * JS压缩处理
 * @param	string	$src
 * @return
 */
function minJs($src='',$md5_name='') {
	if(substr($src, 0, 1) == '/') $src = '.' . $src;
	$cache_src = STYLE_PATH . md5($src.$md5_name) . '.js';
	$return_cache_src = STYLE_PATH . md5($src.$md5_name) . '.js';
	if(substr($return_cache_src, 0, 1) == '.') $return_cache_src = substr($return_cache_src, 1);
	if(!empty($src))
	{
		if(APP_DEBUG === FALSE && file_exists($cache_src)) return $return_cache_src; 
		
		if (!is_dir(STYLE_PATH)) mkdir(STYLE_PATH, 0777);
		if (strpos($src, '|'))
		{
			$src_content = '';
			$arr_src = explode('|', $src);
			foreach ($arr_src as $v)
			{
				if(substr($v, 0, 1) == '/') $v = '.' . $v;
				$src_content .= file_get_contents($v);
			}
		}else {
			$src_content = file_get_contents($src);
		}
		$cache_content = Jsmin::minify($src_content);
		file_put_contents($cache_src, $cache_content);
		if(substr($cache_src, 0, 1) == '.') $cache_src = substr($cache_src, 1);
		return $return_cache_src;
	}else{
		Application::appError('', $errmessage='JS CONFIG ERROR', '', 0);
	}
}


/**
 * CSS压缩处理
 * @param	string	$src
 * @return
 */
function minCss($src='',$md5_name='') {
	if(substr($src, 0, 1) == '/') $src = '.' . $src;
	$cache_src = STYLE_PATH . md5($src.$md5_name) .'.css';
	$return_cache_src = STYLE_PATH . md5($src.$md5_name) .'.css';
	if(substr($return_cache_src, 0, 1) == '.') $return_cache_src = substr($return_cache_src, 1);
	if(!empty($src))
	{
		if(APP_DEBUG === FALSE && file_exists($cache_src)) return $return_cache_src;

		if (!is_dir(STYLE_PATH)) mkdir(STYLE_PATH, 0777);
		if (strpos($src, '|'))
		{
			$src_content = '';
			$arr_src = explode('|', $src);
			foreach ($arr_src as $v)
			{
				if(substr($v, 0, 1) == '/') $v = '.' . $v;
				$src_content .= file_get_contents($v);
			}
		}else {
			$src_content = file_get_contents($src);
		}
		$cache_content = Cssmin::minify($src_content);
		file_put_contents($cache_src, $cache_content);
		return $return_cache_src;
	}else{
		Application::appError('', $errmessage='JS CONFIG ERROR', '', 0);
	}
}

