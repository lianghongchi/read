<?php

/**
 * 管理员操作日志
 *
 * @authName    管理员操作日志
 * @note        所有管理员的操作记录
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2018-12-22
 *
 */
class admin_log_action_controller extends controller_lib
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
        // 控制器名称
        $controller = input('controller', '');
        // 管理员ID
        $adminid = input('admin_id', '');
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

        $controller && $where['controller'] = $controller;
        $adminid && $where['admin_id'] = $adminid;
        $ip && $where['ip'] = $ip;

        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['more', [
                ['>=', $addTimeStart, 'and'],
                ['<=', $addTimeEnd + 86400],
            ]
            ];
        }

        $model = getInstance('admin_log_action_model');

        $page = $this->_getPageData();

        $data = $model
            ->where($where)
            ->field('auto_id,admin_id,controller,action,agent,ip,result,add_time')
            ->limit($page['start'], $page['pageRows'])
            ->order('auto_id desc')
            ->select();

        $data = $model->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'ip' => htmlspecialchars($ip),
            'controller' => htmlspecialchars($controller),
            'adminid' => $adminid,
            'addTime1' => $addTime1,
            'addTime2' => $addTime2
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 操作记录详情
     */
    public function detail_action()
    {
        $autoid = input('auto_id', 0, 'intval');

        $data = getInstance('admin_log_action_model')->id($autoid)->find();

        if (!$data) {
            return $this->_error('数据不存在');
        }

        $this->_assign('data', $data);
        $this->_render();
    }

}
