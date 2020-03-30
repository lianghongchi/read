<?php
/**
 * 管理员认证token管理类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-13
 *
 */

class auth_lib
{

    /**
     * 时间验签秘钥
     * @var string
     */
    const TIMESIGN_SALT = 'xs16mq';

    /**
     * token session名称
     * @var string
     */
    const ADMIN_SESSION_NAME = 'djadminxs_adminqs';


    /**
     * 根据header中的token获取管理员信息
     * @return array 管理员信息
     */
    public static function getAdminForToken()
    {
        // session 中获取 token
        $authToken = $_SESSION[self::ADMIN_SESSION_NAME] ?? '';

        if (!$authToken) {
            return false;
        }

        // base64解码， 按 \t 拆分为token验签字段
        $tokenString = base64_decode($authToken);

        if (!$tokenString || !is_string($tokenString)) {
            return false;
        }

        $tokenArray = explode("\t", $tokenString);

        if (!is_array($tokenArray) || count($tokenArray) != 4) {
            return false;
        }

        list($sign, $timeSign, $adminid, $expiredTime) = $tokenArray;

        // 第一层验证token是否过期
        if (time() > $expiredTime) {
            return false;
        }

        // 第二层时间验签
        if ($timeSign != md5($adminid . $expiredTime . self::TIMESIGN_SALT)) {
            return false;
        }

        // 获取用户信息
        $admin = getInstance('admin_service')->getAdminByAdminid($adminid);

        if (!$admin) {
            return false;
        }

        // 第三层密码验签
        if ($sign != md5($adminid . $admin['password'] . $admin['salt'] . $expiredTime)) {
            return false;
        }

        return $admin;
    }

    /**
     * 创建用户认证token
     * @param  int $adminid 用户idea
     * @param  string $password 用户登录密码
     * @return string 认证token
     */
    public static function createToken($adminid, $password, $salt)
    {
        $adminid = intval($adminid);

        if (!$adminid) {
            return false;
        }

        // token过期时间: 1天
        $expiredTime = get_time() + 86400;

        // 密码签名
        $sign = md5($adminid . $password . $salt . $expiredTime);

        // 过期时间签名
        $timeSign = md5($adminid . $expiredTime . self::TIMESIGN_SALT);

        // 生成token字符串
        $tokenArray = [$sign, $timeSign, $adminid, $expiredTime];

        return base64_encode(implode($tokenArray, "\t"));
    }

    /**
     * 创建管理员登录密码
     * @param  string $password 可以是原文密码，可以是32位 双md5字符串
     * @return array
     */
    public static function createPasswordString($password)
    {
        $md5password = (strlen($password) == 32) ? $password : md5(md5($password));
        $saltArray = [];

        for ($i = 0; $i < 6; $i++) {
            $saltArray[] = mt_rand(0, 9);
        }
        $salt = implode('', $saltArray);

        return [sha1($md5password . $salt), $salt];
    }

    /**
     * 检查登录密码
     * @param  string $password 强制32位 双md5 字符串
     * @param  string $salt 密码盐
     * @param  string $checkPassword 40位 sha1 字符串
     * @return bool
     */
    public static function checkPassword($password, $salt, $checkPassword)
    {
        if (strlen($password) != 32) {
            return false;
        }

        if (sha1(strtolower($password) . $salt) == $checkPassword) {
            return true;
        }

        return false;
    }


}
