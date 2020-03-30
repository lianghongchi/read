<?php
/**
 * 管理员service
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-13
 *
 */

class admin_service
{



    /**
     * 根据管理员ID获取管理员信息
     * @param  int $adminid 管理员id
     * @return array
     */
    public function getAdminByAdminid($adminid)
    {
        if (!($adminid = intval($adminid))) {
            return false;
        }

        return getInstance('admin_admin_model')->id($adminid)->find();
    }



}
