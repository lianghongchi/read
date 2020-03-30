<?php

/**
 * 管理员总列表
 *
 * @authName    管理员总列表
 * @note        对所有管理员的列表展示
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-22
 *
 */
class admin_admin_controller extends controller_lib
{

    /**
     * 管理员总列表
     */
    public function index_action()
    {
        // 姓名
        $name = input('name', '');
        // 手机号
        $cellphone = input('cellphone', '');

        // 添加时间
        $addTime1 = input('add_time1', false);
        $addTimeStart = $addTime1 ? strtotime($addTime1) : '';
        $addTimeStart || $addTime1 = '';

        $addTime2 = input('add_time2', false);
        $addTimeEnd = $addTime2 ? strtotime($addTime2) : '';
        $addTimeEnd || $addTime2 = '';

        $where = [];

        $name && $where['name'] = ['like', "%{$name}%"];
        $cellphone && $where['cellphone'] = ['like', "%{$cellphone}%"];

        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['more', [
                ['>=', $addTimeStart, 'and'],
                ['<=', $addTimeEnd + 86400],
            ]
            ];
        }
        $adminModel = getInstance('admin_admin_model');

        $page = $this->_getPageData();

        // 查询数据
        $data = $adminModel->where($where)->limit($page['start'], $page['pageRows'])->order('admin_id desc')->select();
        $data = $adminModel->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'name' => htmlspecialchars($name),
            'cellphone' => htmlspecialchars($cellphone),
            'addTime1' => $addTime1,
            'addTime2' => $addTime2,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }


}
