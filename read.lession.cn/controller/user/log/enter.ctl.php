<?php

/**
 * 用户进入游戏日志查询
 *
 * @authName    用户进入游戏日志查询
 * @note        用户进入游戏日志查询
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-25
 *
 */
class user_log_enter_controller extends controller_lib
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
     * 进入游戏日志展示页
     */
    public function cat_action()
    {
        $key= isset($_POST['key']) ? $_POST['key'] : '';
        $search = isset($_POST['search']) ? $_POST['search'] : '';

        $enterInfo = user_log_enter_lib::getEnterGameLog($key,$search);

        echo json_encode($enterInfo);
    }

    /**
     * 查看进入游戏日志详情
     */
    public function info_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : '';
        $id = isset($_GET['id']) ? $_GET['id'] : '';

        $loginInfo = user_log_enter_lib::getLoginOne($uid, $id);
        $template = [
            'enter' => $loginInfo
        ];
        $this->_assign($template);
        $this->_render();

    }


}