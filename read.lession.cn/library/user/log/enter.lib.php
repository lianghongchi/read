<?php
/**
 * 用户进入游戏日志逻辑层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */

class user_log_enter_lib
{
    /**
     * 查询用户进入游戏日志
     */
    public static function getEnterGameLog($key, $search)
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

    public static function getUserInfoByUid($uid)
    {
        if (!$uid) {
            return array();
        }
        $result = user_log_model_enter_lib::getEnterGameLog($uid);
        //整理数据
        if ($result) {
            return $result;
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
     * 获取详情
     */
    public static function getLoginOne($uid, $id)
    {
        if (!$uid || !$id) {
            return array();
        }
        $result = user_log_model_enter_lib::getLoginOne($uid, $id);
        if ($result) {
            return $result;
        } else {
            return array();
        }
    }
}
