<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/12/12
 * Time: 21:13
 */
class lib_medicine_service_order {
    /**
     * @param array $where
     * @return int
     * 查询数量
     */
    public static function searchOrderCount(Array $where) {
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
            $whereStr .= " orderTime >= ". strtotime($where['beginTime']). " AND";
        }
        if(!empty($where['endTime'])) {
            $whereStr .= " orderTime < ". strtotime($where['endTime']). " AND";
        }
        $count = lib_medicine_dao_order::searchOrderCountByWhere($whereStr);
        return $count;
    }

    /**
     * @param array $where
     * @param int $offset
     * @param int $limit
     * @return array|int
     * 获取列表
     */
    public static function searchOrderList(Array $where, int $offset = 0, int $limit = 0) {
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
            $whereStr .= " orderTime >= ". strtotime($where['beginTime']). " AND";
        }
        if(!empty($where['endTime'])) {
            $whereStr .= " orderTime < ". strtotime($where['endTime']). " AND";
        }
        $list = lib_medicine_dao_order::searchOrderListByWhere($whereStr, $offset, $limit);
        $data = [];
        if(!empty($list)) {
            foreach ($list as $item) {
                $medicine = lib_medicine_service_goods::getGoodsInfoById($item['goodsId']);
                $factory = lib_medicine_service_factory::getFactoryInfoById($item['factoryId']);
                $data[] = [
                    'orderNo' => $item['orderNo'],
                    'goodsId' => $item['goodsId'],
                    'factoryId' => $item['factoryId'],
                    'inventId' => $item['inventId'],
                    'count' => $item['count'],
                    'price' => $item['price'],
                    'totalPrice' => $item['totalPrice'],
                    'orderTime' => $item['orderTime'],
                    'medicineName' => $medicine['medicineName'],
                    'factoryName' => $factory['factoryName'],
                ];
            }
        }
        return $data;
    }

    public static function addOrderInfo(Array $params) {
        if(empty($params['goodsId']) || empty($params['factoryId'])
            || empty($params['inventId']) || empty($params['count'])
            || $params['count'] < 1 || empty($params['orderTime'])) {
            return false;
        }
        $params['orderNo'] = self::getOrderNo($params['inventId'], $params['count']);
        $params['operationTime'] = time();
        if(empty($params['price'])) {
            $params['price'] = 0;
        }
        $params['totalPrice'] = $params['count'] * $params['price'];
        $res = lib_medicine_dao_order::addOrderInfo($params);
        //修改库存数量
        if($res) {
            $inventInfo = lib_medicine_dao_invent::getInventInfoById($params['inventId'], ['inventId', 'inventCount']);
            $count = $inventInfo['inventCount'] - $params['count'];
            $res = lib_medicine_dao_invent::updateInventInfo($params['inventId'], $count);
        }
        return $res;
    }

    public static function getOrderNo(int $inventId, int $count) {
        if(empty($count) || empty($inventId)) {
            return false;
        }
        $time = intval(microtime(true) * 1000);
        $random = rand(100000, 999999);
        $str = md5($time. $inventId. $random. $count);
        return $str;
    }
}