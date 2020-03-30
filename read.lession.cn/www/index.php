<?php

// 应用根目录

define('KIS_APPLICATION_ROOT', realpath(__DIR__ .'/../') . '/');

/**
 * session 设置
 * @var
 */
// session 名称
ini_set('session.name', 'admin_sessid');

ini_set('session.gc_probability', 1);
ini_set('session.gc_divisor', 1);

// session 2小时失效
ini_set('session.gc_maxlifetime', 7200);

// linux session 保存目录
if (DIRECTORY_SEPARATOR != '\\') {
    if (!is_dir('/tmp/kisphpsession')) {
        if (!mkdir('/tmp/kisphpsession')) {
            die('Session Dir Create Fail');
        }
    }
    session_save_path("/tmp/kisphpsession");
}
if (!ini_get("session.auto_start") && !defined('DONT_SESSION_START')) {
	session_start() || die('Session Start Fail');
}
require KIS_APPLICATION_ROOT . '/../kis_lib/boot.php';
