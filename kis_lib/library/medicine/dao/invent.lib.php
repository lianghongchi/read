<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:11
 */
DB::initConfig('medicine');
class lib_medicine_dao_invent {
    private static $table = 'tb_invent';
    private static $redisGroup = 'medicine';
    private static $redisPre = 'invent_';
    private static $redisTimeout = 86400;

    /**
     * @param array $params
     * @return bool
     * 添加药品
     */
    public static function createInvent(Array $params) {
        if(empty($params['medicineId']) || empty($params['factoryId'])) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (goodsId,factoryId,inventCount) VALUES (:goodsId,:factoryId,:inventCount)";
        $res = DB::insert($sql, ['goodsId'=>$params['medicineId'],'factoryId'=>$params['factoryId'],'inventCount'=>0]);
        if($res) {
            $r = new r(self::$redisGroup);
            $key = self::$redisPre. 'count';
            $r->del($key);
        }
        return $res;
    }

    /**
     * @param String $where
     * @return mixed
     * 获取药品数量
     */
    public static function searchInventCountByWhere(String $where) {
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
     * 获取药品列表
     */
    public static function searchInventListByWhere(String $where, int $offset = 0, int $limit = 0) {
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
     * @param int $inventId
     * @param array $fields
     * @return bool
     * 通过主键获取详情
     */
    public static function getInventInfoById(int $inventId, Array $fields) {
        if(empty($inventId)) {
            return false;
        }
        $fields[] = 'inventId';
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $inventId;
        $info = $r->hMget($key, $fields);
        if(empty($info['inventId'])) {
            $sql = "SELECT * FROM ". self::$table. " WHERE inventId = :inventId";
            $data = DB::getOne($sql, ['inventId' => $inventId]);
            if(!empty($data)) {
                $r->hMset($key, $data);
                $r->expire($key, self::$redisTimeout);
                foreach ($fields as $item) {
                    $info[$item] = $data[$item];
                }
                $data = [];
                unset($data);
            }
        }
        return $info;
    }

    /**
     * @param int $inventId
     * @param int $inventCount
     * @return bool
     * 修改库存数量信息
     */
    public static function updateInventInfo(int $inventId, int $inventCount) {
        if(empty($inventId)) {
            return false;
        }
        $sql = "UPDATE ". self::$table. " SET inventCount = :inventCount WHERE inventId = :inventId";
        $res = DB::update($sql, ['inventId' => $inventId, 'inventCount' => $inventCount]);
        if(empty($res) && is_bool($res)) {
            return false;
        }
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $inventId;
        $r->del($key);
        return true;
    }
}