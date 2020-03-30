<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_common {
    public static function operationWhere(Array $where) {
        $medicine = [];
        if(!empty($where['medicineName'])) {
            $temp = "medicineName like '%". $where['medicineName']."%'";
            $list = lib_medicine_dao_goods::searchGoodsListByWhere($temp);
            if(!empty($list)) {
                foreach ($list as $item) {
                    $medicine[] = $item['medicineId'];
                }
            }
        }
        $factory = [];
        if(!empty($where['factoryName'])) {
            $temp = "factoryName like '%". $where['factoryName']."%'";
            $list = lib_medicine_dao_factory::searchFactoryListByWhere($temp);
            if(!empty($list)) {
                foreach ($list as $item) {
                    $factory[] = $item['factoryId'];
                }
            }
        }
        return ['medicine' => $medicine, 'factory' => $factory];
    }
}