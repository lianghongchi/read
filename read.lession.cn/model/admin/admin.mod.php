<?php
/**
 * 管理员模型
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class admin_admin_model extends lib_dborm_model
{


    /**
     * 数据库名称
     * @var string
     */
    protected $_database = 'admin';

    /**
     * 模型表名称
     * @var string
     */
    protected $_table = 't_admin';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'admin_id';


    /**
     * 获取管理员权限分组列表
     * @param  int $adminid 管理员ID
     * @return array
     */
    public function getRolemap($adminid)
    {
        if (!$adminid) {
            return false;
        }
        $list = getInstance('admin_rolemap_model')->field('id,role_id')->where('admin_id', $adminid)->select();

        return $list;
    }


}
