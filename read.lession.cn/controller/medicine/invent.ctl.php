<?php
/**
 * 药品--药品模块
 *
 * @authName    药品库存管理--库存
 * @note        药品库存管理--库存
 * @package     KIS
 * @author      Freedom
 * @since       2019-09-11
 * 药品库存管理的模块
 */
class medicine_invent_controller extends controller_lib {
    public function index_action() {
        $medicineName = input('medicineName', '');
        $factoryName = input('factoryName', '');
        //根据word查询
        $pageRow = 0;
        $pageRow || $pageRow = input('pagerows', 0);
        $pageRow < 1 && $pageRow = 30;
        $pageRow > 200 && $pageRow = 200;
        $count = lib_medicine_service_invent::searInventCount($medicineName, $factoryName, 0, 0);
        $page = $this->_getPageData($pageRow, $count);
        $list = lib_medicine_service_invent::searchInventList($medicineName, $factoryName, 0, 0, $page['start'], $pageRow);
        $data = [
            'medicineName' => $medicineName,
            'factoryName' => $factoryName,
            'list' => $list,
        ];
        $this->_assign($data);
        $this->_render();
    }

    private function doAdd() {
        $medicineId = input('medicineId', 0, 'intval');
        $factoryId = input('factoryId', 0, 'intval');
        if(empty($medicineId) || empty($factoryId)) {
            return ['code' => 500, 'msg' => '参数错误'];
        }
        //查询是否存在
        $count = lib_medicine_service_invent::searInventCount('', '', $medicineId, $factoryId);
        if($count > 0) {
            return ['code' => 500, 'msg' => '已经存在'];
        }
        $res = lib_medicine_service_invent::addInvent($medicineId, $factoryId);
        if(empty($res)) {
            return ['code' => 500, 'msg' => '添加失败'];
        }
        return ['code' => 200, 'msg' => '添加成功'];
    }

    public function add_action() {
        if(is_post()) {
            $res = $this->doAdd();
            if($res['code'] != 200) {
                return $this->_error($res['msg']);
            }
            return $this->_success('添加成功');
        }
        $medicine = lib_medicine_service_goods::searchGoodsList('');
        $factory = lib_medicine_service_factory::searchFactoryList('');
        $data = [
            'medicine' => $medicine,
            'factory' => $factory,
        ];
        $this->_assign($data);
        $this->_render();
    }

    public function edit_action() {
        $inventId = input('inventId', 0, 'intval');
        if(empty($inventId)) {
            return;
        }
        $info = lib_medicine_service_invent::getInventInfoById($inventId);
        $data = [
            'info' => $info,
        ];
        $this->_assign($data);
        $this->_render();
    }
}