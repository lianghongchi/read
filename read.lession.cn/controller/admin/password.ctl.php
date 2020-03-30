<?php
/**
 * 修改管理员登录密码
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-18
 *
 */

class admin_password_controller extends controller_lib
{

    /**
     * 是否加入权限控制
     * @var bool
     */
    protected $_isAuthControl = false;

    /**
     * 修改登录密码
     * @return
     */
    public function edit_action()
    {
        if (is_post()) {
            // 原登录密码
            $oldpassport = input('oldpassword', '');
            // 新登录密码
            $password = input('password', '');

            // 检查参数
            if (!ctype_graph($password)) {
                return $this->_error('设置的登录密码不能包含空格');
            }

            if (strlen($password) < 6 || strlen($password) > 16) {
                return $this->_error('请输入6-16位的登录密码');
            }

            // 检查原密码
            if (!auth_lib::checkPassword(md5(md5($oldpassport)), $this->_admin['salt'], $this->_admin['password'])) {
                return $this->_error('原登录密码错误');
            }

            $loginPassword = auth_lib::createPasswordString($password);

            $updateAdmin = [
                'password' => $loginPassword[0],
                'salt' => $loginPassword[1],
            ];

            getInstance('admin_admin_model')->id($this->_adminid)->update($updateAdmin);

            return $this->_success('登录密码修改成功');
        }

        $this->_render();
    }


}
