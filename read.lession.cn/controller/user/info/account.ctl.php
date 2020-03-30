<?php

/**
 * 用户账号查询
 *
 * @authName    用户账号查询
 * @note        用户账号查询 可以进行密码重置以及 手机号绑定
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-25
 *
 */
class user_info_account_controller extends controller_lib
{

    /**
     * 账号查询页
     */
    public function index_action()
    {
        $this->_assign();
        $this->_render();
    }

    /**
     * 查看修改账号信息
     */
    public function cat_action()
    {
        $key = isset($_POST['key']) ? $_POST['key'] : '';
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $result = user_info_account_lib::getUserPro($key,$search);
        echo json_encode($result);
    }

}
