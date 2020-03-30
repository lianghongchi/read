<?php
/**
 * 从hash/{$value}.cfg.php获取哈希信息
 * 并从哈希返回的信息里面的$host_group 字段
 * 再从host/{$host_group}.cfg.php获取数据库连接信息
 * 为什么分开两个配置文件？因为多个表可以共享一组连接库表配置信息
 */
return [

    'tb_plat_channel' => [
        'host_group' => 'pay',
        'database' => 'db_pay',
        'table' => 'tb_plat_channel',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'pay-slave',
    ],
    'tb_plat_game_channel' => [
        'host_group' => 'pay',
        'database' => 'db_pay',
        'table' => 'tb_plat_game_channel',
        'db_hash_length' => 0,
        'tb_hash_length' => 0,
        'hash_column' => '',
        'host_group_slave' => 'pay-slave',
    ],
];
