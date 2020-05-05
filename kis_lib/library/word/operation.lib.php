<?php
class lib_word_operation {
    private static $redisGroup = 'word';
    private static $redisPre = 'word_';
    private static $redisTimeout = 86400;

    public static function operation() {
        $r = new r(self::$redisGroup);
        var_dump($r);
        echo 1;
        exit();
    }
}
