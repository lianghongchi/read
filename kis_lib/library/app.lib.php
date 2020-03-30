<?php
/**
 * App 类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_app
{


    private static $instances = [];


    private static $time = null;


	public static function getInstance($name)
	{
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = new $name;
        }
        return self::$instances[$name];
	}


    public static function getTime()
    {
        if (is_null(self::$time)) {
            self::$time = time();
        }
        return self::$time;
    }


}
