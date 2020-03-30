<?php
/**
 * 从hash/{$value}.cfg.php获取哈希信息
 * 并从哈希返回的信息里面的$host_group 字段
 * 再从host/{$host_group}.cfg.php获取数据库连接信息
 * 为什么分开两个配置文件？因为多个表可以共享一组连接库表配置信息
 */
return [

    'tbl_game_info' => [
        'host_group' => 'game',
        'database' => 'db_mapi',
        'table' => 'tbl_game_info',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'game-slave',
    ],
    'tbl_game_version' => [
        'host_group' => 'game',
        'database' => 'db_mapi',
        'table' => 'tbl_game_version',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'game-slave',
    ],
    't_game_role' => [
        'host_group' => 'game',
        'database' => 'db_sdk_role',
        'table' => 't_game_role',
        'db_hash_length' => 0,
        'tb_hash_length' => 2,
        'hash_column' => 'uid',
        'host_group_slave' => 'game-slave',
    ],
    't_login_game' => [
        'host_group' => 'game',
        'database' => 'db_sdk_login',
        'table' => 't_login_game',
        'db_hash_length' => 0,
        'tb_hash_length' => 2,
        'hash_column' => 'uid',
        'host_group_slave' => 'game-slave',
    ],
    't_enter_game' => [
        'host_group' => 'game',
        'database' => 'db_sdk_enter_game',
        'table' => 't_enter_game',
        'db_hash_length' => 0,
        'tb_hash_length' => 2,
        'hash_column' => 'uid',
        'host_group_slave' => 'game-slave',
    ],
    'tb_user' => [
        'host_group'     => 'game',
        'database'       => 'db_user',
        'table'          => 'tb_user',
        'db_hash_length' => 1,
        'tb_hash_length' => 2,
        'hash_column'    => 'uid',
        'host_group_slave' => 'game-slave',
    ],
    'tb_mobile' => [
        'host_group'     => 'game',
        'database'       => 'db_user',
        'table'          => 'tb_mobile',
        'db_hash_length' => 1,
        'tb_hash_length' => 2,
        'hash_column'    => 'mobile',
        'host_group_slave' => 'game-slave',
    ],
    'tb_username' => [
        'host_group'     => 'game',
        'database'       => 'db_user',
        'table'          => 'tb_username',
        'db_hash_length' => 1,
        'tb_hash_length' => 2,
        'hash_column'    => 'username',
        'host_group_slave' => 'game-slave',
    ],
    'tb_game_news' => [
        'host_group'     => 'game',
        'database'       => 'db_mapi',
        'table'          => 'tb_game_news',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
        'host_group_slave' => 'game-slave',
    ],
    'tb_game_paylogincfg' => [
        'host_group'     => 'game',
        'database'       => 'db_mapi',
        'table'          => 'tb_game_paylogincfg',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column'    => '',
        'host_group_slave' => 'game-slave',
    ],


];
