<?php
/**
 * autoload class
 * 自动加载类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 */

class Loader
{

	/**
	 * The loaded file list
	 *
	 * @var array(file_path => success_loaded)  e.g.  array('/dirname/file.php' => true)
	 */
	protected static $loaded_files_ = array(__FILE__ => true);

	const model 	= 'model';
	const view 		= 'view';
	const controller= 'controller';
	const library 	= 'library';
	const helper 	= 'helper';
	const config 	= 'config';
    const service   = 'service';
	const model_ext = '.mod';
	const view_ext	= '.view';
	const controller_ext= '.ctl';
	const library_ext 	= '.lib';
	const helper_ext 	= '.hlp';
	const config_ext	= '.cfg';
    const service_ext   = '.sv';

	/**
	 * 加载公共config
	 * @param  string $path 文件路径
	 * @return [type]       [description]
	 */
	public static function loadConfig($path, $public_config = true, $ignore_not_found = false){
		// var_dump(str_replace('_', DS, $path).self::config_ext.EXT);
		if($public_config) {
			return static::_load_file($path .self::config_ext.EXT, KIS_CORE_LIB_ROOT.self::config);
		} else {
			return static::_load_file($path .self::config_ext.EXT, KIS_APPLICATION_ROOT.self::config, true, $ignore_not_found);
		}
	}

	/**
	 * 获取view文件路径，用于View类加载;
	 * @param  string $path 文件路径名
	 * @return string       文件完整绝对路径
	 */
	public static function getViewPathForLoad($path, $public_view = false){
		if($public_view) {
			$file = KIS_CORE_LIB_ROOT.loader::view.DS.self::clean_path($path).loader::view_ext.EXT;
		} else {
			$file = KIS_APPLICATION_ROOT.loader::view.DS.self::clean_path($path).loader::view_ext.EXT;
		}

		$file_path = $file;
		while (!empty(self::$loaded_files_[$file_path])) {
			$file_path = $file.'.'. ++$i;
		}
		if(file_exists($file)){
			self::$loaded_files_[$file_path] = array(memory_get_usage(),memory_get_usage(true), microtime(true));
			return $file;
		} else {
			self::$loaded_files_[$file_path] = false;
			return false;
		}
	}

	/**
	 * 获取所有加载了的文件
	 * @return array 所有已加载的文件列表
	 */
	public static function getLoadedFiles(){
		return self::$loaded_files_;
	}
	/*
	|--------------------------------------------------------------------------
	| PHP auto load file when use a class
	|--------------------------------------------------------------------------
	|
	*/
	public static function auto($class){
		switch (strtolower($class)) {
				case 'config':
				case 'controller':
				case 'db':
				case 'dba':
				case 'debug':
				case 'e':
				case 'log':
				case 'mc':
				case 'model':
				case 'oci':
				case 'r':
				case 'router':
				case 'response':
				case 'view':
				//load system library
						return static::_load_file($class.self::library_ext.EXT, KIS_CORE_SYSTEM_ROOT.'library');
					break;

			default:
			/*
				// //TODO USE NAMESPACE ..
				// if(strstr($class, '\\')) {
				// 	$class_info = explode('\\', $class);
				// 	switch (strtolower($class_info[0])) {
				// 		case 'nds':
				// 			return static::_load_file(implode(DS, array_slice($class_info, 1)).self::library_ext.EXT, KIS_CORE_LIB_ROOT.'system'.DS.'library');
				// 			break;
				// 		default:

				// 			break;
				// 	}
				// 	break;
				// }
				*/
				return self::loadClassFile($class);
				break;
		}
	}

