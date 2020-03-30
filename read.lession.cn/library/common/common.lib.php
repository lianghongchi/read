<?php
/**
 * 公用lib
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */

class common_common_lib
{

    /**
     * 密码加密
     */
    public static function encodePwd($password)
    {
        $str = md5($password);
        return md5($str . "kissnextjoys" . substr($str, 0, 10));
    }

    /**
     * 检查手机号格式
     */
    public static function checkMobileFormat($mobile = null, $response = true)
    {
        if (!$mobile) {
            return FALSE;
        }
        if (!preg_match('/^1[3456789]\d{9}$/', $mobile)) {
            return FALSE;
        }
        return true;
    }
}
