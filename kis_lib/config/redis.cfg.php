<?php
/**
 * redis配置
 *
 * @package        KIS
 * @author        九曜宫宿
 * @since        2018-11-21
 * @example
 *
 */


if (KIS_ENV == 'DEV') {
    $config = [
        'default' => [['host' => '127.0.0.1', 'port' => '6379'],],
        'user' => [['host' => '127.0.0.1', 'port' => '6379'],],
        'medicine' => [['host' => '127.0.0.1', 'port' => '6379'],],
    ];
} else if (KIS_ENV == 'ONLINE_TEST') {
    $config = [
        'default' => [['host' => '172.16.255.17', 'port' => '6379'],],
        'user' => [['host' => '172.16.255.17', 'port' => '6379'],],
    ];
} else if (KIS_ENV == 'ONLINE') {
    $config = [
        'default' => [['host' => '172.16.255.10', 'port' => '6379'],],
        'user' => [['host' => '172.16.255.10', 'port' => '6379'],],
    ];
}
return $config;