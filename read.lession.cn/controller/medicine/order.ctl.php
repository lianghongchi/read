<?php
/**
 * 药品--药品模块
 *
 * @authName    药品库存管理--出货单
 * @note        药品库存管理--出货单
 * @package     KIS
 * @author      Freedom
 * @since       2019-09-11
 * 药品库存管理的模块
 */
class medicine_order_controller extends controller_lib {
    public function index_action() {
        $medicineName = input('medicineName', '');
        $factoryName = input('factoryName', '');
        $beginTime = input('beginTime', '');
        $endTime = input('endTime', '');
        //根据word查询
        $pageRow = 0;
        $pageRow || $pageRow = input('pagerows', 0);
        $pageRow < 1 && $pageRow = 30;
        $pageRow > 200 && $pageRow = 200;
        $where = [
            'medicineName' => $medicineName,
            'factoryName' => $factoryName,
            'beginTime' => $beginTime,
            'endTime' => $endTime,
        ];
        $count = lib_medicine_service_order::searchOrderCount($where);
        $page = $this->_getPageData($pageRow, $count);
        $list = lib_medicine_service_order::searchOrderList($where, $page['start'], $pageRow);
        $data = [
            'medicineName' => $medicineName,
            'factoryName' => $factoryName,
            'beginTime' => $beginTime,
            'endTime' => $endTime,
            'list' => $list,
        ];
        $this->_assign($data);
        $this->_render();
    }

    public function add_action() {
        if(is_post()) {
            $res = $this->addAdd();
            if($res['code'] != 200) {
                return $this->_error($res['msg']);
            }
            return $this->_success($res['msg']);
        }
        $invent = lib_medicine_service_invent::searchInventList('', '', 0, 0);
        $data = [
            'invent' => $invent,
        ];
        $this->_assign($data);
        $this->_render();
    }

    public function addAdd() {
        $inventId = input('inventId', 0, 'intval');
        $count = input('count', 0, 'intval');
        $price = input('price', '', 'trim');
        $orderTime = strtotime(input('orderTime', '', 'trim'));
        if(empty($inventId) ||  $count < 1 || empty($orderTime)) {
            return ['code' => 500, 'msg' => '参数错误'];
        }
        //库存信息
        $inventInfo = lib_medicine_service_invent::getInventInfoById($inventId);
        if(empty($inventInfo['inventId']) || empty($inventInfo['goodsId']) || empty($inventInfo['factoryId'])) {
            return ['code' => 500, 'msg' => '药品不存在'];
        }
        if($inventInfo['inventCount'] < $count) {
            return ['code' => 500, 'msg' => '没有那么多库存了'];
        }
        $params = [
            'goodsId' => $inventInfo['goodsId'],
            'factoryId' => $inventInfo['factoryId'],
            'inventId' => $inventId,
            'count' => $count,
            'price' => $price,
            'orderTime' => $orderTime,
        ];
        $res = lib_medicine_service_order::addOrderInfo($params);
        if(is_bool($res) && !$res) {
            return ['code' => 500, 'msg' => '添加失败'];
        }
        return ['code' => 200, 'msg' => '添加成功'];
    }
}