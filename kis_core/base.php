<?php
/**
 * NDS - PHP Simple Framwork
 * PHP VERSION >= 5.4
 *
 * @package     NDS
 * @version     1.0
 * @author      peven<qiufeng@dianjiemian.cn>
 * @since       2018-11-22
 */

if (isset($argv[1]) || (defined('KIS_CORE_DAEMON') && KIS_CORE_DAEMON )) {
    define('KIS_TRACE_MODE', false);
}

if (isset($argv[0])) {
    defined('KIS_SCRIPT_NAME') || define('KIS_SCRIPT_NAME', $argv[0]);
} else {
    defined('KIS_SCRIPT_NAME') || define('KIS_SCRIPT_NAME', null);
}
define('KIS_START_TIME', PHP_VERSION > '5.4' ? $_SERVER["REQUEST_TIME_FLOAT"] : microtime(true));

defined('DS')  || define('DS', DIRECTORY_SEPARATOR);
defined('EXT') || define('EXT', '.php');

/*if (php_uname('n') == 'BJMJQ-WEBDEV-212' ||
    php_uname('n') == 'BJMJQ-WEBDEV-10.80.2.193' ||
    php_uname('n') == 'BJMJQ-NEWPAY-WEB-001' ||
    php_uname('n') == 'BJMJQ-WM-WEB-10.80.2.53') {
    defined('KIS_ENV') || define('KIS_ENV', 'DEV');
}*/

//defined('KIS_ENV') || define('KIS_ENV', 'PRO');
//defined('KIS_ENV') || define('KIS_ENV', getenv("PROJECT_ENV"));
$_SERVER['KIS_ENV'] = 'DEV';
defined('KIS_ENV') || define('KIS_ENV', $_SERVER['KIS_ENV']);
/* 应用程序根目录 这个应该在外部定义 */
defined('KIS_APPLICATION_ROOT')       || define('KIS_APPLICATION_ROOT', $_SERVER['DOCUMENT_ROOT'] . DS);

/* 默认入口 */
defined('DEFAULT_CONTROLLER')         || define('DEFAULT_CONTROLLER', 'index');
defined('DEFAULT_ACTION')             || define('DEFAULT_ACTION', 'index');

/* 是否做 URL REWRITE 处理 */
defined('KIS_URL_REWRITE')            || define('URL_REWRITE', true);

/* 核心框架的位置, 默认为当前目录, 可以在外部先定义 */
defined('KIS_CORE_ROOT')              || define('KIS_CORE_ROOT', __DIR__ . DS);

defined('KIS_CORE_VERSION')           || define('KIS_CORE_VERSION',  2.0);

/* 核心框架的位置, 默认为当前目录, 可以在外部先定义 */
defined('KIS_CORE_SYSTEM_ROOT')       || define('KIS_CORE_SYSTEM_ROOT', __DIR__ . DS . 'system' . DS);

/* 公共库位置, 默认为核心框架的位置, 可以在外部先定义 */
defined('KIS_CORE_LIB_ROOT')          || define('KIS_CORE_LIB_ROOT', KIS_CORE_ROOT);

/* 是否处于调试模式, 可在外部定义，生产环境请关闭 */
if (KIS_ENV == 'DEV') {
    defined('KIS_TRACE_MODE')         || define('KIS_TRACE_MODE', true);
}
defined('KIS_TRACE_MODE')             || define('KIS_TRACE_MODE', false);

/* 是否处于后台脚本模式, 暂时没用到 */
defined('KIS_CORE_DAEMON')            || define('KIS_CORE_DAEMON', false);

/* 默认404 */
defined('DEFAULT_CONTROLLER_404')     || define('DEFAULT_CONTROLLER_404', null);
defined('DEFAULT_ACTION_404')         || define('DEFAULT_ACTION_404', null);

/* 前缀参数格式，用于HOME之类的用户名最为URI第一个参数的情况 */
defined('CORE_PREFIX_PARAMS_NUM')     || define('CORE_PREFIX_PARAMS_NUM', 0);

/* 是否处于后台脚本模式, 暂时没用到 */
defined('KIS_CORE_DB_QUERY_CALLBACK') || define('KIS_CORE_DB_QUERY_CALLBACK', false);

