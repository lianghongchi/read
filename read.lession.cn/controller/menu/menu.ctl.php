<?php

/**
 * 菜单列表
 *
 * @authName    菜单列表
 * @note        后台左侧菜单列表
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-22
 *
 */
class menu_menu_controller extends controller_lib
{
    /**
     * 菜单列表
     */
    public function index_action()
    {

        $menuModel = getInstance('menu_menu_model');
        $data = $menuModel->select();
        $data = $menuModel->formatMulti($data);
        $data = $menuModel->getSortMenu($data);
        $tpldata = [
            'data' => $data,
        ];
        $this->_assign($tpldata);
        $this->_render();
    }
}
