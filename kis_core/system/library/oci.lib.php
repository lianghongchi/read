<?php
/**
 * 数据库操作类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class Oci
{

	//默认的数据库驱动，配置中不包含时使用
	const default_driver = 'mysql';
	//默认的数据库端口，配置中不包含时使用
	const default_port = 3306;

	/**
	 * PDO实例数组
	 * @var array[PDO]
	 */
	protected static $instances;

	/**
	 * @var oci
	 */
	protected static $oci;

	/**
	 * @var string 马上要执行的SQL语句
	 */
	protected static $sql;

	/**
	 * 被尝试执行的SQL
	 * @var array
	 */
	protected static $executed_sql = array();

	/**
	 * @var 执行SQL操作返回的错误
	 */
	protected static $error = array();
	/**
	 * 数据库哈希配置信息
	 * @var array
	 */
	public static $hash_config = array();
	/**
	 * 存储hash出来后直接用于操作数据库的host username password database_name table_name等信息
	 * @var array
	 */
	protected static $config = array();

	/**
	 * self::SELECT()同名函数 查询多行数据
	 * @param  string $sql    要执行的SQL
	 * @param  array  $params 要绑定的参数
	 * @return array/false    二维数组或失败
	 */
	public static function getAll($sql){
		return self::select($sql);
	}

	/**
	 * 查询一行 as same as DB::SELECT($sql, $param)[0]
	 * @param  string $sql    要执行的SQL
	 * @param  array  $params 要绑定的参数
	 * @return array/false    一维数组或失败
	 */
	public static function getOne($sql){
		$res =  self::select($sql);
		if($res) {
			return $res[0];
		}
		return false;
	}

	/**
	 * 查询多行数据
	 * @param  string $sql    要执行的SQL
	 * @param  array  $params 要绑定的参数
	 * @return array/false    二维数组或失败
	 */
	public static function select($sql){
		if(self::Query($sql)){
			while ($row = oci_fetch_array(self::$oci, OCI_ASSOC)) {
				$ret[] = $row;
			}
			return $ret;
		} else {
			self::trigger_error();
			return false;
		}
	}

	/**
	 * 执行一条SQL语句，并返回执行结果
	 * @param string SQL语句
	 * @return bool  是否成功
	 */
	public static function Query($sql){


		//创建连接
		$time = microtime(true);
		if(empty(self::$instances)){
			self::getInstance();
		}

		self::$oci = oci_parse(self::$instances, $sql);
		$ret = oci_execute(self::$oci, OCI_DEFAULT);
		//var_dump($row = oci_fetch_array($oci, OCI_ASSOC));

		$time = microtime(true) - $time;
		if($ret) {
			self::$executed_sql[] = array('sql'=>$sql, 'time'=>$time);
		} else {
			self::$error[] = oci_error(self::$oci);
			self::$executed_sql[] = array('sql'=>$sql, 'time'=>$time, 'error'=>count(self::$error)-1);
			self::trigger_error(E::EC_DB_QUERY_ERR, self::$error);
		}
		return $ret;
	}



	/**
	 * 根据具体的参数创建PDO实例
	 * @param 配置信息
	 * @return pdo PDO实例的KEY self::$instances[$key]
	 */
	protected static function getInstance($config = null){
		$username = 'unilg';
		$password = 'cwcvdk7n1nfrfu';
		$host = '192.168.7.175';//192.168.7.180 备 192.168.7.175
		$port = '1521';
		self::$instances = oci_connect($username, $password, "(DEscriptION=(ADDRESS=(PROTOCOL =TCP)(HOST={$host})(PORT = {$port}))(CONNECT_DATA =(SID=udct)))");
		return $key;
	}




	/**
	 * 内部错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	protected static function trigger_error($errno, $error){
		if(KIS_TRACE_MODE) {
			var_dump($errno, $error);
		}
	}

	/**
	 * 魔术方法 返回私有静态成员的静态方法
	 * @param  string $func      魔术静态方法 即要访问的私有静态成员名
	 * @param  array  $arguments 预留参数 可选
	 * @return mix            	 若存在该私有静态成员则返回
	 */
	public static function __callStatic($func, $arguments = array()){
		if(isset(self::$$func)) {
			return self::$$func;
		}
	}
}
