<?php
/**
 * 用户信息逻辑层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */

class user_info_account_lib
{
    /**
     * 查询用户信息
     */
    public static function getUserInfoByUid($uid)
    {
        if (!$uid) {
            return array();
        }
        $result = user_info_model_account_lib::getUserInfoByUid($uid);
        //整理数据
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }

    /**
     * 修改用户信息
     */
    public static function UpdUserInfo($mobile, $password, $remark, $uid, $result)
    {
        if (!$remark) {
            return return_format(400, '备注不可为空');
        }
        if (!$password) {
            return return_format(400, '密码不可为空');
        }
        if ($result['mobile'] == $mobile && ($result['password'] == $password || $result['password'] == common_common_lib::encodePwd($password))) {
            return return_format(400, '无信息修改');
        }
        if (!common_common_lib::checkMobileFormat($mobile)) {
            return return_format(400, '手机号格式不正确');
        }
        $mobileInfo = user_info_model_account_lib::checkMobile($mobile, $uid);
        if (!empty($mobileInfo) && $mobileInfo['uid']) {
            return return_format(400, '手机号已绑定请更换新手机号');
        }
        if ($result['password'] != $password) {
            $password = common_common_lib::encodePwd($password);
        }
        if ($result['mobile'] == '') {
            $return = user_info_model_account_lib::createMobile($mobile, $uid);
            if (!$return) {
                return return_format(400, '新增手机号失败');
            }
        } else {
            $return = user_info_model_account_lib::delMobile($result['mobile']);
            $return = user_info_model_account_lib::createMobile($mobile, $uid);
            if (!$return) {
                return return_format(400, '修改手机号失败');
            }
        }
        $return = user_info_model_account_lib::updUserInfo($mobile, $password, $uid);
        if ($return) {
            return return_format(200, '修改成功');
        } else {
            return return_format(400, '修改失败');
        }


    }

    /**
     * 查询用户信息逻辑处理
     */
    public static function getUserPro($key, $search)
    {
        if ($key == 'uid') {
            return self::getUserInfoByUid($search);
        } else if ($key == 'username') {
            return self::getUidByUserName($search);
        } else if ($key == 'mobile') {
            return self::getUidByMobile($search);
        } else {
            return array();
        }
    }

    /**
     * 根据用户名查询用户信息
     */
    public static function getUidByUserName($username)
    {
        $result = user_info_model_account_lib::getUidByUserName($username);
        if (empty($result)) {
            return array();
        } else {
            return self::getUserInfoByUid($result['uid']);
        }
    }

    /**
     * 根据手机号查找用户信息
     */
    public static function getUidByMobile($mobile)
    {
        $result = user_info_model_account_lib::getUidByMobile($mobile);
        if (empty($result)) {
            return array();
        } else {
            return self::getUserInfoByUid($result['uid']);
        }
    }

    /**
     * 修改用户名
     */
    public static function updUserName($uid, $username, $usernamenew, $usernamecom, $remark)
    {
        if ($uid == 0 || !$username || !$usernamenew || !$usernamecom || !$remark) {
            return return_format(400, '参数缺失');
        }

        if ($username == $usernamenew) {
            return return_format(400, '修改无变化');
        }

        if ($usernamenew != $usernamecom) {
            return return_format(400, '两次用户名输入不一致');
        }
        $checkUsername = user_info_model_account_lib::checkUsername($usernamenew, $uid);
        if (!empty($checkUsername)) {
            return return_format(400, '用户名已存在');
        }
        //删除原表
        $result = user_info_model_account_lib::delUsername($username);

        if (!$result) {
            return return_format(400, '修改失败');
        }
        //增加新数据
        $result = user_info_model_account_lib::createUsername($usernamenew, $uid);

        if (!$result) {
            return return_format(400, '修改失败');
        }
        //更新tb_user
        $result = user_info_model_account_lib::updUserInfo('username', $usernamenew, $uid);

        if ($result) {
            return return_format(200, '修改成功');
        } else {
            return return_format(400, '修改失败');
        }

    }

    /**
     * 修改手机号
     */
    public static function updMobile($uid, $mobile, $mobilenew, $mobilecom, $remark)
    {
        if ($uid == 0 || !$mobile || !$mobilenew || !$mobilecom || !$remark) {
            return return_format(400, '参数缺失');
        }

        if ($mobile == $mobilenew) {
            return return_format(400, '修改无变化');
        }

        if ($mobilenew != $mobilecom) {
            return return_format(400, '两次手机号输入不一致');
        }

        $checkMobile = user_info_model_account_lib::checkMobile($mobilenew, $uid);
        if (!empty($checkMobile)) {
            return return_format(400, '手机号已存在');
        }

        //删除原表
        $result = user_info_model_account_lib::delMobile($mobile);

        if (!$result) {
            return return_format(400, '修改失败');
        }
        //增加新数据
        $result = user_info_model_account_lib::createMobile($mobilenew, $uid);

        if (!$result) {
            return return_format(400, '修改失败');
        }
        //更新tb_user
        $result = user_info_model_account_lib::updUserInfo('mobile', $mobilenew, $uid);

        if ($result) {
            return return_format(200, '修改成功');
        } else {
            return return_format(400, '修改失败');
        }
    }

    /**
     * 修改密码
     */
    public static function updPassword($uid, $passwordnew, $passwordcom, $remark)
    {
        if ($uid == 0 || !$passwordnew || !$passwordcom || !$remark) {
            return return_format(400, '参数缺失');
        }
        if ($passwordnew != $passwordcom) {
            return return_format(400, '两次密码输入不一致');
        }
        $result = self::getUserInfoByUid($uid);
        $passwordnew = common_common_lib::encodePwd($passwordnew);
        if ($result['password'] == $passwordnew) {
            return return_format(400, '密码无变化');
        }

        $result = user_info_model_account_lib::updUserInfo('password', $passwordnew, $uid);

        if ($result) {
            return return_format(200, '修改成功');
        } else {
            return return_format(400, '修改失败');
        }

    }
}
