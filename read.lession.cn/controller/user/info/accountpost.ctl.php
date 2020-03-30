<?php

/**
 * 用户账号信息修改
 *
 * @authName    用户账号信息修改
 * @note        用户账号 进行密码重置 以及 手机号绑定
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-25
 *
 */
class user_info_accountpost_controller extends controller_lib
{
    /**
     * 修改用户名
     */
    public function editusername_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        $result = user_info_account_lib::getUserInfoByUid($uid);
        if (is_post()) {
            $uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $usernamenew = isset($_POST['usernamenew']) ? $_POST['usernamenew'] : '';
            $usernamecom = isset($_POST['usernamecom']) ? $_POST['usernamecom'] : '';
            $remark = isset($_POST['remark']) ? $_POST['remark'] : '';
            $result = user_info_account_lib::updUserName($uid, $username, $usernamenew, $usernamecom, $remark);
            if ($result['code'] != 200) {
                return $this->_error($result['msg']);
            }
            return $this->_success('修改成功');
        }
        $template = [
            'user' => $result,
        ];
        $this->_assign($template);
        $this->_render();
    }

    /**
     * 修改手机号
     */
    public function editmobile_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        $result = user_info_account_lib::getUserInfoByUid($uid);
        if (is_post()) {
            $uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
            $mobile = isset($_POST['mobile']) ? $_POST['mobile'] : '';
            $mobilenew = isset($_POST['mobilenew']) ? $_POST['mobilenew'] : '';
            $mobilecom = isset($_POST['mobilecom']) ? $_POST['mobilecom'] : '';
            $remark = isset($_POST['remark']) ? $_POST['remark'] : '';
            $result = user_info_account_lib::updMobile($uid, $mobile, $mobilenew, $mobilecom, $remark);
            if ($result['code'] != 200) {
                return $this->_error($result['msg']);
            }
            return $this->_success('修改成功');
        }
        $template = [
            'user' => $result,
        ];
        $this->_assign($template);
        $this->_render();
    }

    /**
     * 修改密码
     */
    public function editpassword_action()
    {
        $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;
        if (is_post()) {
            $uid = isset($_POST['uid']) ? $_POST['uid'] : 0;
            $passwordnew = isset($_POST['passwordnew']) ? $_POST['passwordnew'] : '';
            $passwordcom = isset($_POST['passwordcom']) ? $_POST['passwordcom'] : '';
            $remark = isset($_POST['remark']) ? $_POST['remark'] : '';
            $result = user_info_account_lib::updPassword($uid, $passwordnew, $passwordcom, $remark);
            if ($result['code'] != 200) {
                return $this->_error($result['msg']);
            }
            return $this->_success('修改成功');
        }
        $template = [
            'uid' => $uid,
        ];
        $this->_assign($template);
        $this->_render();
    }

}
