<?php
/**
 * 用户进入游戏日志数据操作层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */
DB::initConfig('game');

class user_log_model_enter_lib
{
    /**
     * 查询用户进入游戏日志
     */
    public static function getEnterGameLog($uid)
    {
        DB::setSlave(true);
        $sql = 'SELECT * from t_enter_game where uid = ?';
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
        $sql = 'SELECT * from t_enter_game where uid = ? AND id = ?';
        $result = DB::getOne($sql, [$uid, $id]);
        DB::setSlave(false);
        return $result;
    }
}
