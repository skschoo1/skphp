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
		if (fwrite($fp, str_replace('}-->', '; ?>', str_replace('<!--{', '<?php echo ', $content))) == FALSE) trigger_error('文件 ' . $view_file_cache . ' 写入失败'); 
		fclose($fp);
	}
	
	
	
}

