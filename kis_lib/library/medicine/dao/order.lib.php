<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:11
 */
DB::initConfig('medicine');
class lib_medicine_dao_order {
    private static $table = 'tb_order';
    private static $redisGroup = 'medicine';
    private static $redisPre = 'order_';
    private static $redisTimeout = 86400;

    public static function searchOrderCountByWhere(String $where) {
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
    public static function searchOrderListByWhere(String $where, int $offset = 0, int $limit = 0) {
        $sql = "SELECT * FROM ". self::$table;
        if(!empty($where)) {
            $sql .= " WHERE ". trim($where, "AND");
        }
        $sql .= " ORDER BY orderTime DESC ";
        if(!empty($limit)) {
            $sql .= " LIMIT ".$offset. ",". $limit;
        }
        $list = DB::getAll($sql);
        return $list;
    }

    public static function addOrderInfo(Array $params) {
        if(empty($params['goodsId']) || empty($params['factoryId'])
            || empty($params['inventId']) || empty($params['count'])
            || $params['count'] < 1 || empty($params['orderTime']) || empty($params['orderNo'])) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (orderNo,goodsId,factoryId,inventId,`count`,price,totalPrice,orderTime,operationTime) VALUES (:orderNo,:goodsId,:factoryId,:inventId,:count,:price,:totalPrice,:orderTime,:operationTime)";
        $res = DB::insert($sql, $params);
        if($res) {
            $r = new r(self::$redisGroup);
            $key = self::$redisPre. 'count';
            $r->del($key);
        }
        return $res;
    }
}