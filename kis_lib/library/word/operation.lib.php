<?php
class lib_word_operation {
    private static $redisGroup = 'word';
    private static $redisPre = 'word_';
    private static $redisTimeout = 86400;

    private static $file = ['/opt/newweb/read/kis_lib/library/word/a.txt'];

    public static function operation() {
        $r = new r(self::$redisGroup);
        $sortSetKey = self::$redisPre. 'sort_set_list';
        foreach (self::$file as $item) {
            $arr = file($item);
            foreach ($arr as $value) {
                $strArr = explode(' ', $value);
                foreach ($strArr as $str) {
                    $temp = trim($str, ' ');
                    $temp = trim($temp, '\\n');
                    $info = $r->ZRANK($sortSetKey, $temp);
                    if(empty($info)) {
                        $r->ZADD($sortSetKey, 1, $temp);
                    } else {
                        $r->ZINCRBY($sortSetKey, 1, $temp);
                    }
                }
            }
        }
        exit();
    }
}
