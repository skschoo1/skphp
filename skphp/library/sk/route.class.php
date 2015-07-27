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
// SKPHP路由
//----------------------------------

class Route {
	
	/**
	 * URL
	 * @var url_route
	 * @access public
	 */
	public $url_route = array();

	
	/**
	 * 获取数组形式的URL
	 * @access	public
	 * @param	string  $url_model
	 * @return	array
	 */
	public function getUrlArray($url_model) {
		switch ($url_model) {
			case 'pt':
				$url = $_SERVER['REQUEST_URI'];
				$url = substr($url,strpos($url, 'm=')-1,1) == '/' ? str_replace('/m=', '?m=', $url) : $url;
				$url = substr($url,strpos($url, 'c=')-1,1) == '/' ? parse_url(str_replace('/c=', '?c=', $url)) : parse_url($url);
				if(@empty($url['query']) && @!empty($_SERVER['PATH_INFO']) && $_SERVER['PATH_INFO'] != '/') Application::appError('', $errmessage='页面错误！请稍后再试～', '', 0);
				$this->queryToArray($url);
				break;
			case 'pathinfo':
				$url = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
				$this->pathinfoToArray($url);
				break;
			case 'rewrite':
				$url = isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'';
				$this->rewriteToArray($url);
				break;
		}
		return $this->url_route;
	}
	

	/**
	 * 将普通模式URL转化成数组
	 * @access	public
	 * @param   string  $url
	 * @return	array
	 */
	public function queryToArray($url) {
		$arr = !empty ($url['query']) ? explode('&', $url['query']) : array();
		$array = $tmp = array();
		if (count($arr) > 0) 
		{
			foreach ($arr as $item) 
			{
				$tmp = explode('=', $item);
				if(strpos($item,"m="))
				{
					$array['m'] = $tmp[1];
				}else{
					$array[$tmp[0]] = $tmp[1];
				}
			}	
			if (isset($array['m'])) 
			{
				$this->url_route['m'] = $array['m'];
				unset($array['m']);
			}
			if (isset($array['c'])) 
			{
				$this->url_route['c'] = $array['c'];
				unset($array['c']);
			}
			if (isset($array['a'])) 
			{
				$this->url_route['a'] = $array['a'];
				unset($array['a']);
			}
			if(count($array) > 0) $this->url_route['params'] = $array; 
		}else{
			$this->url_route = array();
		}
	}
	
	
	/**
	 * 将pathinfo模式URL转化成数组
	 * @access	public
	 * @param   string  $url
	 * @return	array
	 */
	public function pathinfoToArray($url) {
		$arr = !empty ($url) ? explode(C('URL_PATHINFO_DEPR'), $url) : array();
		if (count($arr) > 0)
		{
			foreach ($arr as $k=>$v)
			{
				if(empty($v)) unset($arr[$k]);
				if($v == 'm' || $v == 'c' || $v == 'a')
				{
					$this->url_route[$v] = $arr[$k+1];
					unset($arr[$k]);
					unset($arr[$k+1]);
				}				
			}			
			if (count($arr) >= 2) 
			{
				$mark = 0;
				$val = $key = array();
				foreach($arr as $value){
					$mark++;
					if ($mark % 2 == 0) {
						$val[] = $value;
					} else {
						$key[] = $value;
					}
				}
				if(count($key) !== count($val)) $val[] = NULL;
				$get = array_combine($key,$val);
				// foreach($get as $key=>$value) $_GET[$key] = $value;
			}else{
				$get = 0;
			}
			if(count($get) > 0) $this->url_route['params'] = $get;
		}else{
			$this->url_route = array();
		}
	}
	
	
	/**
	 * 将rewrite模式URL转化成数组
	 * @access	public
	 * @return	array
	 */
	public function rewriteToArray($url) {
		if(!empty($url))
		{
			$url = substr($url, 0, 1) != '/' ? $url : substr($url, 1);
			$url = str_replace('.'.C('url_html_suffix'),'',$url);			
			$arr_url_route_rules = C('url_route_rules');
			if(!empty($arr_url_route_rules))
			{
				$str_isNotFindUrl = 0;
				foreach ($arr_url_route_rules as $k=>$v)
				{
					$arr_k = explode('-', $k);
					$str_pregMatch = '';
					$str_pregMatch .= '/^';
					foreach ($arr_k as $kk=>$vv)
					{
						if($kk >= 1)
						{
							$str_pregMatch .= '[0-9a-zA-z]{1,}';
						}else{
							$str_pregMatch .= $vv;
						}
						if($kk < count($arr_k)-1) $str_pregMatch .= '-';
					}
					$str_pregMatch .= '$/';
					if(preg_match($str_pregMatch,$url))
					{
						$str_isNotFindUrl = 1;
						$arr_v = explode('/', $v);
						$this->url_route['m'] = !empty($arr_v[0]) ? $arr_v[0] : '';
						$this->url_route['c'] = !empty($arr_v[1]) ? $arr_v[1] : '';
						$this->url_route['a'] = !empty($arr_v[2]) ? $arr_v[2] : '';
						// 赋值
						$new_get_rules = explode('-', $k);
						$new_get_value = explode('-', $url);
						foreach ($new_get_rules as $url_k=>$url_v)
						{
							if(strpos('search：'.$url_v, ':'))
							{
								$_GET[str_replace(':', '', $url_v)] = $new_get_value[$url_k];
							}
						}
					}
				}
				if(empty($str_isNotFindUrl)) Application::appError('', $errmessage='页面错误！请稍后再试～', '', 0);
			}else{
				$this->pathinfoToArray($url);
			}
		}else{
			$this->pathinfoToArray($url);
		}
	}
	
	
}


