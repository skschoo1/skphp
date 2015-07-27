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
// Mysql
//----------------------------------

class Mysql {
	
    private $db_host; 				//数据库主机
    private $db_user;	 			//数据库用户名
    private $db_pwd; 				//数据库用户名密码
    private $db_database; 			//数据库名
    private static $conn; 			//数据库连接标识;
    private $result; 				//执行query命令的结果资源标识
    private $sql; 					//sql执行语句
    private $row; 					//返回的条目数
    private $db_charset; 			//数据库编码，GBK,UTF8,gb2312
    private $bulletin = true; 		//是否开启错误记录
    private $show_error = true; 	//测试阶段，显示所有错误,具有安全隐患,默认关闭
    private $is_error = false; 		//发现错误是否立即终止,默认true,建议不启用，因为当有问题时用户什么也看不到是很苦恼的
 
    
    /**
     * init connect
     * @return
     */
    public function init($db_host, $db_user, $db_pwd, $db_database, $conn='', $db_charset='UTF8') {
    	if(!(self::$conn instanceof self)) {
	        $this->db_host = $db_host;
	        $this->db_user = $db_user;
	        $this->db_pwd = $db_pwd;
	        $this->db_database = $db_database;
	        self::$conn = $conn;
	        $this->db_charset = $db_charset;
	        $this->connect();
    	}
    }
 
    
    /**
     * CONNECT DATABASES
     * @var $conn 数据库连接标识（pconn-永久链接，other-即使链接）
     * @return
     */
    public function connect() {
        if (self::$conn == "pconn") 
        {
            self::$conn = mysql_pconnect($this->db_host, $this->db_user, $this->db_pwd);
        } else {
            self::$conn = mysql_connect($this->db_host, $this->db_user, $this->db_pwd);
        }
        
        if (!mysql_select_db($this->db_database, self::$conn)) 
        {
            if ($this->show_error) 
            {
                $this->show_error("数据库不可用：", $this->db_database);
            }
        }
        mysql_query("SET NAMES $this->db_charset");
    }
    
    
    /**
     * CURD DATABASES
     * @param  string	 $sql
     * @return<select> 	 array
     * @return<upd> 	 bool
     */
    public function query($sql) {
    	if (empty($sql))  $this->show_error("SQL语句错误：", "SQL查询语句为空");
    	$this->sql = $sql;
    	$result = mysql_query($this->sql, self::$conn);
    	if (!$result)
    	{
    		if ($this->show_error) $this->show_error("错误SQL语句：", $this->sql);
    	} else {
    		if(strpos('isSet：'.$sql, 'select'))
    		{
    			$this->result;
    			while($row = mysql_fetch_assoc($result)) { $rows[]=$row; }
    			return $rows;
    		}else{
    			return $result;
    		}
    	}
    }
    

