<?php
/**
 * mc 类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */
class Mc
{

	const TIMEOUT_CONNECT 	= 200;//连接超时时间200毫秒
	const TIMEOUT_SEND		= 200;//    超时时间200毫秒
	const TIMEOUT_RECV		= 200;//    超时时间200毫秒
	const TIMEOUT_OP		= 0.2;//操作超时时间0.2秒

	/**
	 * 实例，每个实例（$group 组）只connect一次，后面访问直接共享连接
	 * @var array
	 */
	protected static $instances = array();

	protected static $group = null;
	protected static $operation = null;

	/**
	 * 设置一个memcached的值
	 * @param string  $group  memcached服务器组
	 * @param string  $key    memcached的key
	 * @param mix  	  $value  要设置的值
	 * @param integer $expire 生存期
	 * @return bool           设置是否成功
	 */
	public static function set($group, $key, $value, $expire = 300, $log = true){
		self::$operation = 'set';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->set(self::escape_key($group.'_'.$key), $value, $expire);
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['set'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_SET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}elseif( ! $res ) {
				self::trigger_error(E::EC_MC_SET_ERR, $group.'_'.$key);
			}
			return $res;
		}
		return false;
	}

	/**
	 * 获取一个memcached的值
	 * @param string  $group  memcached服务器组
	 * @param string  $key    memcached的key
	 * @return bool           返回结果
	 */
	public static function get($group, $key){
		self::$operation = 'get';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->get(self::escape_key($group.'_'.$key) );
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['get'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_GET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}

	/**
	 * 删除一个memcached
	 * @param  string  $group  删除组
	 * @param  string  $key    删除的key
	 * @param  integer $expire 等待删除的秒数或者时间戳，默认0 立即删除
	 * @return bool            操作结果
	 */
	public static function delete($group, $key, $expire = 0 ){
		self::$operation = 'del';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->delete(self::escape_key($group.'_'.$key), $expire );
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['del'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_DEL_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}

	/**
	 * 增加memcached的值
	 * @param string  $group  memcached服务器组
	 * @param string  $key    memcached的key
	 * @param int  	  $value  要增加的步长，默认为1
	 * @return bool/int       成功时返回元素新的值， 或者在失败时返回 FALSE
	 */
	public static function incr($group, $key, $increment_value = 1){
		self::$operation = 'incr';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->increment(self::escape_key($group.'_'.$key), $increment_value);
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['incr'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_GET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}

	/**
	 * 减少memcached的值
	 * @param string  $group  memcached服务器组
	 * @param string  $key    memcached的key
	 * @param int  	  $value  要减少的步长，默认为1
	 * @return bool/int       成功时返回元素新的值， 或者在失败时返回 FALSE
	 */
	public static function decr($group, $key, $decrement_value = 1){
		self::$operation = 'decr';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->decrement(self::escape_key($group.'_'.$key), $decrement_value);
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['decr'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_GET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}


	public static function getstats($group ){
		self::$operation = 'stat';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->getStats( );
			$info['timer'] = microtime(true)-$info['timer'];
			!KIS_CORE_DAEMON && self::$instances[$group]['counter']['decr'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_GET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}
	public static function flush($group ){
		self::$operation = 'flush';
		if(self::InitInstans($group)) {
			$info['key'] = $key;
			$info['timer'] = microtime(true);
			$res = self::$instances[$group]['obj']->flush( );
			$info['timer'] = microtime(true)-$info['timer'];
			self::$instances[$group]['counter']['decr'][] = $info;
			if( $info['timer'] > self::TIMEOUT_OP ){
				self::trigger_error(E::EC_MC_GET_TIMEOUT, $group.'_'.$key, $info['timer']);
			}
			return $res;
		}
		return false;
	}

	/**
	 * 获取memcache状态信息，读写次数等
	 * @param  string $type 返回类型 默认HTML
	 * @return string/mix   返回状态信息
	 */
	public static function getStat($type = 'html'){
		$stat = array();
		if(!self::$instances) {
			return ;
		}
		foreach (self::$instances as $key => $value) {
			$stat['total_time_cost'] +=$value['timer'];
			$stat['group'][] = array('group'=>$key, 'timer'=>$value['timer'], 'server_nums' => count($value['cfg']));
			foreach ($value['counter'] as $k => $v) {
				foreach ($v as $k2 => $v2) {
					$stat['operation_total']++;
					$stat['total_time_cost'] +=$v2['timer'];
					$stat['operation'][$key][$k]++;
					$stat['operation'][$key]['total']++;
				}

			}

		}
		switch ($type) {
			case 'html':
				$time = substr($stat['total_time_cost'], 0, 6);
				$html = "	Total Cost Time : {$time}s<br/>\n
						Total Operation Times: {$stat['operation_total']}<br/>\n";

				foreach ($stat['operation'] as $key => $value) {
					$html .="group [{$key}]'s Operations and times:(total {$value['total']})<br/>\n---------";
					foreach ($value as $k => $v) {
						if($k == 'total') continue;
						$html .="{{$k}}: {$v} ,";
					}
					$html .="<br/>\n";
				}
				return $html;
				break;

			default:
				break;
		}
		return $stat;
	}

	/**
	 * 获取或创建一个memcached连接实例
	 * @param sring $group memcached组
	 */
	protected static function InitInstans($group){
		self::$group = $group;
		// self::stat();
		if(!$group) {
			self::trigger_error(E::EC_MC_CFG_NK, 'memcache group unknown');
			return false;
		}
		if(empty(self::$instances[$group]['obj']) || (!self::$instances[$group]['obj'] instanceof Memcached)){
			self::$instances[$group]['cfg'] = config::get('memcached.'.$group);
			if(!self::$instances[$group]['cfg']) {//config error
				self::trigger_error(E::EC_MC_CFG_NF, E::$EC_MSG[E::EC_MC_CFG_NF] .':'. $group);
				return false;
			}
			self::$instances[$group]['timer'] = microtime(true);
			self::$instances[$group]['obj'] = new Memcached();
			self::$instances[$group]['obj']->setOption(Memcached::OPT_CONNECT_TIMEOUT, self::TIMEOUT_CONNECT);
			//i think it does not work.... TIMEOUT_SEND and TIMEOUT_RECV
			//self::$instances[$group]['obj']->setOption(Memcached::OPT_SEND_TIMEOUT, self::TIMEOUT_SEND);
			//self::$instances[$group]['obj']->setOption(Memcached::OPT_RECV_TIMEOUT, self::TIMEOUT_RECV);
			self::$instances[$group]['obj']->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, TRUE);
			if(!self::$instances[$group]['obj']->addServers(self::$instances[$group]['cfg'])){
				self::$instances[$group]['timer'] = microtime(true)-self::$instances[$group]['timer'];
				self::trigger_error(E::EC_MC_SERVER_ERR, E::$EC_MSG[E::EC_MC_SERVER_ERR]);
				return false;
			}
			self::$instances[$group]['timer'] = microtime(true)-self::$instances[$group]['timer'];

			if(self::$instances[$group]['timer'] > self::TIMEOUT_OP){
				//TODO timeout... you need to do something here..
				self::trigger_error(E::EC_MC_CONN_TIMEOUT, E::$EC_MSG[E::EC_MC_CONN_TIMEOUT] .':'. self::$instances[$group]['timer']);
			}
		}
		return true;
	}

	/**
	 * 关闭连接 参数为空则关闭所有  controller析构函数中有调用
	 * @param  [type] $group [description]
	 * @return [type]        [description]
	 */
	public static function quit($group = null){

		if( $group ) {
			if(isset(self::$instances[$group]['obj'])) {
				return self::$instances[$group]['obj']->quit();
			}
			else {
				return false;
			}

		} else {
			$res = true;
			foreach (self::$instances as $group => $obj) {
				$ret = self::$instances[$group]['obj']->quit();
				!$ret && $res = $ret;
			}
			return $res;
		}
	}

	/**
	 * 过滤KEY里面的空格
	 * @param  string $key 要过滤的key
	 * @return string      返回过滤后的key
	 */
	public static function escape_key($key){
		return str_replace( ' ', '_|_', $key);
	}

	/**
	 * 内部错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	protected static function trigger_error($errno, $error){
		// var_dump($errno, $error);
		is_array($error) && $error = json_encode($error);
		if(isset($_SERVER['REQUEST_URI'])) {
			$uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		} else {
			$uri = KIS_SCRIPT_NAME;
		}
		$err_msg = E::$EC_MSG[$errno];
        
        E::sendErrorLog('/nds/mc_error', "{$errno}\t{$err_msg}\t{$error}\t{$uri}");
		E::trigger($errno);

		/**
		 * 异常统计 用于监控报警
		 */
		if(method_exists('hlp_stat', 'memcache_error_report')) {
			hlp_stat::memcache_error_report(self::$group, $error, E::$EC_MSG[$errno]);
		}
	}

	/**
	 * 调用次数统计
	 * @return [type] [description]
	 */
	protected static function stat(){
		if(method_exists('hlp_stat', 'action')) {
			if (isset($_SERVER["HTTP_HOST"])) {
	            $stat_key = $_SERVER["HTTP_HOST"] . '_' . php_uname('n');
	        } else {
	            $stat_key = php_uname('n');
	        }
	        hlp_stat::Action('mc_op_'. php_uname('n'), 'log', 'mc_op', self::$operation, null, false);

		}
	}

}
