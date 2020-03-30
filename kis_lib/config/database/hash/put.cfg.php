<?php
/**
 * 从hash/{$value}.cfg.php获取哈希信息
 * 并从哈希返回的信息里面的$host_group 字段
 * 再从host/{$host_group}.cfg.php获取数据库连接信息
 * 为什么分开两个配置文件？因为多个表可以共享一组连接库表配置信息
 */
return [

    'tb_game_putmerc' => [
        'host_group' => 'put',
        'database' => 'db_mapi',
        'table' => 'tb_game_putmerc',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'put-slave',
    ],
    'tb_game_putchid' => [
        'host_group' => 'put',
        'database' => 'db_mapi',
        'table' => 'tb_game_putchid',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'put-slave',
    ],
    'tb_game_putchannel' => [
        'host_group' => 'put',
        'database' => 'db_mapi',
        'table' => 'tb_game_putchannel',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'put-slave',
    ],
    'tb_game_corechid' => [
        'host_group' => 'put',
        'database' => 'db_mapi',
        'table' => 'tb_game_corechid',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'put-slave',
    ],
    'tb_game_corechannel' => [
        'host_group' => 'put',
        'database' => 'db_mapi',
        'table' => 'tb_game_corechannel',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'put-slave',
    ],

];
