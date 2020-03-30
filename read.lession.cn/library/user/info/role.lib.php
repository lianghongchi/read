<?php
/**
 * 角色信息逻辑层
 *
 * @package     KIS
 * @author      mrasked <marsked@163.com>
 * @since       2019-03-25
 *
 */

class user_info_role_lib
{
    /**
     * 查询用户角色信息
     */
    public static function getRoleInfo($uid)
    {
        if (!$uid) {
            return array();
        }
        $result = user_info_model_role_lib::getRoleInfo($uid);

        if ($result) {
            foreach($result as $key => $val){
                if($val['is_first']){
                    $result[$key]['is_first'] = '是';
                }else{
                    $result[$key]['is_first'] = '否';
                }
            }
            return $result;
        } else {
            return array();
        }
    }

    public static function getOneRole($uid,$mid,$gid,$sid,$role){
        if (!$uid || !$mid || !$gid || !$sid || !$role) {
            return array();
        }

        $result = user_info_model_role_lib::getOneRole($uid,$mid,$gid,$sid,$role);

        if ($result) {
            if($result['is_first']){
                $result['is_first'] = '是';
            }else{
                $result['is_first'] = '否';
            }
            return $result;
        } else {
            return array();
        }
    }
}
