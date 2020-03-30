<?php
/**
 * 管理员模型
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class device_app_model extends lib_dborm_model
{


    /**
     * 数据库名称
     * @var string
     */
    protected $_database = 'device';

    /**
     * 模型表名称
     * @var string
     */
    protected $_table = 'stat_app_id_device_active';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'id';

}
