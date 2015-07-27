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
		$app_lib = LIB_PATH.'myclass/'.$lib.'.class.php';;

		if(file_exists($sys_lib))
		{
			if(class_exists('mysql') || $lib != 'mysql')
			{
				return Application::$_instance[$lib];
			}else{
				require ($sys_lib);
				return new Mysql();
			}
		}else if(file_exists($app_lib)){
			require ($app_lib);
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
 * @param  string	$name 			Model名称
 * @param  mixed	$connection 	数据库连接信息（mysql://root:123456@localhost/demo#utf8）
 * @return Model
 */
function DB($name='', $connection='') {
	header("Content-type:text/html;charset=utf-8");
	// 自定义错误
	if(!empty($connection))
	{
		// 努力开发中......
		echo '努力开发中.....';exit;
	}
	if(C('db_host') != '' && C('db_user') != '' && C('db_pwd') != '' && C('db_database') != '' && C('db_charset') != '')
	{
		if (!empty($name))
		{
			// 努力开发中......
			echo '努力开发中.....';exit;
		}else{
			$db_class = import('mysql');
			$db_class->init(C('db_host'), C('db_user'), C('db_pwd'), C('db_database'), C('conn'), C('db_charset'));
			return $db_class;
		}
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
