<?php

defined('DOMAIN') || define('DOMAIN', 'dianjiemian.com');


//静态资源域名
defined('__CORE_DOMAIN_DEFINE_STATIC__') || define('__CORE_DOMAIN_DEFINE_STATIC__', 'cdn.dianjiemian.com'); 
defined('__CORE_DOMAIN_DEFINE_STATIC_2__') || define('__CORE_DOMAIN_DEFINE_STATIC_2__', __CORE_DOMAIN_DEFINE_STATIC__);
defined('__CORE_DOMAIN_DEFINE_STATIC_3__') || define('__CORE_DOMAIN_DEFINE_STATIC_3__', __CORE_DOMAIN_DEFINE_STATIC__);
defined('__CORE_DOMAIN_DEFINE_STATIC_WT__') || define('__CORE_DOMAIN_DEFINE_STATIC_WT__', __CORE_DOMAIN_DEFINE_STATIC__); 


Config::get('ip'); //IP宏定义