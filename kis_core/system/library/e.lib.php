<?php
/**
 * 通用错误类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class E {

	//ErrorCode
	//File System
	const EC_FS_FILE_NF		= 101;//file not found

	//FramWork
	const EC_FW_CTL_NF		= 201;//controller not found
	const EC_FW_ACTION_NF	= 202;//controller-action not found
	const EC_FW_CONFIG_NF	= 211;//config var not found....

	//MemCached
	const EC_MC_CFG_NK 		= 301;//memcached config group UnKnown
	const EC_MC_CFG_NF 		= 301;//memcached config not found
	const EC_MC_SERVER_ERR	= 302;//memcached addserver(s) error
	const EC_MC_CONN_TIMEOUT= 303;//memcached connect timeout
	const EC_MC_SET_TIMEOUT	= 304;//memcached set timeout
	const EC_MC_SET_ERR		= 305;//memcached set return failed
	const EC_MC_GET_TIMEOUT = 307;
	const EC_MC_DEL_TIMEOUT = 308;

	//Database
	const EC_DB_HASH_ERR	= 401;
	const EC_DB_CONFIG_ERR	= 402;
	const EC_DB_CONFIG_CONFLICT	= 403;
	const EC_DB_QUERY_ERR	= 410;//database execute return false;
	const EC_DB_QUERY_TIMEOUT	= 420;//database execute return false;
	const EC_DB_CONN_ERR	= 430;//database connect return false;
	const EC_DB_READ_ONLY	= 490;//database readonly;

	/**
	 * 错误码对应的错误信息
	 * @var array
	 */
	public static $EC_MSG = array(
			E::EC_FS_FILE_NF 	=> 'File_Not_Found',
			E::EC_FW_CTL_NF 	=> 'Controller_Not_Found',
			E::EC_FW_ACTION_NF 	=> 'Action_Not_Found',
			E::EC_FW_CONFIG_NF 	=> 'Config_Not_Found',
			E::EC_MC_CFG_NK 	=> 'Memcache_Config_Unknown',
			E::EC_MC_CFG_NF 	=> 'Memcache_Config_File_Not_Fount',
			E::EC_MC_SERVER_ERR => 'Memcache_Connect_Error',
			E::EC_MC_CONN_TIMEOUT 	=> 'Memcache_Connect_Timeout',
			E::EC_MC_SET_TIMEOUT 	=> 'Memcache_Set_Timeout',
			E::EC_MC_SET_ERR 	=> 'Memcache_Set_ERR',
			E::EC_MC_GET_TIMEOUT 	=> 'Memcache_Get_Timeout',
			E::EC_MC_DEL_TIMEOUT 	=> 'Memcache_Del_Timeout',
			E::EC_DB_HASH_ERR 	=> 'EC_DB_HASH_ERR',
			E::EC_DB_CONFIG_ERR 	=> 'EC_DB_CONFIG_ERR',
			E::EC_DB_CONFIG_CONFLICT 	=> 'EC_DB_CONFIG_CONFLICT',
			E::EC_DB_QUERY_ERR 	=> 'EC_DB_QUERY_ERR',
			E::EC_DB_QUERY_TIMEOUT 	=> 'EC_DB_QUERY_TIMEOUT',
			E::EC_DB_CONN_ERR 	=> 'EC_DB_CONNECT_ERROR',
			E::EC_DB_READ_ONLY 	=> 'EC_DB_READ_ONLY',

			//E::EC_FS_FILE_NF => 'File_Not_Found',
		);

	/**
	 * 错误计数器  看看除了多时次错误
	 * @var array
	 */
	protected static $errors = array();

	/**
	 * 错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	public static function trigger($error_code, $error_msg = '', $ext_info = NULL){
		switch ($error_code) {
			case static::EC_FS_FILE_NF:
				// throw new Exception(static::$EC_MSG[static::EC_FS_FILE_NF] . $error_msg, $error_code);

				break;
			case static::EC_FW_CTL_NF:

				break;
			case static::EC_FW_ACTION_NF:

				break;
			case static::EC_MC_CFG_NK:

				break;
			case static::EC_MC_CFG_NF:

				break;
			case static::EC_MC_SERVER_ERR:

				break;

			default:
				# code...
				break;
		}
		empty(static::$errors[$error_code]) ? (static::$errors[$error_code] = 1) : (static::$errors[$error_code] ++) ;
	}

    /**
     * 发送错误日志
     * @param  string $msg 错误信息
     * @param  string $dir 路径， 根目录为 /tmp
     * @return [type] [description]
     */
    public static function sendErrorLog($fileName, $message)
    {
        // 检查参数
        if (!$message || !$fileName) {
            return false;
        }
        if (!is_string($message)) {
            $message = (array) $message;
        }

        if (stripos($fileName, '.', 0) !== false) {
            return false;
        }

        // 创建日志文件目录
        $dir = '/tmp/' . $fileName;
        $dir = dirname($dir);

        if (!is_dir($dir)) {
            if (!mkdir($dir, 0777, true)) {
                return false;
            }
        }
        $fileName = '/tmp/' . $fileName . '.' . date('Y-m-d') . '.log';

        // array格式转换格式
        if (is_array($message)) {
            $message = var_export($message, true);
        }

        // 日志标题
        $mtime = microtime(true);

        $content = '['.date('Y-m-d H:i:s', $mtime)." - {$mtime}] :  ##############################|\n{$message}\n\n";

        return error_log($content, 3, $fileName);
    }


	/**
	 * 获取发生的错误
	 * @return array
	 */
	public static function getErrors(){
		return static::$errors;
	}
}
