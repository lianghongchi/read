<?php
/**
 * 从hash/{$value}.cfg.php获取哈希信息
 * 并从哈希返回的信息里面的$host_group 字段
 * 再从host/{$host_group}.cfg.php获取数据库连接信息
 * 为什么分开两个配置文件？因为多个表可以共享一组连接库表配置信息
 */
return [

    't_admin' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],

    't_admin_log_login' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_log_login',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],

    't_admin_auth' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_auth',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],

    't_admin_role' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_role',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],

    't_admin_role_auth' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_role_auth',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],

    't_admin_rolemap' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_rolemap',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],
    't_admin_log_action' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_admin_log_action',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],
    't_menu' => [
        'host_group'     => 'admin',
        'database'       => 'le_sdk_admin',
        'table'          => 't_menu',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
    ],



];
