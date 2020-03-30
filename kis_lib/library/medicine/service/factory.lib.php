<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_factory {
    /**
     * @param String $param
     * @return mixed
     * 获取厂家数量
     */
    public static function searchFactoryCount(String $param) {
        $where = '';
        if(!empty($param)) {
            $where .= "factoryName like '%". $param."%' AND";
        }
        $count = lib_medicine_dao_factory::searchFactoryCountByWhere($where);
        return $count;
    }

    /**
     * @param int $factoryId
     * @param String $factoryName
     * @return bool
     * 通过主键和名称查询数量
     */
    public static function getFactoryCountByIdName(int $factoryId, String $factoryName) {
        if(empty($factoryName) || empty($factoryId)) {
            return false;
        }
        $where = " factoryName = '{$factoryName}' AND factoryId != ". $factoryId. " AND";
        $count = lib_medicine_dao_factory::searchFactoryCountByWhere($where);
        return $count;
    }

    /**
     * @param String $word
     * @param int $offset
     * @param int $limit
     * @return array
     * 获取厂家列表数据
     */
    public static function searchFactoryList(String $word, int $offset = 0, int $limit = 0) {
        $where = '';
        if(!empty($word)) {
            $where .= "factoryName like '%". $word."%' AND";
        }
        $list = lib_medicine_dao_factory::searchFactoryListByWhere($where, $offset, $limit);
        return $list;
    }

    /**
     * @param String $factoryName
     * @return bool
     * 添加工厂名称
     */
    public static function addFactory(String $factoryName) {
        if(empty($factoryName)) {
            return false;
        }
        $res = lib_medicine_dao_factory::createFactory(['factoryName' => $factoryName]);
        return $res;
    }

    /**
     * @param int $factoryId
     * @return bool
     * 获取工厂的详情
     */
    public static function getFactoryInfoById(int $factoryId) {
        if(empty($factoryId)) {
            return false;
        }
        $info = lib_medicine_dao_factory::getFactoryInfoById($factoryId, ['factoryId', 'factoryName']);
        return $info;
    }

    /**
     * @param int $factoryId
     * @param String $factoryName
     * @return bool
     * 修改
     */
    public static function updateFactory(int $factoryId, String $factoryName) {
        if(empty($factoryName) || empty($factoryId)) {
            return false;
        }
        $res = lib_medicine_dao_factory::updateFactoryInfo($factoryId, $factoryName);
        return $res;
    }
}