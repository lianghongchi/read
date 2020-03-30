<?php
/**
 * 角色信息数据操作层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */
DB::initConfig('game');

class user_info_model_role_lib
{
    /**
     * 查询用户游戏角色信息
     */
    public static function getRoleInfo($uid)
    {
        DB::setSlave(true);
        $sql = 'SELECT * from t_game_role where uid = ?';
        $result = DB::getAll($sql, [$uid]);
        DB::setSlave(false);
        return $result;
    }

    public static function getOneRole($uid,$mid,$gid,$sid,$role){
        DB::setSlave(true);
        $sql = 'SELECT * from t_game_role where uid = ? AND mid = ? AND gid = ? AND sid = ? AND role = ?';
        $result = DB::getOne($sql, [$uid,$mid,$gid,$sid,$role]);
        DB::setSlave(false);
        return $result;
    }
}
