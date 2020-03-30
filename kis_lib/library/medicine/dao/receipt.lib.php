<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:11
 */
DB::initConfig('medicine');
class lib_medicine_dao_receipt {
    private static $table = 'tb_receipt';
    private static $redisGroup = 'medicine';
    private static $redisPre = 'receipt_';
    private static $redisTimeout = 86400;

    public static function searchReceiptCountByWhere(String $where) {
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'count';
        //没有办法处理带搜索条件的redis，暂时不处理
        if(empty($where)) {
            $count = $r->get($key);
            if(!empty($count)) {
                return $count;
            }
        }
        $sql = "SELECT count(1) AS num FROM ". self::$table;
        if(!empty($where)) {
            $sql .= " WHERE ". trim($where, "AND");
        }
        $res = DB::getOne($sql);
        $count = $res['num'];
        if(empty($where)) {
            $r->SETEX($key, self::$redisTimeout, $count);
        }
        return $count;
    }

    /**
     * @param String $where
     * @param int $offset
     * @param int $limit
     * @return array
     * 获取列表
     */
    public static function searchReceiptListByWhere(String $where, int $offset = 0, int $limit = 0) {
        $sql = "SELECT * FROM ". self::$table;
        if(!empty($where)) {
            $sql .= " WHERE ". trim($where, "AND");
        }
        $sql .= " ORDER BY receiptTime DESC ";
        if(!empty($limit)) {
            $sql .= " LIMIT ".$offset. ",". $limit;
        }
        $list = DB::getAll($sql);
        return $list;
    }

    public static function addReceiptInfo(Array $params) {
        if(empty($params['goodsId']) || empty($params['factoryId'])
            || empty($params['inventId']) || empty($params['receiptCount'])
            || $params['receiptCount'] < 1 || empty($params['receiptTime'])) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (goodsId,factoryId,inventId,receiptCount,receiptPrice,receiptTotal,receiptTime,operationTime) VALUES (:goodsId,:factoryId,:inventId,:receiptCount,:receiptPrice,:receiptTotal,:receiptTime,:operationTime)";
        $res = DB::insert($sql, $params);
        if($res) {
            $r = new r(self::$redisGroup);
            $key = self::$redisPre. 'count';
            $r->del($key);
        }
        return $res;
    }
}