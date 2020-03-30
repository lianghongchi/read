<?php
/**
 * 药品--药品模块
 *
 * @authName    药品库存管理--厂家管理
 * @note        药品库存管理--厂家管理
 * @package     KIS
 * @author      Freedom
 * @since       2019-09-11
 * 药品库存管理的模块
 */
class medicine_factory_controller extends controller_lib {
    /**
     * 厂家列表
     */
    public function index_action() {
        $word = input('word', '');
        //根据word查询
        $pageRow = 0;
        $pageRow || $pageRow = input('pagerows', 0);
        $pageRow < 1 && $pageRow = 30;
        $pageRow > 200 && $pageRow = 200;
        $count = lib_medicine_service_factory::searchFactoryCount($word);
        $page = $this->_getPageData($pageRow, $count);
        $list = lib_medicine_service_factory::searchFactoryList($word, $page['start'], $pageRow);
        $data = [
            'word' => $word,
            'list' => $list,
        ];
        $this->_assign($data);
        $this->_render();
    }

    public function add_action() {
        if(is_post()) {
            $res = $this->doAddFactory();
            if($res['code'] != 200) {
                return $this->_error($res['msg']);
            }
            return $this->_success('添加成功');
        }
        $this->_render();
    }

    public function update_action() {
        if(is_post()) {
            $factoryId = input('factoryId', 0, 'intval');
            $factoryName = input('factoryName', '', 'trim');
            if(empty($factoryId) || empty($factoryName)) {
                return $this->_error('参数错误');
            }
            $count = lib_medicine_service_factory::getFactoryCountByIdName($factoryId, $factoryName);
            if(is_numeric($count) && $count > 0) {
                return $this->_error('已经存在名称');
            }
            $res = lib_medicine_service_factory::updateFactory($factoryId, $factoryName);
            if($res) {
                return $this->_success('修改成功');
            }
        }
        return $this->_error('修改失败');
    }

    private function doAddFactory() {
        $factoryName = input('factoryName', '', 'trim');
        if(empty($factoryName)) {
            return ['code' => 500, 'msg' => '厂家名称不能为空'];
        }
        $count = lib_medicine_service_factory::searchFactoryCount($factoryName);
        if($count > 0) {
            return ['code' => 500, 'msg' => '已存在的厂家名称'];
        }
        $res = lib_medicine_service_factory::addFactory($factoryName);
        if(empty($res)) {
            return ['code' => 500, 'msg' => '添加失败'];
        }
        return ['code' => 200, '添加成功'];
    }

    public function edit_action() {
        $id = input('factoryId', 0, 'intval');
        if(empty($id)) {
            return;
        }
        $info = lib_medicine_service_factory::getFactoryInfoById($id);
        $data = [
            'info' => $info,
        ];
        $this->_assign($data);
        $this->_render();
    }
}