	public static function getPathByInfo($path_prefix, $path_info, $ext = EXT, $implode = DS, $implode_origin = '_'){
			// if($_GET['ring'])  var_dump($path_prefix, $path_info, $ext);
		// foreach ($path_info as $key => $path) {
		// 	if (!preg_match('/^[\w]+$/', $path)) {
		// 		return false;
		// 	}
		// 	if(is_file($path_prefix.DS.strtolower($path).$ext)) {
		// 		// var_dump($path_prefix.DS.strtolower($path).$ext);
		// 		return $path_prefix.DS.strtolower($path).$ext;
		// 	}elseif (is_dir($path_prefix.DS.strtolower($path))) {
		// 		$path_prefix .= DS . strtolower($path);
		// 	} else{
		// 		// var_dump($path_info, $key);
		// 		return $path_prefix.DS .strtolower(implode($implode_origin, array_slice($path_info, $key))).$ext;
		// 	}
		// }
        // peven 修改文件加载方式 2018/8/11
        foreach ($path_info as $key => $path) {
			if (!preg_match('/^[\w]+$/', $path)) {
				return false;
			}
            $path_prefix .= DS . strtolower($path);
		}

        return $path_prefix . $ext;
	}

	/**
	 * 加载类声明文件
	 * @param  string $class 类名
	 * @return bool        加载是否成功
	 */
	protected static function loadClassFile($class){
		$class_info = explode('_', $class);
		switch (strtolower($class_info[0])) {

			//load library
			case 'lib':
				return static::loadPubLibrary(array_slice($class_info, 1));
				break;
			case 'hlp':
				return static::loadPubHelper(array_slice($class_info, 1));
				break;
            // peven 2018/11/29 - 添加公共model层
            case 'model':
				return static::loadPubModel(array_slice($class_info, 1));
            // peven 2018/12/12 - 添加公共service层
            case 'service':
                return static::loadPubService(array_slice($class_info, 1));
				break;
			default:
				switch (strtolower(end($class_info))) {
					case 'model':
						//var_dump('mmm',$class_info,array_slice($class_info, 0, -1));
						self::loadModel(implode(DS, array_slice($class_info, 0, -1)));
						break;
                    case 'service':
                        self::loadService(implode(DS, array_slice($class_info, 0, -1)));
                        break;
					case 'lib':
						self::loadLibrary(array_slice($class_info, 0, -1));
						break;
					case 'controller':
						//var_dump($class_info,implode(DS, array_slice($class_info, 0, -1)));
						// if($_GET['ring'])self::loadController(array_slice($class_info, 0, -1));else
						// self::loadController(implode(DS, array_slice($class_info, 0, -1)));
						self::loadController(array_slice($class_info, 0, -1));
						break;
					case 'hlp':
						self::loadHelper(array_slice($class_info, 0, -1));
						break;
					default:
						#TODO  load nothing.  trigger error...
						break;
				}
				break;
		}
	}

	//加载公共使用的类 类名及类文件名命名方式需要对应起来  为了方便所有人 强制规范！
	//类名格式：lib_+[<目录名>_[<目录名>_]]+<文件名前缀>.lib.php
	//类名示例：lib_user_info  		对应的文件路径  KIS_CORE_LIB_ROOT.'library/user/info.lib.php'
	//          lib_user_userinfo	对应的文件路径	KIS_CORE_LIB_ROOT.'library/user/userinfo.lib.php'
	/**
	 * @param $path 文件路径 不包含前缀目录和后缀名 KIS_CORE_LIB_ROOT.'library/user/info.lib.php' 只需要传user/info即可
	 */
	protected static function loadPubLibrary($path_info){
		return static::_load_file(self::getPathByInfo(KIS_CORE_LIB_ROOT.self::library, $path_info, self::library_ext.EXT));
		// return static::_load_file($path .self::library_ext.EXT, KIS_CORE_LIB_ROOT.self::library);
	}

	/**
	 * 加载公共helper
	 * @param  [type] $path_info [description]
	 * @return [type]            [description]
	 */
	protected static function loadPubHelper($path_info){
		return static::_load_file(self::getPathByInfo(KIS_CORE_LIB_ROOT.self::helper, $path_info, self::helper_ext.EXT));
	}

    /**
	 * 加载公共model
	 * @param  [type] $path_info [description]
	 * @return [type]            [description]
	 */
	protected static function loadPubModel($path_info){
		return static::_load_file(self::getPathByInfo(KIS_CORE_LIB_ROOT.self::model, $path_info, self::model_ext.EXT));
	}
    
    /**
     * 加载公共service
     * @param  [type] $path_info [description]
     * @return [type]            [description]
     */
    protected static function loadPubService($path_info){
        return static::_load_file(self::getPathByInfo(KIS_CORE_LIB_ROOT.self::service, $path_info, self::service_ext.EXT));
    }

