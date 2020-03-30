<?php
/**
 * 后台管理示例模板
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class admin_log_action_model extends lib_dborm_model
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
    protected $_table = 't_admin_log_action';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'auto_id';


    public function format($data)
    {
        $data = parent::format($data);

        if (isset($data['result'])) {
            $result = $data['result'] ? json_decode($data['result'], true) : '';

            if ($result && isset($result['code'])) {
                $code = $result['code'];
                $msg = $result['msg'];
                $data['result'] = $code . ' / ' . $msg;

            } else if (strlen($data['result']) > 254) {
                $data['result'] = substr($data['result'], 0, 254);
            }
        }

        return $data;
    }


}
