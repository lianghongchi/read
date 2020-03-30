<?php
/**
 * 管理员=>分组模型
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class admin_rolemap_model extends lib_dborm_model
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
    protected $_table = 't_admin_rolemap';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'id';


}
