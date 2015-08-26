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

	
	private static $_instance;		//该属性用来保存实例
	
	public static $dbtype = 'mysql';
	public static $dbhost = '';
	public static $dbport = '';
	public static $dbname = '';
	public static $dbuser = '';
	public static $dbpass = '';
	public static $charset = '';
	public static $stmt = null;
	public static $DB = null;			//  当前数据库连接实例	
	public static $connect = true; 		//  是否长连接
	public static $debug = APP_DEBUG;
	private static $parms = array ();
	
	
	/**
	 * 静态方法，单例访问统一入口
	 * @return Singleton返回应用中的唯一对象实例
	 */
	static public function getInstance() 
	{
		if(!(self::$_instance instanceof self)) 
		{
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	
	
	/**
	 * init connect
	 * @return
	 */
	public function init($db_host, $db_port='3306', $db_user, $db_pwd, $db_database, $conn=true, $db_charset='UTF8') 
	{
		self::$dbtype = 'mysql';
		self::$dbhost = $db_host;
		self::$dbport = $db_port;
		self::$dbuser = $db_user;
		self::$dbpass = $db_pwd;
		self::$dbname = $db_database;
		self::$connect = $conn;
		self::$charset = $db_charset;
		self::connect ();
		self::$DB->setAttribute ( PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true );
		self::$DB->setAttribute ( PDO::ATTR_EMULATE_PREPARES, true );
		self::execute ('SET NAMES ' . self::$charset, '');
	}
	
	
	/**
	 * 析构函数
	 */
	public function __destruct() {
		self::close ();
	}

	
	/**
	 * 连接数据库
	 */
	public function connect() {
		try {
			self::$DB = new PDO ( self::$dbtype . ':host=' . self::$dbhost . ';port=' . self::$dbport . ';dbname=' . self::$dbname, self::$dbuser, self::$dbpass, array (
					PDO::ATTR_PERSISTENT => self::$connect
			) );
		} catch ( PDOException $e ) {
			self::sqlError ( $e->getMessage () );
			exit;
		}
	}
	

	/**
	 * 关闭数据连接
	 */
	public function close() {
		self::$DB = null;
	}

	
	/**
	 * 对字符串进行转移
	 */
	public function quote($str) {
		return self::$DB->quote ( $str );
	}

	
	/**
	 * 作用:获取数据表里的栏位
	 * 返回:表字段结构
	 * 类型:数组
	 */
	public function getFields($table) {
		self::$stmt = self::$DB->query ( "DESCRIBE $table" );
		$result = self::$stmt->fetchAll ( PDO::FETCH_ASSOC );
		self::$stmt = null;
		return $result;
	}

	
	/**
	 * 作用:获得最后INSERT的主键ID
	 * 返回:最后INSERT的主键ID
	 * 类型:数字
	 */
	public function getLastId() {
		return self::$DB->lastInsertId ();
	}
	

	/**
	 * 作用:执行INSERT\UPDATE\DELETE
	 * 返回:执行語句影响行数
	 * 类型:数字
	 */
	public function execute($sql, $table='') {
		self::getPDOError ( $sql );
		$status = self::$DB->exec ( $sql );
		if(empty($status) && !empty($table))
		{
			$isFindTab = $this->getField("INFORMATION_SCHEMA`.`TABLES",'TABLE_NAME',"TABLE_SCHEMA='".self::$dbname."' and TABLE_NAME='".$table."'");
			if(empty($isFindTab))
			{
				self::sqlError ( "Not Found: 1146 Table ".self::$dbname.".".$table." doesn't exist" );
			}
		}
		return $status;
	}

	
	/**
	 * 获取要操作的数据
	 * 返回:合併后的SQL語句
	 * 类型:字串
	 */
	private function getCode($table, $args) {
		$code = '';
		if (is_array ( $args )) {
			foreach ( $args as $k => $v ) {
				if ($v == '') {
					continue;
				}
				$code .= "`$k`='$v',";
			}
		}
		$code = substr ( $code, 0, - 1 );
		return $code;
	}


	public function optimizeTable($table) {
		$sql = "OPTIMIZE TABLE $table";
		self::execute ($sql, $table);
	}


	/**
	 * 执行具体SQL操作
	 * 返回:运行結果
	 * 类型:数组
	 */
	public function query($sql, $type='1') {
		$result = array ();
		self::$stmt = self::$DB->query ( $sql );
		self::getPDOError ( $sql );
		self::$stmt->setFetchMode ( PDO::FETCH_ASSOC );
		switch ($type) {
			case '0' :
				$result = self::$stmt->fetch ();
				break;
			case '1' :
				$result = self::$stmt->fetchAll ();
				break;
			case '2' :				 
				$result = self::$stmt->rowCount ();				 
				break;
		}
		self::$stmt = null;
		return $result;
	}


	/**
	 * 作用:插入数据
	 * 返回:表內记录
	 * 类型:数组
	 * 參数:$db->insert('$table',array('name'=>'test'))
	 */
	public function insert($table, $args) 
	{
		$sql = "INSERT INTO `$table` SET ";
		 
		$code = self::getCode ( $table, $args );
		$sql .= $code;
		$status = self::execute ($sql, $table);
		if($status)
		{
			return $this->getLastId ();
		}else{
			return $status;
		}
	}
	
	/**
	 * 作用: 读取字段值
	 * 返回: 表內记录
	 * 类型: 数组
	 * 參数: $db->getField($table,$fields,$where)
	 */
	public function getField($table,$fields,$where)
	{
		$sql = "SELECT {$fields} FROM `{$table}` ";
		$sql .= ($where) ? " WHERE $where" : '';
		$sql .= "limit 1";
		$data = self::query ( $sql, $type = '1' );
		if(empty($data)) return false;
 		if(strpos($fields,','))
		{
			return $data[0];
		}else{
			return $data[0][$fields];
		}
	}
	

	/**
	 * 修改数据
	 * 返回:记录数 
	 * 类型:数字
	 * 參数:$db->update($table,$data,$where);
	 */
	public function update($table='', $args='', $where='') {
		if(empty($table) || empty($args)) self::sqlError ( 'CURD syntax error' );
		if(empty($where)) self::sqlError ( 'must where conditions' );
		$code = self::getCode ( $table, $args );
		$sql = "UPDATE `$table` SET ";
		$sql .= $code;
		$sql .= " Where $where";		 
		return self::execute ($sql, $table);
	}
	

	/**
	 * 作用:刪除数据
	 * 返回:表內记录
	 * 类型:数组
	 * 參数:$db->delete($table,$where)
	 */
	public function delete($table='', $where='') {
		if(empty($table)) self::sqlError ( 'CURD syntax error' );
		if(empty($where)) self::sqlError ( 'must where conditions' );
		$sql = "DELETE FROM `$table` Where $where";
		return self::execute ($sql, $table);
	}

	
	/**
	 * 作用:获取单行数据
	 * 返回:表內第一条记录
	 * 类型:数组
	 * 參数: $db->fetOne($table,$field,$where)
	 */
	public function fetOne($table, $field = '*', $where = false) {
		$sql = "SELECT {$field} FROM `{$table}`";
		$sql .= ($where) ? " WHERE $where" : '';
		return self::query ( $sql, $type = '0' );
	}
	
	
	/**
	 * 作用:获取所有数据
	 * 返回:表內记录
	 * 类型:二維数组
	 * 參数:$db->fetAll($table,$field = '*',$orderby = '',$where='')
	 */
	public function fetAll($table, $field = '*', $orderby = false, $where = false) {
		$sql = "SELECT {$field} FROM `{$table}`";
		$sql .= ($where) ? " WHERE $where" : '';
		$sql .= ($orderby) ? " ORDER BY $orderby" : '';
		return self::query ( $sql, $type = '1' );
	}
	
	
	/**
	 * 作用:获取单行数据
	 * 返回:表內第一条记录
	 * 类型:数组
	 * 參数:select * from table where id='1'
	 */
	public function getOne($sql) {
		return self::query ( $sql, $type = '0' );
	}
	
	
	/**
	 * 作用:获取所有数据
	 * 返回:表內记录
	 * 类型:二維数组
	 * 參数:select * from table
	 */
	public function getAll($sql) {
		return self::query ( $sql, $type = '1' );
	}
	
	
	/**
	 * 作用:获取首行首列数据
	 * 返回:首行首列欄位值
	 * 类型:值
	 * 參数:select `a` from table where id='1'
	 */
	public function scalar($sql, $fieldname) {
		$row = self::query ( $sql, $type = '0' );
		return $row [$fieldname];
	}
	
	
	/**
	 * 获取记录总数
	 * 返回:记录数
	 * 类型:数字
	 * 參数:$db->fetRow($table,$field,$where);
	 */
	public function fetRowCount($table, $field = '*', $where = false) {
		$sql = "SELECT COUNT({$field}) AS num FROM $table";
		$sql .= ($where) ? " WHERE $where" : '';
		return self::query ( $sql, $type = '0' );
	}

	
	/**
	 * 获取记录总数
	 * 返回:记录数
	 * 类型:数字
	 * 參数:select count(*) from table
	 */
	public function getRowCount($sql) {
		return self::query ( $sql, $type = '2' );
	}


	/**
	 * 设置是否为调试模式 
	 */
	public function setDebugMode($mode = true) {
		return ($mode == true) ? self::$debug = true : self::$debug = false;
	}

	
	/**
	 * 捕获PDO错误信息
	 * 返回:出错信息
	 * 类型:字串
	 */
	private function getPDOError($sql) {
		self::$debug ? self::errorfile ( $sql ) : '';
		if (self::$DB->errorCode () != '00000') {
			$info = (self::$stmt) ? self::$stmt->errorInfo () : self::$DB->errorInfo ();
			echo (self::sqlError ( 'mySQL Query Error', $info [2], $sql ));
			exit ();
		}
	}
	
	private function getSTMTError($sql) {
		self::$debug ? self::errorfile ( $sql ) : '';
		if (self::$stmt->errorCode () != '00000') {
			$info = (self::$stmt) ? self::$stmt->errorInfo () : self::$DB->errorInfo ();
			echo (self::sqlError ( 'mySQL Query Error', $info [2], $sql ));
			exit ();
		}
	}

	/**
	 * 写入错误日志
	 */
	private function errorfile($sql) 
	{
		$sql = str_replace ( array (
				"\n",
				"\r",
				"\t",
				"  ",
				"  ",
				"  "
		), array (
				" ",
				" ",
				" ",
				" ",
				" ",
				" "
		), $sql );
		
		$ip = $this->getip();
		$time = date("Y-m-d H:i:s");
		$message = "\r\n$sql" . "\r\n客户IP:$ip" . "\r\n时间 :$time" . "\r\n";
		
		$errorfile = LOG_PATH . date("YmdH") . ".php";
		
		if (! file_exists ( $errorfile )) {
			$fp = file_put_contents ( $errorfile, "<?PHP exit('Access Denied'); ?>\n" . $message );
		} else {
			$fp = file_put_contents ( $errorfile, "\n" . $message, FILE_APPEND );
		}
	}
	

	/**
	 * 作用:运行错误信息
	 * 返回:运行错误信息和SQL語句
	 * 类型:字符
	 */
	private function sqlError($message = '', $info = '', $sql = '') 
	{		 
		$html = '';
		if ($message) {
			$html .=  $message;
		}
		 
		if ($info) {
			$html .= '，SQLID: ' . $info ;
		}
		if ($sql) {
			$html .= "；ErrorSQL: " . $sql;
		}
		 
		throw new Exception($html);
	}
     
	

    function getip() 
    {
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
	
	
}

?>