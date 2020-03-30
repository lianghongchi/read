<?php
/**
 * 管理员登录日志
 *
 * @authName    管理员登录日志
 * @note        所有管理的登陆日志
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2018-12-22
 *
 */

class admin_log_login_controller extends controller_lib
{


    /**
     * 是否记录操作日志
     */
    protected $_isRecordActionLog = false;

    /**
     * 管理员操作日志列表
     */
    public function index_action()
    {
        // 管理员ID
        $adminid = input('admin_id', '');
        // 登录状态
        $status = input('status', '', 'intval');
        // IP
        $ip = input('ip', '');

        // 注册时间
        $addTime1 = input('add_time1', false);
        $addTimeStart = $addTime1 ? strtotime($addTime1) : '';
        $addTimeStart || $addTime1 = '';

        $addTime2 = input('add_time2', false);
        $addTimeEnd = $addTime2 ? strtotime($addTime2) : '';
        $addTimeEnd || $addTime2 = '';

        $where = [];

        $adminid && $where['admin_id'] = $adminid;
        $status && $where['status'] = $status;
        $ip && $where['ip'] = $ip;

        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['more', [
                ['>=', $addTimeStart, 'and'],
                ['<=', $addTimeEnd + 86400],
            ]
            ];
        }

        $model = getInstance('admin_log_login_model');

        $page = $this->_getPageData();

        $data = $model
            ->where($where)
            ->limit($page['start'], $page['pageRows'])
            ->order('auto_id desc')
            ->select();

        $data = $model->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'ip' => htmlspecialchars($ip),
            'status' => $status,
            'statusList' => $model->statusList,
            'adminid' => $adminid,
            'addTime1' => $addTime1,
            'addTime2' => $addTime2
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

}
