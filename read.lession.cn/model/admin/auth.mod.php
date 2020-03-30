<?php
/**
 * 权限模型
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class admin_auth_model extends lib_dborm_model
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
    protected $_table = 't_admin_auth';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'id';


    /**
     * 格式化数据
     * @param  array $data 数据
     * @return array
     */
    public function format($data)
    {
        if (isset($data['add_time'])) {
            $data['add_time_text'] = date('Y-m-d H:i:s', $data['add_time']);
        }

        return $data;
    }

    /**
     * 按分组排列权限列表
     * @param  array $data string
     * @return array
     */
    public function formatWithTag($data)
    {
        if (!$data) {
            return $data;
        }
        $tagData = [];

        foreach ($data as $key => $value) {
            $tagData[$value['tag_name']][] = $value;
        }

        return $tagData;
    }

}
