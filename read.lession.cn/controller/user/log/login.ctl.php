<?php

/**
 * 用户登陆日志查询
 *
 * @authName    用户登陆日志查询
 * @note        用户登陆日志查询
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-25
 *
 */
class user_log_login_controller extends controller_lib
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
     * 登陆日志展示页
     */
    public function cat_action()
    {
        $key= isset($_POST['key']) ? $_POST['key'] : '';
        $search = isset($_POST['search']) ? $_POST['search'] : '';
        $loginInfo = user_log_login_lib::getLoginLog($key,$search);
        echo json_encode($loginInfo);
    }

    /**
     * 查看登陆日志详情
     */
    public function info_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : '';
        $id = isset($_GET['id']) ? $_GET['id'] : '';

        $loginInfo = user_log_login_lib::getLoginOne($uid, $id);
        $template = [
            'login' => $loginInfo
        ];
        $this->_assign($template);
        $this->_render();

    }

}