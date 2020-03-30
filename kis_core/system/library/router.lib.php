<?php
/**
 * 路由类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 * URL结构示例：
 * /controller/action/param[0]/param[1]/.../?xxx=v....
 * /dir_name/controller/action/param[0]/param[1]/.../?xxx=v....
 *
 * //启动路由，一般是框架系统执行
 * Router::route();
 * //获取controller
 * Router::getController();
 * //获取action
 * Router::getAction();
 */

class Router
{

	/**
	 * URL解析出来的路由字段信息
	 * @var array
	 */
	protected static $path_info = array();

	/**
	 * 路由到的controller
	 * @var string
	 */
	protected static $controller = null;

	/**
	 * 路由到的action
	 * @var string
	 */
	protected static $action = null;

	/**
	 * 路由出来的参数
	 * @var array
	 */
	protected static $params = array();

	/**
	 * 前缀参数
	 * @var [type]
	 */
	protected static $prefix_params = [];
	/**
	 * 标示是否已经完成工作
	 * @var array
	 */
	protected static $finished = null;

	/**
	 * 执行路由解析,并执行相应controller-action或触发404
	 * @return null
	 */
	public static function route(){
		static::before();
		if(KIS_CORE_DAEMON) {
			return;
		}

		self::parseUri();
		$ret = self::parseControllerActionParam(CORE_PREFIX_PARAMS_NUM);
		 // var_dump($ret,self::$path_info);
		if($ret && self::$controller) {
			// var_dump( self::$controller, self::$action);
			controller::call(self::$controller, self::$action, self::$params);
		} else {
			if(DEFAULT_CONTROLLER_404) {
				//if(method_exists(DEFAULT_CONTROLLER_404, $action_name))
				if(DEFAULT_ACTION_404) {
					self::$controller = DEFAULT_CONTROLLER_404;
					self::parseActionParam(-1);
					return controller::call(DEFAULT_CONTROLLER_404, DEFAULT_ACTION_404, self::$params);
				}
					self::parseActionParam(0);
					return controller::call(DEFAULT_CONTROLLER_404, self::$action, self::$params);
			}
			Response::error('404', 'controller not found  :'. (self::$controller ?: self::$path_info[intval(CORE_PREFIX_PARAMS_NUM)]) );
		}
		//i want to do something else....if no die before here....
		static::after();
	}

	/**
	 * 获取action
	 * @return string action名
	 */
	public static function getAction(){
		return self::$action;
	}

	/**
	 * 获取controller
	 * @return string controller名
	 */
	public static function getController(){
		return self::$controller;
	}

	/**
	 * 标示完成工作
	 * @return null
	 */
	public static function finish($finished = true){
		self::$finished = $finished;
	}

	/**
	 * 预处理
	 * @return null
	 */
	protected static function before(){
		config::get('macro');
		config::load('onload', true);
	}

	/**
	 * 扫尾工作
	 * @return null
	 */
	protected static function after(){
		//do something at last if no die before...
	}



	/**
	 * 解析URL到self::$path_info
	 * @return null
	 */
	protected static function parseUri(){
		// var_dump($_SERVER);
        // $pathinfo = /*!empty($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : */$_SERVER['REQUEST_URI'];
		$pathinfo = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';

		if (false !== ($pos = strpos($pathinfo, '?'))) {
			$pathinfo = substr($pathinfo, 0, $pos);
		}

		$pathinfo = trim(str_replace('//', '/', $pathinfo), '/');

		self::$path_info = explode('/', $pathinfo);

	}

	/**
	 * 从self::$path_info 解析出controller-action-params
	 * @return bool 是否解析成功
	 */
	protected static function parseControllerActionParam($prefix_params_num = 0){
		$prefix_params_num = intval($prefix_params_num);
		$key = 0;
		while($key < $prefix_params_num) {
			self::$prefix_params[] = self::$path_info[$key];
			$key++;
		}
// var_dump(empty(self::$path_info[$prefix_params_num]));
		if(empty(self::$path_info[$prefix_params_num])) {
			if(DEFAULT_CONTROLLER_404 && !class_exists(DEFAULT_CONTROLLER.controller::controller_ext)) {
				self::$controller = DEFAULT_CONTROLLER_404;
				self::$action = DEFAULT_ACTION_404;
			} else {
				self::$controller = DEFAULT_CONTROLLER;
				self::$action = DEFAULT_ACTION;
			}
			return true;
		} else {
			$dir_name = KIS_APPLICATION_ROOT.Loader::controller;
			$file_name = '';
			foreach (self::$path_info as $key => $path) {
				if($key < $prefix_params_num) {
					// self::$prefix_params[] = $path;
					continue;
				}
				if (!preg_match('/^[\w]+$/', $path)) {
					return false;
				}
				// var_dump($key , $path);
				if(file_exists($dir_name)){
					if(file_exists($dir_name.DS.strtolower($path).loader::controller_ext.EXT )){
						self::$controller = implode('_', array_slice(self::$path_info, $prefix_params_num, $key - $prefix_params_num + 1));
						// if(empty(self::$path_info[$key + 1])) {
						// 	self::$action = DEFAULT_ACTION;
						// } else {
						// 	self::$action = self::$path_info[$key + 1];
						// 	empty(self::$path_info[$key + 1]) || self::$params = array_slice(self::$path_info, $key + 2);
						// }
						self::parseActionParam($key);
						return true;
					}
					$dir_name .= DS . strtolower($path);
					// var_dump($dir_name);
				}
			}
			return false;
		}
	}

	protected static function parseActionParam($key){
		if(empty(self::$path_info[$key + 1])) {
			self::$action = DEFAULT_ACTION;
		} else {
			self::$action = self::$path_info[$key + 1];
			if(DEFAULT_ACTION_404 && !method_exists(self::$controller.controller::controller_ext, self::$action .controller::action_ext)) {
				self::$action = DEFAULT_ACTION_404;
			}
			empty(self::$path_info[$key + 1]) || self::$params = array_slice(self::$path_info, $key + 2);
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