	/**
	 * 加载频道helper
	 * @param  [type] $path_info [description]
	 * @return [type]            [description]
	 */
	protected static function loadHelper($path_info){
		return static::_load_file(self::getPathByInfo(KIS_APPLICATION_ROOT.self::helper, $path_info, self::helper_ext.EXT));
	}

	/**
	 * 加载model声明文件
	 * @param  string $path model路径名
	 * @return bool       是否成功
	 */
	protected static function loadModel($path){
		return static::_load_file($path .self::model_ext.EXT, 	KIS_APPLICATION_ROOT.self::model);
	}
    
    /**
     * 加载service声明文件
     * @param  string $path model路径名
     * @return bool       是否成功
     */
    protected static function loadService($path){
        return static::_load_file($path .self::service_ext.EXT,   KIS_APPLICATION_ROOT.self::service);
    }

	/**
	 * 加载library声明文件
	 * @param  string $path library路径名
	 * @return bool       是否成功
	 */
	protected static function loadLibrary($path_info){
		// if(strstr($path_info[0], 'demo'))var_dump($path_info,self::getPathByInfo(KIS_APPLICATION_ROOT.self::library, $path_info, self::library_ext.EXT));
		return static::_load_file(self::getPathByInfo(KIS_APPLICATION_ROOT.self::library, $path_info, self::library_ext.EXT));
		return static::_load_file($path .self::library_ext.EXT, KIS_APPLICATION_ROOT.self::library);
	}

	/**
	 * 加载controller声明文件
	 * @param  string $path controller路径名
	 * @return bool       是否成功
	 */
	protected static function loadController($path_info){
		// if($_GET['ring']) var_dump('expression',$path_info);
		// if($_GET['ring'])
		return static::_load_file(self::getPathByInfo(KIS_APPLICATION_ROOT.self::controller, $path_info, self::controller_ext.EXT));
		return static::_load_file($path_info .self::controller_ext.EXT, KIS_APPLICATION_ROOT.self::controller);
	}


	/**
	 * 加载文件
	 * @param  string  $path        文件路径名
	 * @param  string  $prefix_path 文件路径前缀
	 * @param  boolean $load_once   是否仅加载一次
	 * @return bool                 是否加载成功
	 */
	protected static function _load_file($path, $prefix_path = '', $load_once = true, $ignore_not_found = false){
		if($prefix_path) {
			$file_path = realpath($prefix_path) .DS. strtolower(static::clean_path($path));
		} else {
			$file_path = realpath($path) ?:$path;
		}
		if(isset(self::$loaded_files_[$file_path]) && $load_once) {
			return true;
		} elseif(is_file($file_path)){
			self::$loaded_files_[$file_path] = array(memory_get_usage(),memory_get_usage(true), microtime(true));
			if($load_once) {
				return require_once $file_path;
			} else {
				return include  $file_path;
			}

		} else {
			//something wrong here , should trigger log or debug_track...
			//TODO
			// var_dump($path, $prefix_path,$file_path);
			if($ignore_not_found) {

			} else {
				self::trigger_error(E::EC_FS_FILE_NF, $file_path . ' NOT found.');
				self::$loaded_files_[$file_path] = false;
			}
			return false;
		}
	}

	/**
	 * 过滤异常路径
	 * @param  string $path 要过滤的路径
	 * @return string       过滤后的路径
	 */
	protected static function clean_path($path ) {
		return str_replace('..', '', $path);
	}

	//something wrong happened , we should trigger log or debug_track...
	/**
	 * 内部错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	protected static function trigger_error($errno, $error_msg){
		//something wrong here , should trigger log or debug_track...
		//TODO logger
        E::sendErrorLog('/nds/file_load_error', $errno . "\t" . $error_msg);
		//TODO stat...
		E::trigger(E::EC_FS_FILE_NF, $error_msg);
		//TODO id debug  show...
		//throw new Exception("$error_msg , No such file.", E::EC_FS_FILE_NF);

		if(KIS_TRACE_MODE && Controller::debug()) {
			echo "<pre>{$error_msg}</pre>";
		}
	}

}
