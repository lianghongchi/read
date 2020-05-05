<?php
if (KIS_ENV == 'DEV') {
    //默认的数据库ip、port
    //主库配置
    defined('__KIS_DB_PORT_ALL_1__') || define('__KIS_DB_PORT_ALL_1__', '3306');
    defined('__KIS_DB_HOST_DEFAULT_1__') || define('__KIS_DB_HOST_DEFAULT_1__', '192.168.0.4');
    defined('__KIS_DB_USER_DEFAULT_1__') || define('__KIS_DB_USER_DEFAULT_1__', 'dev');
    defined('__KIS_DB_PASS_DEFAULT_1__') || define('__KIS_DB_PASS_DEFAULT_1__', 'Freedom_Lession!@#$');

    //从库配置
    defined('__KIS_DB_PORT_ENTOURAGE_1__') || define('__KIS_DB_PORT_ENTOURAGE_1__', '3306');
    defined('__KIS_DB_HOST_ENTOURAGE_1__') || define('__KIS_DB_HOST_ENTOURAGE_1__', '192.168.0.4');
    defined('__KIS_DB_USER_ENTOURAGE_1__') || define('__KIS_DB_USER_ENTOURAGE_1__', 'dev');
    defined('__KIS_DB_PASS_ENTOURAGE_1__') || define('__KIS_DB_PASS_ENTOURAGE_1__', 'Freedom_Lession!@#$');
} else if (KIS_ENV == 'ONLINE_TEST'){
    //默认的数据库ip、port
    //主库配置
    defined('__KIS_DB_PORT_ALL_1__') || define('__KIS_DB_PORT_ALL_1__', '3306');
    defined('__KIS_DB_HOST_DEFAULT_1__') || define('__KIS_DB_HOST_DEFAULT_1__', '192.168.0.4');
    defined('__KIS_DB_USER_DEFAULT_1__') || define('__KIS_DB_USER_DEFAULT_1__', 'dev');
    defined('__KIS_DB_PASS_DEFAULT_1__') || define('__KIS_DB_PASS_DEFAULT_1__', 'Freedom_Lession!@#$');

    //从库配置
    defined('__KIS_DB_PORT_ENTOURAGE_1__') || define('__KIS_DB_PORT_ENTOURAGE_1__', '3306');
    defined('__KIS_DB_HOST_ENTOURAGE_1__') || define('__KIS_DB_HOST_ENTOURAGE_1__', '192.168.0.4');
    defined('__KIS_DB_USER_ENTOURAGE_1__') || define('__KIS_DB_USER_ENTOURAGE_1__', 'dev');
    defined('__KIS_DB_PASS_ENTOURAGE_1__') || define('__KIS_DB_PASS_ENTOURAGE_1__', 'Freedom_Lession!@#$');
} else if(KIS_ENV == 'ONLINE'){

    //默认的数据库ip、port
    //主库配置
    defined('__KIS_DB_PORT_ALL_1__') || define('__KIS_DB_PORT_ALL_1__', '3306');
    defined('__KIS_DB_HOST_DEFAULT_1__') || define('__KIS_DB_HOST_DEFAULT_1__', '192.168.0.4');
    defined('__KIS_DB_USER_DEFAULT_1__') || define('__KIS_DB_USER_DEFAULT_1__', 'dev');
    defined('__KIS_DB_PASS_DEFAULT_1__') || define('__KIS_DB_PASS_DEFAULT_1__', 'Freedom_Lession!@#$');

    //从库配置
    defined('__KIS_DB_PORT_ENTOURAGE_1__') || define('__KIS_DB_PORT_ENTOURAGE_1__', '3306');
    defined('__KIS_DB_HOST_ENTOURAGE_1__') || define('__KIS_DB_HOST_ENTOURAGE_1__', '192.168.0.4');
    defined('__KIS_DB_USER_ENTOURAGE_1__') || define('__KIS_DB_USER_ENTOURAGE_1__', 'dev');
    defined('__KIS_DB_PASS_ENTOURAGE_1__') || define('__KIS_DB_PASS_ENTOURAGE_1__', 'Freedom_Lession!@#$');
}