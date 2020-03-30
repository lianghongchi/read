<?php
/**
 * 下级管理员列表
 *
 * @authName    下级管理员列表
 * @note        下级管理员列表
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-24
 *
 */

class admin_adminsubset_controller extends admin_adminpost_controller
{

    /**
     * 是否自定义权限控制
     */
    protected $_isCustomAuthControl = true;

    /**
     * 下级管理员列表
     */
    public function index_action()
    {
        $where = ['p_admin_id' => $this->_adminid];

        // 子管理员ID
        $adminid = input('admin_id', '', 'intval');
        // 姓名
        $name = input('name', '');

        // 手机号
        $cellphone = input('cellphone', '');

        // 注册时间
        $addTime1 = input('add_time1', false);
        $addTimeStart = $addTime1 ? strtotime($addTime1) : '';
        $addTimeStart || $addTime1 = '';

        $addTime2 = input('add_time2', false);
        $addTimeEnd = $addTime2 ? strtotime($addTime2) : '';
        $addTimeEnd || $addTime2 = '';

        $name && $where['name'] = ['like', "%{$name}%"];
        $cellphone && $where['cellphone'] = ['like', "%{$cellphone}%"];

        // 时间区间查询
        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['MORE', [
                ['>=', $addTimeStart, 'AND'],
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
            'adminid' => $adminid,
            'name' => htmlspecialchars($name),
            'cellphone' => htmlspecialchars($cellphone),
            'addTime1' => $addTime1,
            'addTime2' => $addTime2,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 添加子管理员控制器
     */
    public function add_action()
    {
        $haveAuthWhere = [];

        // 非超级管理员可选择自己拥有的权限
        if (!$this->_admin['is_super']) {
            $haveAuthWhere = ['role_id' => ['IN', $this->_getAdminRoleList() ?? []]];
        }

        $roleList = getInstance('admin_role_model')->where($haveAuthWhere)->selectWithUnique('role_id');

        if (is_post()) {
            $addState = $this->doAddAdminAccount();
            if ($addState['code'] != 200) {
                return $this->_error($addState['msg']);
            }
            $insertAdminId = $addState['data']['admin_id'];

            // 添加权限
            $role = input('role', []);

            // 检查选择的权限组是否合法 并添加权限
            if ($role && is_array($role)) {
                $roleIds = [];

                foreach ($role as $key => $value) {
                    $roleId = intval($value);
                    $roleId && $roleIds[$roleId] = 1;
                }
                // 查询出有效权限
                if ($roleIds) {
                    $validRoleList = array_intersect_key($roleList, $roleIds);

                    if ($validRoleList) {
                        $insertRolemapArray = [];
                        $ntime = time();
                        foreach ($validRoleList as $key => $value) {
                            $insertRolemapArray[] = [
                                'role_id' => $value['role_id'],
                                'admin_id' => $insertAdminId,
                                'add_time' => $ntime,
                            ];
                        }
                        getInstance('admin_rolemap_model')->insert($insertRolemapArray);
                    }
                }
            }

            return $this->_success('管理员添加成功');
        }

        $this->_assign('roleList', $roleList);
        $this->_render();
    }

    /**
     * 编辑子管理员控制器
     */
    public function edit_action()
    {
        // 获取管理员信息
        $adminid = input('admin_id', 0, 'intval');
        $adminModel = getInstance('admin_admin_model');
        $admin = $adminModel->id($adminid)->where('p_admin_id', $this->_adminid)->find();

        if (!$admin) {
            return $this->_error('此管理员不存在');
        }

        // 获取管理员权限分组信息
        $haveAuthWhere = [];

        // 非超级管理员可选择自己拥有的权限
        if (!$this->_admin['is_super']) {
            $haveAuthWhere = ['role_id' => ['IN', $this->_getAdminRoleList() ?? []]];
        }

        $roleList = getInstance('admin_role_model')->where($haveAuthWhere)->selectWithUnique('role_id');

        if (is_post()) {
            $upState = $this->doEditAdminAccount($admin);
            $upCode = $upState['code'];

            if ($upCode != 200 && $upCode != 201) {
                return $this->_error($upState['msg']);
            }

            // 选择的权限
            $role = input('role', []);

            // 检查选择的权限组
            $isChangeRolemap = true;
            $roleModel = getInstance('admin_role_model');
            $rolemapModel = getInstance('admin_rolemap_model');

            // 检查选择的权限组是否合法 并添加权限
            if ($role && is_array($role)) {
                $roleIds = [];

                foreach ($role as $key => $value) {
                    $roleId = intval($value);
                    $roleId && $roleIds[] = $roleId;
                }
                if ($roleIds) {
                    $validRoleList = $roleModel->where('role_id', ['IN', $roleIds])->selectWithUnique('role_id');
                    $selectRolemapList = $rolemapModel->field('admin_id,role_id')->where('admin_id', $adminid)->selectWithUnique('role_id');

                    // 编辑此管理员提交的权限组是否发生变化
                    if (array_intersect_key($selectRolemapList, $roleList) == $selectRolemapList && count($selectRolemapList) == count($roleList)) {
                        $isChangeRolemap = false;
                    }
                }
            }

            // 数据无变化，不操作数据库
            if (!$isChangeRolemap && $upCode == 201) {
                return $this->_success('信息无变化，无需保存');
            }

            // 如果权限已变更、更新管理员权限
            if ($isChangeRolemap) {
                // 删除历史权限
                $rolemapModel->where('admin_id', $adminid)->delete();
                // 插入已选择的权限
                if (!empty($validRoleList)) {
                    $insertRolemapArray = [];
                    $ntime = time();

                    foreach ($validRoleList as $key => $value) {
                        $insertRolemapArray[] = [
                            'role_id' => $value['role_id'],
                            'admin_id' => $adminid,
                            'add_time' => $ntime,
                        ];
                    }

                    $rolemapModel->insert($insertRolemapArray);
                }
            }

            return $this->_success('保存成功');
        }

        // 所能赋予的权限分组
        $admin['role_list'] = $adminModel->getRolemap($adminid);

        $this->_assign([
            'admin' => $admin,
            'roleList' => $roleList,
        ]);

        $this->_render();
    }

    /**
     * 递归检查传入的管理员是否是当前登录管理员的下级
     */
    protected function checkIsSubsetAdmin($checkPAdminId)
    {
        if (!$checkPAdminId) {
            return false;
        }

        $adminModel = getInstance('admin_admin_model');

        $findAdmin = $adminModel->id($checkPAdminId)->field('admin_id,p_admin_id')->find();

        if (!$findAdmin || !$findAdmin['p_admin_id']) {
            return false;
        }
        if ($findAdmin['p_admin_id'] == $this->_adminid) {
            return true;
        }

        return $this->checkIsSubsetAdmin($findAdmin['p_admin_id']);
    }

    /**
     * 下级管理员列表
     */
    public function subset_action()
    {
        // 子管理员ID
        $pAdminId = input('p_admin_id', '', 'intval');

        // 是否属于已登录管理员的子集
        $checkState = $this->checkIsSubsetAdmin($pAdminId);
        if (!$checkState) {
            return $this->_error('查询的管理员不是当前登录管理员的下级');
        }

        $where = ['p_admin_id' => $pAdminId];

        // 管理员ID
        $adminid = input('admin_id', '', 'intval');
        // 姓名
        $name = input('name', '');
        // 手机号
        $cellphone = input('cellphone', '');

        // 注册时间
        $addTime1 = input('add_time1', false);
        $addTimeStart = $addTime1 ? strtotime($addTime1) : '';
        $addTimeStart || $addTime1 = '';

        $addTime2 = input('add_time2', false);
        $addTimeEnd = $addTime2 ? strtotime($addTime2) : '';
        $addTimeEnd || $addTime2 = '';

        $adminid && $where['admin_id'] = $adminid;
        $name && $where['name'] = ['like', "%{$name}%"];
        $cellphone && $where['cellphone'] = ['like', "%{$cellphone}%"];
        // 时间区间查询
        if ($addTimeStart && $addTimeEnd) {
            $where['add_time'] = ['more', [
                ['>=', $addTimeStart, 'and'],
                ['<=', $addTimeEnd + 86400],
            ]
            ];
        }

        $adminModel = getInstance('admin_admin_model');

        $page = $this->_getPageData();

        $data = $adminModel->where($where)->limit($page['start'], $page['pageRows'])->order('admin_id desc')->select();
        $data = $adminModel->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'adminid' => $adminid,
            'pAdminId' => $pAdminId,
            'name' => htmlspecialchars($name),
            'cellphone' => htmlspecialchars($cellphone),
            'addTime1' => $addTime1,
            'addTime2' => $addTime2,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 自定义权限控制
     */
    public function _customAuthControl($admin): bool
    {
        // 仅拥有 下级管理权限 才能访问
        if (!empty($admin['is_sub_manager'])) {
            return true;
        }

        return false;
    }


}
