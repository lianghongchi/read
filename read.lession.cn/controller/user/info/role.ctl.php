<?php

/**
 * 用户游戏角色信息查询
 *
 * @authName    用户游戏角色信息查询
 * @note        根据用户uid查询用户游戏角色信息变化
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-25
 *
 */
class user_info_role_controller extends controller_lib
{

    /**
     * 输入查询页
     */
    public function index_action()
    {
        $this->_assign();
        $this->_render();
    }

    /**
     * 角色信息展示页
     */
    public function cat_action()
    {
        $key = isset($_POST['key']) ? $_POST['key'] : '';
        $search = isset($_POST['search']) ? $_POST['search'] : '';

        $roleInfo = user_info_role_lib::getRoleInfo($search);
        echo json_encode($roleInfo);
    }

    /**
     * 查看角色详情信息
     */
    public function info_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : '';
        $mid = isset($_GET['mid']) ? $_GET['mid'] : '';
        $gid = isset($_GET['gid']) ? $_GET['gid'] : '';
        $sid = isset($_GET['sid']) ? $_GET['sid'] : '';
        $role = isset($_GET['role']) ? $_GET['role'] : '';
        $roleInfo = user_info_role_lib::getOneRole($uid,$mid,$gid,$sid,$role);

        $template = [
            'user' => $roleInfo
        ];
        $this->_assign($template);
        $this->_render();
    }

}
