<?php

/**
 * 后台管理示例模板
 *
 * @authName    后台管理示例模板
 * @note        后台管理示例模板
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2018-12-17
 *
 */
class develop_template_controller extends controller_lib
{


    /**
     * 是否加入权限控制
     * @var bool
     */
    protected $_isAuthControl = false;


    public function index_action()
    {
        // 用户id
        $uid = input('uid', '', 'intval');
        // 用户昵称
        $usernick = input('name', '');
        // 用户状态
        $status = input('status', '');
        // 是否已锁定
        $isLocked = input('is_locked', '', 'intval');
        // 注册时间
        $addTime1 = input('add_time1', false);
        $addTimeStart = $addTime1 ? strtotime($addTime1) : '';
        $addTimeStart || $addTime1 = '';

        $addTime2 = input('add_time2', false);
        $addTimeEnd = $addTime2 ? strtotime($addTime2) : '';
        $addTimeEnd || $addTime2 = '';

        // 用户状态列表
        $statusList = $this->getStatusList();

        $where = [];

        $uid && $where['uid'] = $uid;
        $usernick && $where['user_nick'] = ['like', "%{$usernick}%"];

        if (array_key_exists($status, $statusList)) {
            $where['status'] = $status;
        }

        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['more', [
                ['>=', $addTimeStart, 'and'],
                ['<=', $addTimeEnd + 86400],
            ]
            ];
        }

        $testModel = getInstance('test_model');

        $page = $this->_getPageData();

        $data = $testModel->where($where)->limit($page['start'], $page['pageRows'])->order('uid desc')->select();
        $data = $testModel->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'uid' => $uid,
            'usernick' => htmlspecialchars($usernick),
            'status' => $status,
            'statusList' => $statusList,
            'isLocked' => $isLocked,
            'addTime1' => $addTime1,
            'addTime2' => $addTime2
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    public function detail_action()
    {
        $uid = input('uid', '', 'intval');

        $data = getInstance('test_model')->id($uid)->find();

        if (!$data) {
            return $this->_error('用户不存在');
        }

        $this->_assign('data', $data);
        $this->_render();
    }

    public function add_action()
    {
        if (is_post()) {
            return $this->_success('服务器收到数据:<br>' . json_encode($_REQUEST, JSON_UNESCAPED_UNICODE));
        }

        $tpldata = [
            'cityList' => $this->getCityList(),
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

    protected function getStatusList()
    {
        return getInstance('test_model')->statusList;
    }

    public function add2_action()
    {
        if (is_post()) {
            // 检查参数

            // 用户昵称
            $usernick = input('user_nick', '');
            // 密码
            $password = input('password', '');
            // 城市
            $city = input('city', '');
            // 性别
            $sex = input('sex', '', 'intval');

            if (strlen($usernick) < 2 || strlen($usernick) > 16) {
                return $this->_error('昵称必须是2-16个字符');
            }
            if (!$password) {
                return $this->_error('请设置登录密码');
            }
            $sex = $sex == 1 ? 1 : 2;

            $data = [
                'user_nick' => $usernick,
                'password' => md5($password),
                'city' => $city,
                'sex' => $sex,
                'add_time' => time(),
            ];
            $id = getInstance('test_model')->insert($data);

            if (!$id) {
                return $this->_error('添加失败');
            }

            return $this->_success('添加成功');
        }

        $tpldata = [
            'cityList' => $this->getCityList(),
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

    // 编辑
    public function edit_action()
    {
        $uid = input('uid', 0, 'intval');
        $data = getInstance('test_model')->id($uid)->find();

        if (!$data) {
            return $this->_error('用户不存在');
        }

        if (is_post()) {
            // 用户昵称
            $usernick = input('user_nick');
            // 所在城市
            $city = input('city');
            // 性别
            $sex = input('sex', 1, 'intval');
            $sex = $sex == 1 ? 1 : 2;

            $updateUser = [
                'user_nick' => $usernick,
                'city' => $city,
                'sex' => $sex
            ];

            // 数据是否变更
            if (array_intersect_key($data, $updateUser) == $updateUser) {
                return $this->_error('信息无变化，无需保存');
            }

            $updateState = getInstance('test_model')->id($uid)->update($updateUser);

            if (!$updateState) {
                return $this->_error('修改失败');
            }

            return $this->_success('信息修改成功');
        }

        $tpldata = [
            'cityList' => $this->getCityList(),
            'data' => ['user_nick' => '公子再次', 'city' => 'beijing', 'sex' => 2],
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

    public function delete_action()
    {
        if (!is_post()) {
            return $this->_error('method 404');
        }

        $uid = input('uid', 0, 'intval');

        if (!$uid) {
            return $this->_error('uid 错误');
        }

        $deleteState = getInstance('test_model')->id($uid)->delete();

        if (!$deleteState) {
            return $this->_error('删除失败');
        }

        return $this->_success('删除成功');
    }


    protected function getCityList()
    {
        return [
            '北京' => '北京',
            '上海' => '上海',
            '广州' => '广州',
            '深圳' => '深圳'
        ];
    }

}
