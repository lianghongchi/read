<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_goods {
    /**
     * @param String $param
     * @return mixed
     * 获取厂家数量
     */
    public static function searchGoodsCount(String $param) {
        $where = '';
        if(!empty($param)) {
            $where .= "medicineName like '%". $param."%' AND";
        }
        $count = lib_medicine_dao_goods::searchGoodsCountByWhere($where);
        return $count;
    }

    /**
     * @param int $medicineId
     * @param String $medicineName
     * @return bool|mixed
     * 通过主键和名称查询
     */
    public static function getGoodsCountByIdName(int $medicineId, String $medicineName) {
        if(empty($medicineName) || empty($medicineId)) {
            return false;
        }
        $where = " medicineName = '{$medicineName}' AND medicineId != ". $medicineId;
        $count = lib_medicine_dao_goods::searchGoodsCountByWhere($where);
        return $count;
    }

    /**
     * @param String $word
     * @param int $offset
     * @param int $limit
     * @return array
     * 获取厂家列表数据
     */
    public static function searchGoodsList(String $word, int $offset = 0, int $limit = 0) {
        $where = '';
        if(!empty($word)) {
            $where .= "medicineName like '%". $word."%' AND";
        }
        $list = lib_medicine_dao_goods::searchGoodsListByWhere($where, $offset, $limit);
        return $list;
    }

    /**
     * @param String $medicineName
     * @return bool
     * 添加工厂名称
     */
    public static function addGoods(String $medicineName) {
        if(empty($medicineName)) {
            return false;
        }
        $res = lib_medicine_dao_goods::createGoods(['medicineName' => $medicineName]);
        return $res;
    }

    /**
     * @param int $medicineId
     * @return bool
     * 获取工厂的详情
     */
    public static function getGoodsInfoById(int $medicineId) {
        if(empty($medicineId)) {
            return false;
        }
        $info = lib_medicine_dao_goods::getGoodsInfoById($medicineId, ['medicineId', 'medicineName']);
        return $info;
    }

    /**
     * @param int $medicineId
     * @param String $medicineName
     * @return bool
     * 修改
     */
    public static function updateGoods(int $medicineId, String $medicineName) {
        if(empty($medicineId) || empty($medicineName)) {
            return false;
        }
        $res = lib_medicine_dao_goods::updateGoodsInfo($medicineId, $medicineName);
        return $res;
    }
}