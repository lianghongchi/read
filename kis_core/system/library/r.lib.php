<?php
/**
 * Redis操作类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class R
{

	const connect_timeout = 0.2;

	/**
	 * redis连接实例 按组保存，以便下次使用
	 * @var array
	 */
	protected static $instances = array();

	/**
	 * 构造函数 加载组配置并创建连接实例
	 * @param string  $group redis组名
	 * @param integer $db    数据库 默认0
	 */
	public function __construct($group = 'dev', $db = 0){
		$this->group = $group;
		$this->db = $db;
		//$this->getInstance();

		// $this->redis_connect($group);
	}

	/**
	 * 析构函数
	 */
	public function __destruct(){
		// try{
		// 	// $this->redis_->close();
		// }catch(Exception $e) {
		// }
	}


	/**
	 * 获取或创建一个实例
	 * @param  boolean $overwrite 是否覆盖已存在的实例，要重连时覆盖
	 * @return Redis             Redis实例
	 */
	protected function getInstance($hash_key ='', $overwrite = false ,$need_md5 = true){
		$group_config = config::get('redis.'.$this->group);
		if(!$group_config) {
			return false;
		}
		$config = isset($group_config['master']) ?: $group_config;
		if(count($config)==1){
			$instance_key_hash = 0;
			//$config = $config[0];
		} else {
			$need_md5 && $hash_key = md5($hash_key);
			$instance_key_hash = floor(hexdec(substr( ($hash_key), 0, 2))*count($config)/256);
			//$config = $config[floor(hexdec(substr(md5($hash_key), 0, 1))*count($config)/16)];
		}
		$config = $config[$instance_key_hash];
		$instance_key = $this->group . ':' . $instance_key_hash;

		if(!empty(self::$instances[$instance_key]) && !$overwrite) {
			// if($ping) {
			// 	self::$instances[$this->group]->ping();
			// }
			return $instance_key;
			// return self::$instances[$instance_key];
		}


		try{
			$time = microtime(true);
			self::$instances[$instance_key] = new Redis();
			self::$instances[$instance_key]->connect($config['host'], $config['port'], self::connect_timeout);
			if (microtime(true)-$time > self::connect_timeout) {
				self::trigger_error(2, microtime(true)-$time );
			}
			// if($this->db) {
			// 	$time = microtime(true);
			// 	self::$instances[$instance_key]->select($this->db);
			// 	if (microtime(true)-$time > self::connect_timeout) {
			// 		self::trigger_error(21, microtime(true)-$time );
			// 	}
			// }
		}catch(Exception $e) {
			self::trigger_error(1,$e);
			return false;
		}
		return $instance_key;
	}

	/**
	 * 屏蔽Redis的connect
	 * @param  [type] $host    [description]
	 * @param  [type] $port    [description]
	 * @param  [type] $timeout [description]
	 * @return [type]          [description]
	 */
	public function connect($host = null, $port = null, $timeout = null){
		//屏蔽
	}

	/**
	 * 屏蔽Redis的select
	 * @param  integer $db [description]
	 * @return [type]      [description]
	 */
	public function select($hash=0, $db = 0){
		//屏蔽
		//return false;
		$instance_key = $this->getInstance($hash,false,false);
		return self::$instances[$instance_key]->select($db);
	}

	public function info($hash=0){
		$instance_key = $this->getInstance($hash,false,false);
		return self::$instances[$instance_key]->info();
	}

	public function keys($hash=0, $keys){
		$instance_key = $this->getInstance($hash,false,false);
		return self::$instances[$instance_key]->keys($keys);
	}

	public function flushdb($hash=0){
		$instance_key = $this->getInstance($hash,false,false);
		return self::$instances[$instance_key]->flushdb();
	}

	/**
	 * 魔术方法，调用redis
	 * @param  [type] $func_name [description]
	 * @param  array  $arguments [description]
	 * @return [type]            [description]
	 */
	public function __call($func_name, $arguments = array()){
		if(!is_string($arguments[0])){
			return false;
		}
		// $instance_key = self::getInstanceKey($this->group, $arguments[0]);
		$instance_key = $this->getInstance($arguments[0]);
		if(method_exists(self::$instances[$instance_key], $func_name)){
			$time = microtime(true);
			try {
				$ret = call_user_func_array(array(self::$instances[$instance_key], $func_name), (array) $arguments);
			} catch (Exception $e) {
				self::trigger_error(3, $func_name."\t".implode("\t", $arguments)."\t".$e);
				usleep(2000);
				//尝试重连后重试
				$this->getInstance($arguments[0],true);
				try {
					$ret = call_user_func_array(array(self::$instances[$instance_key], $func_name), (array) $arguments);
				} catch (Exception $e) {
					self::trigger_error(4, $func_name."\t".implode("\t", $arguments)."\t".$e);
				}

			}
			if (microtime(true)-$time > self::connect_timeout) {
					self::trigger_error(5, microtime(true)-$time );
			}
			return $ret;
		} else {
			throw new Exception("Method Not Fount", 9999);
		}
	}



	/**
	 * 内部错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	protected static function trigger_error($errno = 0, $msg = ''){
		switch ($errno) {
			case 1:
				$page = 'ConnectErr';
				break;
			case 2:
				$page = 'ConnectTimeout';
				break;
			case 21:
				$page = 'SelectTimeout';
				break;
			case 3:
				$page = 'OperationErr';
				break;
			case 4:
				$page = 'ReOperationErr';
				break;
			case 5:
				$page = 'OperationTimeout';
				break;
			default:
				$page = 'unknown';
				break;
		}
		// Stat::Action(
		// 	$action_name = 'RedisError_'.php_uname('n'),
		// 	$channel = 'log',
		// 	$application ='RedisError',
		// 	$page ,
		// 	$account = NULL,
		// 	$auto_check_login = FALSE
		// 	);
        E::sendErrorLog('/nds/redis_r_error', "{$page}\t{$msg}");
	}

}