/* DB只读模式 */
defined('KIS_CORE_DB_READ_ONLY')      || define('KIS_CORE_DB_READ_ONLY', false);

// 设置时区
date_default_timezone_set('PRC');

if (KIS_TRACE_MODE) {
    error_reporting(E_ALL);
} else {
    error_reporting(E_ERROR|E_PARSE|E_CORE_ERROR|E_COMPILE_ERROR|E_USER_ERROR);
}

// 自动加载
require KIS_CORE_SYSTEM_ROOT . 'library/loader.lib.php';

// 注册自动加载
spl_autoload_register(array('Loader', 'auto'));

// 自定义异常处理
set_exception_handler(function($e) {

    $uri = isset($_SERVER['HTTP_HOST']) ? ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : KIS_SCRIPT_NAME;
    $msg = "{$uri}\n{$e->getLine()} => {$e->getFile()}\n{$e->getMessage()}";
    e::sendErrorLog('/nds/error/exception', $msg);

    if (KIS_TRACE_MODE) {
        d('throw exception ##################################################');
        d($e);
    }
    return true;
});
// 自定义错误处理
set_error_handler(function($code, $error, $file, $line) {
    //E::native($code, $error, $file, $line);
    $uri = isset($_SERVER['HTTP_HOST']) ? ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : KIS_SCRIPT_NAME;
    $msg = "{$uri}\n{$code} => {$line} => {$file}\n{$error}";

    e::sendErrorLog('/nds/error/error', $msg);

    if (KIS_TRACE_MODE) {
        d('throw error ##################################################');
        d(func_get_args());
    }

    return true;
});
// 捕获致命错误
register_shutdown_function(function() {

    if (!is_null($error = error_get_last())) {
        $uri = isset($_SERVER['HTTP_HOST']) ? ($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) : KIS_SCRIPT_NAME;
        $msg = "{$uri}\n{$error['type']} => {$error['line']} => {$error['file']}\n{$error['message']}";

        e::sendErrorLog('/nds/error/fatal', $msg);

        if (KIS_TRACE_MODE) {
            d('throw fatal_error ##################################################');
            d([$error, func_get_args()]);
            return ;
        }
    }
});
if (isset($_GET['__debug'])) {
    Controller::debug(true);
}

if (isset($argv[1])) {
    $_SERVER['REQUEST_URI'] = $argv[1];
    Controller::debug(false);
}

// 执行路由
Router::route();

// 后面都是调试用的
if (KIS_TRACE_MODE && Controller::debug()) {
    //echo '<pre>';
    echo '<div>File loadered:';
    foreach (loader::getLoadedFiles() as $key => $value) {
        $i++;
        if ($value) {
            $color = 'green';
            $value[0] = round($value[0] / 1024);
            $value[1] = round($value[1] / 1024);
            if ($value[2]) {
                $time_cost = substr($value[2] - KIS_START_TIME, 0, 6);
                $time_cost_last = $time_cost - $time_cost_last;
                if ($time_cost_last > 0.01) {
                    $color = 'Crimson';
                }
            }
            echo "\n<br /><font color='{$color}'>[{$i}]{$key} ok ({$value[0]}/{$value[1]}K used, {$time_cost_last}s, {$time_cost}s)</font>";
        } else {
            echo "\n<br /><font color='red'>[{$i}]{$key} failed</font>";
        }
    }
    $total_time = substr(microtime(true) - KIS_START_TIME, 0, 6);
    $total_memory_used = round(memory_get_usage() / 1024) . '/' . round(memory_get_usage(true) / 1024);
    $total_memory_used_peak = round(memory_get_peak_usage() / 1024) . '/' . round(memory_get_peak_usage(true) / 1024);

    echo "\n<br /><font color='green'>Total_memory_used :{$total_memory_used}K (peak: {$total_memory_used_peak}K), {$total_time}s</font>";
    echo '</div>';
    echo "<div>Error triggered:<br/>\n";
    foreach (E::getErrors() as $key => $value) {
        $msg = E::$EC_MSG[$key];
        echo "{$key} {$msg} {$value}<br/>\n";
    }
    echo '</div>';

    echo "<div>Memcached stat:<br/>\n";
    echo (MC::getStat());
    echo '</div>';
    //echo '</pre>';
    //
	echo DB::getStat();
}
// 执行完成
Router::finish();
