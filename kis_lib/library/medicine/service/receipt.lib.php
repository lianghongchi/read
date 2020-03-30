<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_receipt {
    /**
     * @param array $where
     * @return int
     * 查询数量
     */
    public static function searchReceiptCount(Array $where) {
        $whereStr = '';
        $operationWhere = lib_medicine_service_common::operationWhere($where);
        if((!empty($where['medicineName']) && empty($operationWhere['medicine'])) || (!empty($where['factoryName']) && empty($operationWhere['factory']))) {
            return 0;
        }
        if(!empty($operationWhere['medicine'])) {
            $str = implode(',', $operationWhere['medicine']);
            $whereStr .= " goodsId in (". $str.") AND";
        }
        if(!empty($operationWhere['factory'])) {
            $str = implode(',', $operationWhere['factory']);
            $whereStr .= " factoryId in (". $str.") AND";
        }
        if(!empty($where['beginTime'])) {
            $whereStr .= " receiptTime >= ". strtotime($where['beginTime']). " AND";
        }
        if(!empty($where['endTime'])) {
            $whereStr .= " receiptTime < ". strtotime($where['endTime']). " AND";
        }
        $count = lib_medicine_dao_receipt::searchReceiptCountByWhere($whereStr);
        return $count;
    }

    /**
     * @param array $where
     * @param int $offset
     * @param int $limit
     * @return array|int
     * 获取列表
     */
    public static function searchReceiptList(Array $where, int $offset = 0, int $limit = 0) {
        $whereStr = '';
        $operationWhere = lib_medicine_service_common::operationWhere($where);
        if((!empty($where['medicineName']) && empty($operationWhere['medicine'])) || (!empty($where['factoryName']) && empty($operationWhere['factory']))) {
            return 0;
        }
        if(!empty($operationWhere['medicine'])) {
            $str = implode(',', $operationWhere['medicine']);
            $whereStr .= " goodsId in (". $str.") AND";
        }
        if(!empty($operationWhere['factory'])) {
            $str = implode(',', $operationWhere['factory']);
            $whereStr .= " factoryId in (". $str.") AND";
        }
        if(!empty($where['beginTime'])) {
            $whereStr .= " receiptTime >= ". strtotime($where['beginTime']). " AND";
        }
        if(!empty($where['endTime'])) {
            $whereStr .= " receiptTime < ". strtotime($where['endTime']). " AND";
        }
        $list = lib_medicine_dao_receipt::searchReceiptListByWhere($whereStr, $offset, $limit);
        $data = [];
        if(!empty($list)) {
            foreach ($list as $item) {
                $medicine = lib_medicine_service_goods::getGoodsInfoById($item['goodsId']);
                $factory = lib_medicine_service_factory::getFactoryInfoById($item['factoryId']);
                $data[] = [
                    'receiptId' => $item['receiptId'],
                    'goodsId' => $item['goodsId'],
                    'factoryId' => $item['factoryId'],
                    'inventId' => $item['inventId'],
                    'receiptCount' => $item['receiptCount'],
                    'receiptPrice' => $item['receiptPrice'],
                    'receiptTotal' => $item['receiptTotal'],
                    'receiptTime' => $item['receiptTime'],
                    'medicineName' => $medicine['medicineName'],
                    'factoryName' => $factory['factoryName'],
                ];
            }
        }
        return $data;
    }

    public static function addReceiptInfo(Array $params) {
        if(empty($params['goodsId']) || empty($params['factoryId'])
            || empty($params['inventId']) || empty($params['receiptCount'])
            || $params['receiptCount'] < 1 || empty($params['receiptTime'])) {
            return false;
        }
        $params['operationTime'] = time();
        if(empty($params['receiptPrice'])) {
            $params['receiptPrice'] = 0;
        }
        $params['receiptTotal'] = $params['receiptCount'] * $params['receiptPrice'];
        $res = lib_medicine_dao_receipt::addReceiptInfo($params);
        //修改库存数量
        if($res) {
            $inventInfo = lib_medicine_dao_invent::getInventInfoById($params['inventId'], ['inventId', 'inventCount']);
            $count = $inventInfo['inventCount'] + $params['receiptCount'];
            $res = lib_medicine_dao_invent::updateInventInfo($params['inventId'], $count);
        }
        return $res;
    }
}