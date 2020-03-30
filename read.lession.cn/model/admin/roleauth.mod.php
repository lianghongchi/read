<?php
/**
 * 分组=>权限模型
 *
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2018-11-22
 *
 */

class admin_roleauth_model extends lib_dborm_model
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
    protected $_table = 't_admin_role_auth';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'id';


}
