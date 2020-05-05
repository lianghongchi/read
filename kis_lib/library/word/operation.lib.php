<?php
class lib_word_operation {
    private static $redisGroup = 'word';
    private static $redisPre = 'word_';
    private static $redisTimeout = 86400;

    private static $file = ['a.txt'];

    public static function operation() {
        $r = new r(self::$redisGroup);
        foreach (self::$file as $item) {
            $arr = file($item);
            print_r($arr);
        }
        exit();
    }
}
