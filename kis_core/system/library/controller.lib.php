<?php
/**
 * 控制器基类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class controller {

	const controller_ext = '_Controller';
	const action_ext = '_Action';

	/**
	 * 最后调用的控制器
	 * @var [type]
	 */
	protected static $last_controller;

	/**
	 * 最后调用的action
	 * @var [type]
	 */
	protected static $last_action;

	/**
	 * 所有被调用的action
	 * @var array
	 */
	protected static $called_action = array();

	protected static $debug_on = false;

	/**
	 * 构造一下
	 * @param null
	 */
	public function __construct($argv = null){
		//parent::__construct();
	}

	public function __destruct(){

		if(method_exists('hlp_stat', 'PageView')){
			$pv_name = self::$last_controller . '__' .self::$last_action;
			hlp_stat::PageView($pv_name,'','',NULL,FALSE);
		}
	}

	/**
	 * 调用某个控制器下的某
	 * @param  string $controller 控制器名称
	 * @param  string $action     action名称
	 * @param  array  $params     传入action的参数
	 * @return null
	 */
	public static function call($controller, $action, $params = array() ){
        $controller_name = '';
		$controller_name .= $controller . self::controller_ext;
		$action_name  = $action . self::action_ext;
		if(class_exists($controller_name)) {
			
			if(method_exists($controller_name, $action_name)) {
				$ctl = new $controller_name;
				self::$called_action[] = $controller .'.'. $action ;
				self::$last_controller = $controller;
				self::$last_action = $action;
                // [peven] 2018-12-21 增加执行操作前后回调
                if (is_callable(array($ctl, '__actionBefore'))) {
                    $beforeResult = call_user_func(array($ctl, '__actionBefore'));
                }
                if (true){//!isset($beforeResult) || $beforeResult !== false) {
                    call_user_func_array(array($ctl, $action_name), (array) $params);
                }
                if (is_callable(array($ctl, '__actionAfter'))) {
                    call_user_func(array($ctl, '__actionAfter'));
                }
			} else {
				Response::error(404, "action not found:{$controller_name}::{$action_name}");
				// throw new Exception("method not found:{$controller_name}::{$action_name}", E::EC_FW_ACTION_NF);
			}
		} else {
			Response::error(404, "controller not found:{$controller_name}");
			// throw new Exception("controller not found:{$controller_name}", E::EC_FW_CTL_NF);
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

	public static function debug($on = null){
		if($on === NULL) return self::$debug_on;
		self::$debug_on = $on;
		return self::$debug_on;
	}

	// public function __set($name, $value){
	// 	switch ($name) {
	// 		case '_debug':
	// 			//self::$debug_on = $value;
	// 			break;

	// 		default:
	// 			# code...
	// 			break;
	// 	}
	// }

	// public function __get($name){
	// 	switch ($name) {
	// 		case '_debug':
	// 			//return self::$debug_on ;
	// 			break;

	// 		default:
	// 			# code...
	// 			break;
	// 	}
	// }
}