    /**
     * 释放结果集
     * @return
     */
    public function free() {
    	@ mysql_free_result($this->result);
    }
    
    
    /**
     * 析构函数，自动关闭数据库,垃圾回收机制
     * @return
     */
    public function __destruct() {
    	if (!empty ($this->result)) {
    		$this->free();
    	}
    	mysql_close(self::$conn);
    } 
     
 
    /**
     * 输出显示sql语句
     * @param	string  $message  错误提示
     * @param	string  $sql 
     * @return
     */
    public function show_error($message = "", $sql = "") {
        if (!$sql) {
            echo "<font color='red'>" . $message . "</font>";
            echo "<br />";
        } else {
            echo "<fieldset>";
            echo "<legend>SK RUNNING ERROR MESSAGE：</legend><br />";
            echo "<div style='font-size:14px; clear:both; font-family:Verdana, Arial, Helvetica, sans-serif;'>";
            echo "<div style='padding-left: 5px;height:20px; background:#000000; border:1px #000000 solid'>";
            echo "<font color='white'>错误原因：</font>";
            echo "</div><br />";
            echo mysql_error() . "<br /><br />";
            echo "<div style='padding-left: 5px;height:20px; background:#FF0000; border:1px #FF0000 solid'>";
            echo "<font color='white'>" . $message . "</font>";
            echo "</div>";
            echo "<font color='red'><pre>" . $sql . "</pre></font>";
            $ip = $this->getip();
            if ($this->bulletin) 
            {
                $time = date("Y-m-d H:i:s");
                $message = $message . "\r\n$this->sql" . "\r\n客户IP:$ip" . "\r\n时间 :$time" . "\r\n\r\n";
 
                $server_date = date("Y-m-d");
                $filename = $server_date . ".txt";
                $file_path = LOG_PATH . $filename;
                $error_content = $message;
                //$error_content="错误的数据库，不可以链接";
                $file = LOG_PATH; //设置文件保存目录
 
                //建立文件夹
                if (!file_exists($file)) 
                {
                    if (!mkdir($file, 0777)) 
                    {
                        //默认的 mode 是 0777，意味着最大可能的访问权
                        die("upload files directory does not exist and creation failed");
                    }
                }
 
                //建立txt日期文件
                if (!file_exists($file_path)) 
                {
 
                    //echo "建立日期文件";
                    fopen($file_path, "w+");
 
                    //首先要确定文件存在并且可写
                    if (is_writable($file_path)) 
                    {
                        //使用添加模式打开$filename，文件指针将会在文件的开头
                        if (!$handle = fopen($file_path, 'a')) 
                        {
                            echo "不能打开文件 $filename";
                            exit;
                        }
 
                        //将$somecontent写入到我们打开的文件中。
                        if (!fwrite($handle, $error_content)) 
                        {
                            echo "不能写入到文件 $filename";
                            exit;
                        }

                        echo "<br /><br /><b>FILE：</b> 模块[".MODULE_NAME."]  控制器[".CONTROLLER_NAME."]  方法[".ACTION_NAME."]<br /><br />";
                        echo "<b>CACHE：</b>".$file_path;
                        echo "<br /><br /><br /><a title='官方网站' target='_blank' href='http://www.sk-school.com'>SKPHP</a><sup>1.0.0</sup> { Share knowledge change you and me } -- [ Made In China  ]";
 
                        //关闭文件
                        fclose($handle);
                    } else {
                        echo "文件 $filename 不可写";
                    }
 
                } else {
                    //首先要确定文件存在并且可写
                    if (is_writable($file_path)) 
                    {
                        //使用添加模式打开$filename，文件指针将会在文件的开头
                        if (!$handle = fopen($file_path, 'a')) 
                        {
                            echo "不能打开文件 $filename";
                            exit;
                        }
 
                        //将$somecontent写入到我们打开的文件中。
                        if (!fwrite($handle, $error_content)) 
                        {
                            echo "不能写入到文件 $filename";
                            exit;
                        }
                        
                        echo "<br /><br /><b>FILE：</b> 模块[".MODULE_NAME."]  控制器[".CONTROLLER_NAME."]  方法[".ACTION_NAME."]<br /><br />";
                        echo "<b>CACHE：</b>".$file_path;
                        echo "<br /><br /><br /><a title='官方网站' target='_blank' href='http://www.sk-school.com'>SKPHP</a><sup>1.0.0</sup> { Share knowledge change you and me } -- [ Made In China  ]";
 
                        //关闭文件
                        fclose($handle);
                    } else {
                        echo "文件 $filename 不可写";
                    }
                }
 
            }
            echo "<br />";
            if ($this->is_error) exit;
        }
        echo "</div>";
        echo "</fieldset>";
 
        echo "<br />";
        die;
    }
 
    
    //取得 MySQL 服务器信息
    public function mysql_server($num = '') {
        switch ($num) {
            case 1 :
                return mysql_get_server_info(); //MySQL 服务器信息
                break;
 
            case 2 :
                return mysql_get_host_info(); //取得 MySQL 主机信息
                break;
 
            case 3 :
                return mysql_get_client_info(); //取得 MySQL 客户端信息
                break;
 
            case 4 :
                return mysql_get_proto_info(); //取得 MySQL 协议信息
                break;
 
            default :
                return mysql_get_client_info(); //默认取得mysql版本信息
        }
    }
 
    // 获得客户端真实的IP地址
    function getip() {
        if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) 
        {
            $ip = getenv("HTTP_CLIENT_IP");
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) 
            {
                $ip = getenv("HTTP_X_FORWARDED_FOR");
            } else {
                if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) 
                {
                    $ip = getenv("REMOTE_ADDR");
                } else {
                    if (isset ($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) 
                    {
                        $ip = $_SERVER['REMOTE_ADDR'];
                    } else {
                        $ip = "unknown";
                    }
                }
            }
        }
        return ($ip);
    }
    //防止注入
    function inject_check($sql_str) { 
        $check = eregi('select|insert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);
        if ($check) {
            echo "输入非法注入内容！";
            exit ();
        } else {
            return $sql_str;
        }
    }
    //检查来路
    function checkurl() {
        if (preg_replace("/https?:\/\/([^\:\/]+).*/i", "\\1", $_SERVER['HTTP_REFERER']) !== preg_replace("/([^\:]+).*/", "\\1", $_SERVER['HTTP_HOST'])) {
            header("Location: http://www.xxxxx.com");
            exit();
        }
    }
 
}
?>