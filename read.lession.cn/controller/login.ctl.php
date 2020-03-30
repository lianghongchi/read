<?php
/**
 * 登录 控制器
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-17
 *
 */

class login_controller extends controller_lib
{

    /**
     * 是否需要登录账号
     * @var bool
     */
    protected $_isLoginRequired = false;

    /**
     * 是否加入权限控制
     * @var bool
     */
    protected $_isAuthControl = false;

    /**
     * ga 登录session 名称
     * @var string
     */
    protected $gaLoginSessionName = 'ga_login_adminid';

    /**
     * 管理员登录页面
     * @return
     */
    public function index_action()
    {
        $this->_render();
    }

    /**
     * 管理员登录
     * @return
     */
    public function dologin_action()
    {
        if (!is_post()) {
            return;
        }

        // 获取登录名称
        $loginName = input('login_name', '');
        // 登录密码
        $password = input('password', '');
        // 登录验证图形验证码
        $seccode = input('seccode', '');

        // 图形验证码验证，开发环境跳过
        if (!captcha_image_lib::check($seccode)) {
            return $this->_error('验证码错误');
        }

        if (!ctype_alnum($loginName) || strlen($loginName) < 2 || strlen($loginName) > 16) {
            return $this->_error('用户名称格式错误');
        }

        // 查找管理员
        $adminTable = getInstance('admin_admin_model');
        $admin = $adminTable->field('admin_id,password,salt,is_login_ga,is_locked')->where('admin_name', $loginName)->find();

        // 用户不存在
        if (!$admin) {
            return $this->_error('用户名或密码错误，请重新输入。');
        }

        // 是否被锁定
        if ($admin['is_locked']) {
            return $this->_error('此管理员已被禁用。');
        }

        // 管理员ID
        $adminid = $admin['admin_id'];

        // 同一管理员2秒内 只能登录一次
        $loginLogModel = getInstance('admin_log_login_model');
        $lastLoginLog = $loginLogModel->where('admin_id', $adminid)->order('add_time desc')->find();

        if ($lastLoginLog) {
            if (time() - $lastLoginLog['add_time'] <= 2) {
                return $this->_error('登录过于频繁，请稍后再试。');
            }
        }

        // 验证密码是否正确
        if (!auth_lib::checkPassword($password, $admin['salt'], $admin['password'])) {
            // 密码错误，记录日志
            $this->addLoginLog($adminid, -1);
            return $this->_error('用户名或密码错误，请重新输入。');
        }

        if ($admin['is_login_ga']) {
            $_SESSION[$this->gaLoginSessionName] = $adminid;
            return $this->_render(201, '身份认证');
        }

        $this->adminDoLogin($adminid, $admin['password'], $admin['salt']);

        return $this->_success('登录成功');
    }

    /**
     * 管理员登录执行
     * @param  int $adminid 管理员ID
     * @param  string $password 登录密码
     * @param  string $salt 登录密码盐
     * @param  int $loginState 登录状态，1 登录成功
     * @return
     */
    protected function adminDoLogin($adminid, $password, $salt)
    {
        $tokenString = auth_lib::createToken($adminid, $password, $salt);
        $_SESSION[auth_lib::ADMIN_SESSION_NAME] = $tokenString;
        $this->addLoginLog($adminid, 1);
    }

    /**
     * 记录登录日志
     * @param int $adminid 管理员ID
     * @param int $loginState 登录状态，1 登录成功，-1 登录密码错误, -2 GA密码错误
     */
    protected function addLoginLog($adminid, $loginState)
    {
        return getInstance('admin_log_login_model')->addLog($adminid, $loginState);
    }

    /**
     * 退出登录
     * @return
     */
    public function logout_action()
    {
        // 删除 session
        $_SESSION[auth_lib::ADMIN_SESSION_NAME] = null;
        unset($_SESSION[auth_lib::ADMIN_SESSION_NAME]);

        // 删除 cookie中的 session id
        $sessionCookieName = session_name();

        if (setcookie($sessionCookieName, '', $_SERVER['REQUEST_TIME'] - 3600, '/')) {
            unset($_COOKIE[$sessionCookieName]);
        }
        $this->_redirect(200, '账号退出成功', $this->_baseUrl);
    }

    /**
     * ga登录
     * @return
     */
    public function checkga_action()
    {
        if (!is_post()) {
            return;
        }

        $gaLoginAdminid = $_SESSION[$this->gaLoginSessionName] ?? '';

        if (!$gaLoginAdminid) {
            return $this->_render(210, '认证信息不存在或已过期，请重新登录。');
        }

        $loginName = input('login_name', '');
        $code = input('gacode', '');

        // 用户名、手机号 都可登陆
        $where = is_cellphone($loginName) ? ['cellphone' => $loginName] : ['admin_name' => $loginName];

        $admin = getInstance('admin_admin_model')->field('admin_id,auth_key,password,salt')->where($where)->find();

        if ($admin['admin_id'] != $gaLoginAdminid) {
            return $this->_render(210, '认证的管理员信息与登陆的账户不一致，请重新登录。');
        }

        $authCode = getInstance('ga_lib')->getCode($admin['auth_key']);

        // 开发环境跳过
        if ($code != $authCode && !is_dev()) {
            // GA认证失败，记录登录日志
            $this->addLoginLog($admin['admin_id'], -2);
            return $this->_error('认证失败，请重试。');
        }

        unset($_SESSION[$this->gaLoginSessionName]);

        $this->adminDoLogin($admin['admin_id'], $admin['password'], $admin['salt']);

        return $this->_success('登录成功');
    }

}
