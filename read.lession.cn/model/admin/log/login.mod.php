<?php
/**
 * 后台管理示例模板
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class admin_log_login_model extends lib_dborm_model
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
    protected $_table = 't_admin_log_login';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'auto_id';

    /**
     * 登录状态列表
     * @var array
     */
    protected $statusList = [
        '1' => '登录成功',
        '-1' => '登录密码错误',
        '-2' => 'GA认证失败',
    ];


    /**
     * 格式化数据
     * @param  array $data 数据
     * @return array
     */
    public function format($data)
    {
        if (isset($data['status'])) {
            $data['status_text'] = $this->statusList[$data['status']];
        }

        if (isset($data['add_time'])) {
            $data['add_time_text'] = date('Y-m-d H:i:s', $data['add_time']);
        }

        return $data;
    }

    public function addLog($adminid, $loginState = 1)
    {
        if (!$adminid) {
            return false;
        }
        $request = getInstance('lib_app_request');
        $agent = htmlentities($request->agent());
        $agent = lib_util_safe::removeXss($agent);

        $data = [
            'admin_id' => $adminid,
            'ip' => $request->ip(),
            'agent' => $agent,
            'status' => intval($loginState),
            'add_time' => time(),
        ];

        return $this->insert($data);
    }


}
