<?php
/**
 * 用户登录日志数据操作层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */
DB::initConfig('game');

class user_log_model_login_lib
{
    /**
     * 查询用户登录日志
     */
    public static function getLoginLog($uid)
    {
        DB::setSlave(true);
        $sql = 'SELECT * from t_login_game where uid = ? ORDER BY add_time DESC';
        $result = DB::getAll($sql, [$uid]);
        DB::setSlave(false);
        return $result;
    }

    /**
     * 获取详情信息
     */
    public static function getLoginOne($uid, $id)
    {
        DB::setSlave(true);
        $sql = 'SELECT * from t_login_game where uid = ? AND id = ?';
        $result = DB::getOne($sql, [$uid, $id]);
        DB::setSlave(false);
        return $result;
    }
}
