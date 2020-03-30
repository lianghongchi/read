<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_invent {
    private static $remind = 5;
    public static function searInventCount(String $medicineName, String $factoryName, int $medicineId, int $factoryId) {
        $where = '';
        $operationWhere = lib_medicine_service_common::operationWhere(['medicineName' => $medicineName, 'factoryName' => $factoryName]);
        if((!empty($medicineName) && empty($operationWhere['medicine'])) || (!empty($factoryName) && empty($operationWhere['factory']))) {
            return 0;
        }
        if(empty($operationWhere['medicine']))
            if(!empty($operationWhere['medicine'])) {
                $str = implode(',', $operationWhere['medicine']);
                $where .= " goodsId in (". $str.") AND";
            }
        if(!empty($operationWhere['factory'])) {
            $str = implode(',', $operationWhere['factory']);
            $where .= " factoryId in (". $str.") AND";
        }
        if(!empty($medicineId)) {
            $where .= " goodsId = ". $medicineId. " AND";
        }
        if(!empty($factoryId)) {
            $where .= " factoryId = ". $factoryId. " AND";
        }
        $count = lib_medicine_dao_invent::searchInventCountByWhere($where);
        return $count;
    }

    public static function searchInventList(String $medicineName, String $factoryName, int $medicineId, int $factoryId, int $offset = 0, int $limit = 0) {
        $where = '';
        $operationWhere = lib_medicine_service_common::operationWhere(['medicineName' => $medicineName, 'factoryName' => $factoryName]);
        if((!empty($medicineName) && empty($operationWhere['medicine'])) || (!empty($factoryName) && empty($operationWhere['factory']))) {
            return 0;
        }
        if(!empty($operationWhere['medicine'])) {
            $str = implode(',', $operationWhere['medicine']);
            $where .= " goodsId in (". $str.") AND";
        }
        if(!empty($operationWhere['factory'])) {
            $str = implode(',', $operationWhere['factory']);
            $where .= " factoryId in (". $str.") AND";
        }
        if(!empty($medicineId)) {
            $where .= " goodsId = ". $medicineId. " AND";
        }
        if(!empty($factoryId)) {
            $where .= " factoryId = ". $factoryId. " AND";
        }
        $list = lib_medicine_dao_invent::searchInventListByWhere($where, $offset, $limit);
        $data = [];
        if(!empty($list)) {
            foreach ($list as $item) {
                $medicine = lib_medicine_service_goods::getGoodsInfoById($item['goodsId']);
                $factory = lib_medicine_service_factory::getFactoryInfoById($item['factoryId']);
                $isRemind = $item['inventCount'] <= self::$remind? true: false;
                $data[] = [
                    'inventId' => $item['inventId'],
                    'goodsId' => $item['goodsId'],
                    'factoryId' => $item['factoryId'],
                    'inventCount' => $item['inventCount'],
                    'medicineName' => $medicine['medicineName'],
                    'factoryName' => $factory['factoryName'],
                    'together' => $medicine['medicineName']. ' -- '. $factory['factoryName'],
                    'isRemind' => $isRemind,
                ];
            }
        }
        return $data;
    }

    public static function addInvent(int $medicineId, int $factoryId) {
        if(empty($medicineId) || empty($factoryId)) {
            return false;
        }
        $res = lib_medicine_dao_invent::createInvent(['medicineId' => $medicineId, 'factoryId' => $factoryId]);
        return $res;
    }

    public static function getInventInfoById(int $inventId) {
        if(empty($inventId)) {
            return false;
        }
        $info = lib_medicine_dao_invent::getInventInfoById($inventId, ['inventId','goodsId','factoryId','inventCount']);
        return $info;
    }
}