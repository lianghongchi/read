<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/9/10
 * Time: 16:35
 * 数据库操作类
 */
DB::initConfig('word');
class lib_english_dao_word {
    private static $table = 'tb_word';
    private static $redisGroup = 'word';
    private static $redisTimeout = 86400;

    /**
     * @param String $word
     * @param String $short
     * @param String $long
     * @param String $longer
     * @return bool
     * 添加单词表
     */
    public static function addWord(String $word, String $short, String $long, String $longer) {
        if(empty($word)) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (word,explain_short,explain_long,explain_longer) VALUES (:word,:explain_short,:explain_long,:explain_longer)";
        $res = DB::insert($sql, ['word'=>$word,'explain_short'=>$short,'explain_long'=>$long,'explain_longer'=>$longer]);
        return $res;
    }

    /**
     * @param String $where
     * @param int $offset
     * @param int $limit
     * @return array
     * 获取单词列表
     */
    public static function searchWordListByWhere(String $where, int $offset = 0, int $limit = 0) {
        $sql = "SELECT * FROM ". self::$table;
        if(!empty($where)) {
            $sql .= " WHERE ". trim($where, "AND");
        }
        if(!empty($limit)) {
            $sql .= " LIMIT ".$offset. ",". $limit;
        }
        $list = DB::getAll($sql);
        return $list;
    }

    /**
     * @param String $where
     * @return mixed
     * 查询单词总数
     */
    public static function searchWordCountByWhere(String $where) {
        $sql = "SELECT count(1) AS num FROM ". self::$table;
        if(!empty($where)) {
            $sql .= " WHERE ". trim($where, "AND");
        }
        $count = DB::affectedRows($sql);
        return $count;
    }
}