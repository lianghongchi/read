<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:11
 */
DB::initConfig('medicine');
class lib_medicine_dao_goods {
    private static $table = 'tb_goods';
    private static $redisGroup = 'medicine';
    private static $redisPre = 'goods_';
    private static $redisTimeout = 86400;

    /**
     * @param array $params
     * @return bool
     * 添加药品
     */
    public static function createGoods(Array $params) {
        if(empty($params['medicineName'])) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (medicineName) VALUES (:medicineName)";
        $res = DB::insert($sql, ['medicineName'=>$params['medicineName']]);
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
    public static function searchGoodsCountByWhere(String $where) {
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
    public static function searchGoodsListByWhere(String $where, int $offset = 0, int $limit = 0) {
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
     * @param int $medicineId
     * @param array $fields
     * @return bool
     * 通过主键获取详情
     */
    public static function getGoodsInfoById(int $medicineId, Array $fields) {
        if(empty($medicineId)) {
            return false;
        }
        $fields[] = 'medicineId';
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $medicineId;
        $info = $r->hMget($key, $fields);
        if(empty($info['medicineId'])) {
            $sql = "SELECT * FROM ". self::$table. " WHERE medicineId = :medicineId";
            $data = DB::getOne($sql, ['medicineId' => $medicineId]);
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
     * @param int $medicineId
     * @param String $medicineName
     * @return bool
     * 修改药品信息
     */
    public static function updateGoodsInfo(int $medicineId, String $medicineName) {
        if(empty($medicineId) || empty($medicineName)) {
            return false;
        }
        $sql = "UPDATE ". self::$table. " SET medicineName = :medicineName WHERE medicineId = :medicineId";
        $res = DB::update($sql, ['medicineId' => $medicineId, 'medicineName' => $medicineName]);
        if(empty($res) && is_bool($res)) {
            return false;
        }
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $medicineId;
        $r->del($key);
        return true;
    }
}