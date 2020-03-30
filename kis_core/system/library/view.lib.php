<?php

/**
 * 数据库操作类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 *
 * 公用函数
 *  //参数说明 view路径名称，带入的参数(array或stdClass)，是否输出到浏览器
 * 	view::load('example', $data);//直接输出到浏览器
 * 	view::load('example', $data, false);//仅返回内容,同view::make('example', $data);
 * 	view::make('example', $data);//返回内容
 *
 * 		$data['name'] = 'nds';
 * 		view::load('example', $data);
 *
 * 		$data = new   stdClass();
 * 		$data->name = 'NDS';
 * 		view::make('example', $data);
 */

class View
{

    /**
     * 最后加载的View
     * @var string
     */
    protected static $last_load = null;

    /**
     * 所有被加载的View
     * @var array
     */
    protected static $views = array();

    /**
     * 输出View给浏览器 等同于View::make($view, $params, FALSE);
     * @param  string  $view   View路径名
     * @param  array/stdClass  $params 参数
     * @param  boolean $output 是否输出给浏览器 默认是
     * @return string          返回View最终内容
     */
    public static function load($view, $params = array(), $output = true, $public_view = false) {
        return self::make($view, $params, $output, $public_view);
    }

    public static function loadPub($view, $params = array(), $output = true, $public_view = true) {
        return self::make($view, $params, $output, $public_view);
    }

    /**
     * 生成View内容并返回
     * @param  string  $view   View路径名
     * @param  array/stdClass  $params 参数
     * @param  boolean $output 是否输出给浏览器 默认否
     * @return string          返回View最终内容
     */
    public static function make($___nds_view, $___nds_params = array(), $output = false, $public_view = false) {
        self::$last_load = $___nds_view;
        if (empty(self::$views[$___nds_view])) {
            $___nds_view_path = loader::getViewPathForLoad($___nds_view, $public_view);
            if ($___nds_view_path) {
                self::$views[$___nds_view_path] = file_get_contents($___nds_view_path);
            } else {
                return '';
            }
            // var_dump($___nds_view, $___nds_view_path, self::$views[$___nds_view_path]);
        }
        if (is_array($___nds_params)) {
            extract($___nds_params);
        } elseif (is_object($___nds_params)) {
            extract(get_object_vars($___nds_params));
        }
        ob_start();
        //include loader::getViewPathForLoad($___nds_view);
        if (1 || KIS_TRACE_MODE) {
            include $___nds_view_path;
        } else {
            eval('?>' . self::$views[$___nds_view_path]);
        }
        $content = ob_get_clean();
        if ($output) {
            echo $content;
        }
        return $content;
    }

    /**
     * 获取最后加载的View
     * @return string 最后加载的view
     */
    public static function lastLoad() {
        return self::$last_load;
    }

    /**
     * 魔术方法 返回私有静态成员的静态方法
     * @param  string $func      魔术静态方法 即要访问的私有静态成员名
     * @param  array  $arguments 预留参数 可选
     * @return mix            	 若存在该私有静态成员则返回
     */
    public static function __callStatic($func, $arguments = array()) {
        if (isset(self::$$func)) {
            return self::$$func;
        }
    }

}
