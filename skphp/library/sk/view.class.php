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
// 模板类
//----------------------------------

class View {

	/**
	 * 模板输出变量
	 * @var data
	 * @access protected
	 */
	public $data = array();

	
	/**
	 * 模板名
	 * @var theme
	 * @access protected
	 */
	public $template_name = '';
		
	
	/**
	 * 模板变量赋值
	 * @access public
	 * @param mixed $name
	 * @param mixed $value
	 */
	public function assign($name,$value=''){
		if(is_array($name)) {
			$this->data   =  array_merge($this->data,$name);
		}else {
			$this->data[$name] = $value;
		}
	}
	
	
	/**
	 * 加载模板和页面输出 可以返回输出内容
	 * @access public
	 * @param string $templateFile 模板文件名
	 * @param string $charset 模板输出字符集
	 * @return mixed
	 */
	public function display($templateFile='',$charset='') {
		$content = $this->fetch($templateFile);	// 解析并获取模板内容
		$this->render($content,$charset);		// 输出模板内容
	}
	
	
    /**
     * 解析和获取模板内容 用于输出
     * @access 	public
     * @param	string 	$templateFile 模板文件名
     * @return 	string
     */
	public function fetch($templateFile) {
		$view_file = VIEW_PATH . $templateFile . '.html';
		if (file_exists($view_file)) 
       	{
       		$view_file_cache = CACHE_PATH . base64_encode(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME) . '.php';
			if(APP_DEBUG) $this->writeTplCache($view_file_cache,file_get_contents($view_file));
			extract($this->data);
			ob_start();
        	ob_implicit_flush(0);
            include $view_file_cache;
			$content = ob_get_contents();
			ob_end_clean();
            return $content;
		} else {
			trigger_error('加载 ' . $view_file . ' 模板不存在');
		}
	}
	
	
	/**
	 * 输出内容文本可以包括Html
	 * @access private
	 * @param string $content 输出内容
	 * @param string $charset 模板输出字符集
	 * @param string $contentType 输出类型
	 * @return mixed
	 */
	private function render($content,$charset='',$contentType=''){
		if(empty($charset))  $charset = 'utf-8';
		if(empty($contentType)) $contentType = 'text/html';
		header('Content-Type:'.$contentType.'; charset='.$charset); // 网页字符编码
		header('Cache-control: private');  							// 页面缓存控制
		header('X-Powered-By:SKPHP');
		echo $content;	// 输出模板文件
	} 
	
	
	/**
	 * 写入静态化文件
	 * @param 	$view_file_cache  模版缓存地址
	 * @param	$content   		      内容
	 * @access	public
	 */
	public function writeTplCache($view_file_cache,$content) {
		if (!is_dir(CACHE_PATH)) mkdir(CACHE_PATH, 0777);
		if (!$fp = @fopen($view_file_cache, 'w'))  trigger_error('文件 ' . $view_file_cache . ' 不能打开');
		$content = $this->replaceTag($content);
		if (fwrite($fp, $content) == FALSE) trigger_error('文件 ' . $view_file_cache . ' 写入失败'); 
		fclose($fp);
	}
	
	
	/**
	 * 标签替换
	 * @param string $content
	 */
	private function replaceTag($content)
	{
		$md5_name = md5(MODULE_NAME.CONTROLLER_NAME.ACTION_NAME);
		// other
		$tmpl_parse_string =  Application::$_config['tmpl_parse_string'];
		if(!empty($tmpl_parse_string))
		{
			foreach ($tmpl_parse_string as $k=>$v)
			{
				if(!empty($k)) $content = str_replace($k, $v, $content);
			}
		}
		// var 
		$content = str_replace('$', '#s#', $content);
		preg_match_all("/{#s#[A-Za-z\[\]'\"\.]+}/",$content,$matches);
		if(!empty($matches[0]))
		{
			foreach($matches[0] as $k=>$v)
			{
				if(substr($v,0,1) == '{' && substr($v,-1,1) == '}')
				{
					$var = substr(substr($v,1),0,strlen(substr($v,1))-1);
					if(strpos($var,'.'))
					{
						$arr_var = explode('.', $var);
						foreach($arr_var as $kk=>$vv)
						{
							if($kk == 0)
							{
								$var = $vv;
							}else{
								$var .= "['".$vv."']";
							}
						}
					}
					$content = str_replace($v, '<?php echo '.$var.'; ?>', $content);
				}
			}
			$content = str_replace('#s#', '$', $content);
		}
		// fun
		$content = str_replace('<php>', '<?php ', $content);
		$content = str_replace('</php>', ' ?>', $content);
		// foreach
		preg_match_all("/{foreach\s+name=[^{]*}/",$content,$matches);
		if(!empty($matches[0]))
		{
			foreach($matches[0] as $k=>$v)
			{
				preg_match("/name=['\"]{1}[a-zA-Z]+['\"]{1}/",$v, $foreach_name);
				preg_match("/key=['\"]{1}[a-zA-Z]+['\"]{1}/",$v, $foreach_key);
				preg_match("/val=['\"]{1}[a-zA-Z]+['\"]{1}/",$v, $foreach_val);
				$foreach_name = substr(substr($foreach_name[0],6),0,strlen(substr($foreach_name[0],6))-1);
				$foreach_val = substr(substr($foreach_val[0],5),0,strlen(substr($foreach_val[0],5))-1);
				if(empty($foreach_key))
				{
					$foreach_key = 'key';
				}else{
					$foreach_key = substr(substr($foreach_key[0],5),0,strlen(substr($foreach_key[0],5))-1);
				}
			
				if(!empty($foreach_name) && !empty($foreach_key) && !empty($foreach_val))
				{
					$content = str_replace($v, '<?php foreach($'.$foreach_name.' as $'.$foreach_key.'=>$'.$foreach_val.'): ?>', $content);
				}
			}
			$content = str_replace('{/foreach}', '<?php endforeach; ?>', $content);
		}
		// if
		preg_match_all("/{if\s+condition=[^{]*?}/",$content,$matches);
		if(!empty($matches[0]))
		{
			foreach($matches[0] as $k=>$v)
			{
				preg_match("/condition=['\"]{1}.+['\"]{1}/",$v, $condition);
				$condition = substr(substr($condition[0],11),0,strlen(substr($condition[0],11))-1);
				if(!empty($condition))
				{
					$content = str_replace($v, '<?php if('.$condition.'): ?>', $content);
				}
			}
			// elseif
			preg_match_all("/{elseif\s+condition=[^{]*?}/",$content,$matches);
			if(!empty($matches[0]))
			{
				foreach($matches[0] as $k=>$v)
				{
					preg_match("/condition=['\"]{1}.+['\"]{1}/",$v, $condition);
					$condition = substr(substr($condition[0],11),0,strlen(substr($condition[0],11))-1);
					if(!empty($condition))
					{
						$content = str_replace($v, '<?php elseif('.$condition.'): ?>', $content);
					}
				}
			}
			$content = str_replace('{else}', '<?php else: ?>', $content);
			$content = str_replace('{/if}', '<?php endif; ?>', $content);
		}
		// js 
		$content = preg_replace('/<js\s*src=[\'"]{1}/i','<script src="<?php echo minJs(\'',$content);
		$content = preg_replace('/[\'"]{1}\s*>\s*<\/js>/i','\',\''.$md5_name.'\'); ?>" ></script>',$content);
		// css
		$content = preg_replace('/<css\s*src=[\'"]{1}/i','<link rel="stylesheet" href="<?php echo minCss(\'',$content);
		$content = preg_replace('/[\'"]{1}\s*>\s*<\/css>/i','\',\''.$md5_name.'\'); ?>" />',$content);
		return $content;
	}
	
	
	
}

