<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:11
 */
DB::initConfig('medicine');
class lib_medicine_dao_factory {
    private static $table = 'tb_factory';
    private static $redisGroup = 'medicine';
    private static $redisPre = 'factory_';
    private static $redisTimeout = 86400;

    /**
     * @param array $params
     * @return bool
     * 添加工厂
     */
    public static function createFactory(Array $params) {
        if(empty($params['factoryName'])) {
            return false;
        }
        $sql = "INSERT INTO ". self::$table. " (factoryName) VALUES (:factoryName)";
        $res = DB::insert($sql, ['factoryName'=>$params['factoryName']]);
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
     * 获取厂家数量
     */
    public static function searchFactoryCountByWhere(String $where) {
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
     * 获取厂家列表
     */
    public static function searchFactoryListByWhere(String $where, int $offset = 0, int $limit = 0) {
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
     * @param int $factoryId
     * @param array $fields
     * @return bool
     * 通过主键获取详情
     */
    public static function getFactoryInfoById(int $factoryId, Array $fields) {
        if(empty($factoryId)) {
            return false;
        }
        $fields[] = 'factoryId';
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $factoryId;
        $info = $r->hMget($key, $fields);
        if(empty($info['factoryId'])) {
            $sql = "SELECT * FROM ". self::$table. " WHERE factoryId = :factoryId";
            $data = DB::getOne($sql, ['factoryId' => $factoryId]);
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
     * @param int $factoryId
     * @param String $factoryName
     * @return bool
     * 修改工厂信息
     */
    public static function updateFactoryInfo(int $factoryId, String $factoryName) {
        if(empty($factoryId) || empty($factoryName)) {
            return false;
        }
        $sql = "UPDATE ". self::$table. " SET factoryName = :factoryName WHERE factoryId = :factoryId";
        $res = DB::update($sql, ['factoryId' => $factoryId, 'factoryName' => $factoryName]);
        if(empty($res) && is_bool($res)) {
            return false;
        }
        $r = new r(self::$redisGroup);
        $key = self::$redisPre. 'info_'. $factoryId;
        $r->del($key);
        return true;
    }
}