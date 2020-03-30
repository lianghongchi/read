<?php
/**
 * 用户信息数据操作层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */
DB::initConfig('game');

class user_info_model_account_lib
{
    /**
     * 查询用户信息
     */
    public static function getUserInfoByUid($uid)
    {
        DB::setSlave(true);
        $sql = "SELECT * from tb_user where uid = ?";
        $result = DB::getOne($sql, [$uid]);
        DB::setSlave(false);
        return $result;
    }

    /**
     * 修改用户信息
     */
    public static function updUserInfo($key, $value, $uid)
    {
        $sql = "UPDATE tb_user SET " . $key . " = ?WHERE uid = ?";

        $result = db::update($sql, [$value, $uid]);

        return $result;
    }

    /**
     * 根据用户名查找UID
     */
    public static function getUidByUserName($username)
    {
        DB::setSlave(true);
        $result = db::getOne("SELECT * FROM tb_username WHERE username = ?", [$username]);
        DB::setSlave(false);
        return $result;
    }

    /**
     * 根据手机号查找uid
     */
    public static function getUidByMobile($mobile)
    {
        DB::setSlave(true);
        $result = db::getOne("SELECT * FROM tb_mobile WHERE mobile = ?", [$mobile]);
        DB::setSlave(false);
        return $result;
    }

    /**
     * 检查用户名是否存在
     */
    public static function checkUsername($username, $uid)
    {
        DB::setSlave(true);
        $uidinfo = db::getOne("SELECT * FROM tb_username WHERE username = ? AND uid != ?", [$username, $uid]);
        DB::setSlave(false);
        return $uidinfo;
    }

    /**
     * 删除原有用户名
     */
    public static function delUsername($username)
    {
        $sql = "DELETE FROM tb_username WHERE username = ? LIMIT 1";

        $result = DB::delete($sql, [$username]);

        return $result;
    }

    /**
     * 创建新用户名
     */
    public static function createUsername($username, $uid)
    {
        $sql = 'INSERT INTO tb_username (uid,username) VALUES (:uid,:username)';
        $params = [
            'uid' => $uid,
            'username' => $username,
        ];

        $result = DB::Query($sql, $params);

        return $result;
    }

    /**
     * 检查手机号是否绑定
     */
    public static function checkMobile($mobile, $uid)
    {
        DB::setSlave(true);
        $uidinfo = db::getOne("SELECT * FROM tb_mobile WHERE mobile = ? AND uid != ?", [$mobile, $uid]);
        DB::setSlave(false);
        return $uidinfo;
    }

    /**
     * 删除tb_mobile表中信息
     */
    public static function delMobile($mobile)
    {
        $sql = "DELETE FROM tb_mobile WHERE mobile = ? LIMIT 1";

        $result = DB::delete($sql, [$mobile]);

        return $result;
    }

    /**
     * 更新tb_mobile 表
     */
    public static function createMobile($mobile, $uid)
    {
        $sql = 'INSERT INTO tb_mobile (uid,mobile) VALUES (:uid,:mobile)';
        $params = [
            'uid' => $uid,
            'mobile' => $mobile,
        ];

        $result = DB::Query($sql, $params);

        return $result;
    }


}
