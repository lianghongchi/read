<?php
/**
 * 药品--药品模块
 *
 * @authName    药品库存管理--药品名称管理
 * @note        药品库存管理--药品名称管理
 * @package     KIS
 * @author      Freedom
 * @since       2019-09-11
 * 药品库存管理的模块
 */
class medicine_goods_controller extends controller_lib {
    public function index_action() {
        $word = input('word', '');
        //根据word查询
        $pageRow = 0;
        $pageRow || $pageRow = input('pagerows', 0);
        $pageRow < 1 && $pageRow = 30;
        $pageRow > 200 && $pageRow = 200;
        $count = lib_medicine_service_goods::searchGoodsCount($word);
        $page = $this->_getPageData($pageRow, $count);
        $list = lib_medicine_service_goods::searchGoodsList($word, $page['start'], $pageRow);
        $data = [
            'word' => $word,
            'list' => $list,
        ];
        $this->_assign($data);
        $this->_render();
    }

    public function add_action() {
        if(is_post()) {
            $res = $this->doAddGoods();
            if($res['code'] != 200) {
                return $this->_error($res['msg']);
            }
            return $this->_success('添加成功');
        }
        $this->_render();
    }

    public function update_action() {
        if(is_post()) {
            $medicineId = input('medicineId', 0, 'intval');
            $medicineName = input('medicineName', '', 'trim');
            if(empty($medicineName) || empty($medicineId)) {
                return $this->_error('参数错误');
            }
            $count = lib_medicine_service_goods::getGoodsCountByIdName($medicineId, $medicineName);
            if(is_numeric($count) && $count > 0) {
                return $this->_error('已经存在当前名称');
            }
            $res = lib_medicine_service_goods::updateGoods($medicineId, $medicineName);
            if($res) {
                return $this->_success('修改成功');
            }
        }
        return $this->_error('修改失败');
    }

    private function doAddGoods() {
        $medicineName = input('medicineName', '', 'trim');
        if(empty($medicineName)) {
            return ['code' => 500, 'msg' => '药品名称不能为空'];
        }
        $count = lib_medicine_service_goods::searchGoodsCount($medicineName);
        if($count > 0) {
            return ['code' => 500, 'msg' => '已存在的药品名称'];
        }
        $res = lib_medicine_service_goods::addGoods($medicineName);
        if(empty($res)) {
            return ['code' => 500, 'msg' => '添加失败'];
        }
        return ['code' => 200, '添加成功'];
    }

    public function edit_action() {
        $id = input('medicineId', 0, 'intval');
        if(empty($id)) {
            return;
        }
        $info = lib_medicine_service_goods::getGoodsInfoById($id);
        $data = [
            'info' => $info,
        ];
        $this->_assign($data);
        $this->_render();
    }
}