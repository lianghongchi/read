<?php

/**
 * 管理员操作
 *
 * @authName    管理员操作
 * @note        对管理员的基本操作 增加 修改
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-22
 *
 */
class admin_adminpost_controller extends controller_lib
{
    /**
     * 添加管理员
     */
    public function add_action()
    {
        if (is_post()) {
            // 添加管理员账号
            $addState = $this->doAddAdminAccount();

            // 添加失败
            if ($addState['code'] != 200) {
                return $this->_error($addState['msg']);
            }

            // 管理员ID
            $insertAdminId = $addState['data']['admin_id'];

            // 设置的权限
            $role = input('role', []);

            // 添加权限
            if ($role && is_array($role)) {
                $roleIds = [];

                foreach ($role as $key => $value) {
                    $roleId = intval($value);
                    $roleId && $roleIds[] = $roleId;
                }
                // 检查选择的权限组是否合法 并添加权限组
                if ($roleIds) {
                    $validRoleList = getInstance('admin_role_model')->where('role_id', ['IN', $roleIds])->selectWithUnique('role_id');
                    $ntime = time();

                    if ($validRoleList) {
                        $insertRolemapArray = [];
                        foreach ($validRoleList as $key => $value) {
                            $insertRolemapArray[] = [
                                'role_id' => $value['role_id'],
                                'admin_id' => $insertAdminId,
                                'add_time' => $ntime,
                            ];
                        }
                        // 插入权限
                        getInstance('admin_rolemap_model')->insert($insertRolemapArray);
                    }
                }
            }

            return $this->_success('管理员添加成功');
        }

        // 获取权限分组信息
        $roleList = getInstance('admin_role_model')->select();

        $this->_assign('roleList', $roleList);
        $this->_render();
    }

    /**
     * 添加管理员信息
     */
    protected function doAddAdminAccount()
    {
        // 用户名
        $adminName = input('admin_name', '', 'trim');
        // 真实姓名
        $name = input('name', '', 'trim');
        // 手机号
        $cellphone = input('cellphone', '', 'trim');
        // 登录密码
        $password = input('password', '');
        // 开启下级管理权限
        $isSubManager = input('is_sub_manager', 0, 'intval');
        $isSubManager = $isSubManager ? 1 : 0;
        // 开启GA登录
        $isLogingGa = input('is_login_ga', 0, 'intval');
        $isLogingGa = $isLogingGa ? 1 : 0;
        // 职位
        $adminTitle = input('admin_title', '');

        // 检查参数
        if (!ctype_alnum($adminName)) {
            return return_format(400, '用户名仅支持字母和(或者)数字');
        }

        if (strlen($adminName) < 2 || strlen($adminName) > 16) {
            return return_format(400, '请输入2-16位的用户名');
        }

        if (!is_cellphone($cellphone)) {
            return return_format(400, '请输入正确的手机号码');
        }

        if (!ctype_graph($password)) {
            return return_format(400, '设置的登录密码不能包含空格');
        }

        if (strlen($password) < 6 || strlen($password) > 16) {
            return return_format(400, '请输入6-16位的密码');
        }

        $nameReg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\_]+$/u";

        if (!preg_match($nameReg, $name)) {
            return return_format(400, '姓名只能由中文、数字、字母、下划线组成');
        }

        if (mb_strlen($name) < 2 || mb_strlen($name) > 16) {
            return return_format(400, '请输入2-16位的真实姓名');
        }
        //如果填写了职位
        if ($adminTitle) {
            if (!preg_match($nameReg, $adminTitle)) {
                return return_format(400, '职位只能由中文、数字、字母组成');
            }

            if (mb_strlen($adminTitle) < 2 || mb_strlen($adminTitle) > 15) {
                return return_format(400, '请输入2-15位的职位名称');
            }
        }

        // 检查用户名是否被注册
        $findAdmin = getInstance('admin_admin_model')->field('admin_id,admin_name')->where('admin_name', $adminName)->find();

        if ($findAdmin) {
            return return_format(400, '此管理员用户名已存在');
        }

        // 创建登录密码
        $loginPassword = auth_lib::createPasswordString($password);

        // 如果开启GA登录，创建GA 密钥
        if ($isLogingGa) {
            $authKey = getInstance('ga_lib')->createSecret();
        }

        $admin = [
            'admin_name' => $adminName,
            'name' => $name,
            'p_admin_id' => $this->_adminid,
            'password' => $loginPassword[0],
            'salt' => $loginPassword[1],
            'admin_title' => $adminTitle,
            'is_sub_manager' => $isSubManager,
            'is_login_ga' => $isLogingGa,
            'auth_key' => empty($authKey) ? '' : $authKey,
            'cellphone' => $cellphone,
            'add_time' => time()
        ];

        $insertAdminId = getInstance('admin_admin_model')->insert($admin);

        if (!$insertAdminId) {
            return return_format(400, '管理员添加失败');
        }
        $admin['admin_id'] = $insertAdminId;

        return return_format(200, '管理员添加成功', $admin);
    }

    /**
     * 编辑管理员
     */
    public function edit_action()
    {
        // 获取管理员信息
        $adminid = input('admin_id', 0, 'intval');
        $adminModel = getInstance('admin_admin_model');
        $admin = $adminModel->id($adminid)->find();

        if (!$admin) {
            return $this->_error('此管理员不存在');
        }
        // 选择的权限分组
        $role = input('role', []);

        // 获取权限分组信息
        $roleList = getInstance('admin_role_model')->select();

        if (is_post()) {
            $upState = $this->doEditAdminAccount($admin);
            $upCode = $upState['code'];

            if ($upCode != 200 && $upCode != 201) {
                return $this->_error($upState['msg']);
            }

            // 检查选择的权限组
            $isChangeRolemap = true;
            $roleModel = getInstance('admin_role_model');
            $rolemapModel = getInstance('admin_rolemap_model');

            // 检查选择的权限组是否合法 并添加权限组
            if ($role && is_array($role)) {
                $roleIds = [];

                foreach ($role as $key => $value) {
                    $roleId = intval($value);
                    $roleId && $roleIds[] = $roleId;
                }
                if ($roleIds) {
                    $validRoleList = $roleModel->where('role_id', ['IN', $roleIds])->selectWithUnique('role_id');
                    $selectRolemapList = $rolemapModel->field('admin_id,role_id')->where('admin_id', $adminid)->selectWithUnique('role_id');

                    // 权限组是否发生变化
                    if (array_intersect_key($selectRolemapList, $roleList) == $selectRolemapList && count($selectRolemapList) == count($roleList)) {
                        $isChangeRolemap = false;
                    }
                }
            }

            // 信息无变化，不操作数据库
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

        // 所拥有的分组权限
        $admin['role_list'] = $adminModel->getRolemap($adminid);

        $this->_assign([
            'admin' => $admin,
            'roleList' => $roleList,
        ]);
        $this->_render();
    }


    /**
     * 编辑用户信息
     */
    protected function doEditAdminAccount($admin)
    {
        $adminModel = getInstance('admin_admin_model');
        // 用户名
        $adminName = input('admin_name', '', 'trim');
        // 真实姓名
        $name = input('name', '', 'trim');
        // 手机号
        $cellphone = input('cellphone', '', 'trim');
        // 登录密码
        $password = input('password', '');
        // 开启下级管理权限
        $isSubManager = input('is_sub_manager', 0, 'intval');
        $isSubManager = $isSubManager ? 1 : 0;
        // 开启GA登录
        $isLogingGa = input('is_login_ga', 0, 'intval');
        $isLogingGa = $isLogingGa ? 1 : 0;
        // 是否禁用管理员
        $isLocked = input('is_locked', 1);
        $isLocked = $isLocked ? 1 : 0;
        // 职位
        $adminTitle = input('admin_title', '');

        // 检查参数
        if (!ctype_alnum($adminName)) {
            return $this->_error('用户名仅支持字母和(或者)数字');
        }

        if (strlen($adminName) < 2 || strlen($adminName) > 16) {
            return return_format(400, '请输入2-16位的用户名');
        }

        if (!is_cellphone($cellphone)) {
            return return_format(400, '请输入正确的手机号码');
        }

        // 重新设置登录密码
        if ($password) {
            if (!ctype_graph($password)) {
                return return_format(400, '设置的登录密码不能包含空格');
            }

            if (strlen($password) < 6 || strlen($password) > 16) {
                return return_format(400, '请输入6-16位的密码');
            }
            // 创建登录密码
            $loginPassword = auth_lib::createPasswordString($password);
        }

        $nameReg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\_]+$/u";

        if (!preg_match($nameReg, $name)) {
            return return_format(400, '姓名只能由中文、数字、字母、下划线组成');
        }

        if (mb_strlen($name) < 2 || mb_strlen($name) > 16) {
            return return_format(400, '请输入2-16位的真实姓名');
        }
        // 如果填写了职位
        if ($adminTitle) {
            if (!preg_match($nameReg, $adminTitle)) {
                return return_format(400, '职位只能由中文、数字、字母、下划线组成');
            }
            if (mb_strlen($adminTitle) < 2 || mb_strlen($adminTitle) > 15) {
                return return_format(400, '请输入2-15位的职位名称');
            }
        }

        if ($adminName != $admin['admin_name']) {
            // 检查用户名是否被注册
            $findAdmin = $adminModel->where('admin_name', $adminName)->field('admin_id,admin_name')->find();

            if ($findAdmin) {
                return return_format(400, '此管理员用户名已存在');
            }
        }

        // GA 密钥
        if ($isLogingGa && !$admin['is_login_ga']) {
            $authKey = getInstance('ga_lib')->createSecret();
            if (!$authKey) {
                return return_format(400, 'GA 密钥创建失败');
            }
        }

        $updateAdmin = [
            'admin_name' => $adminName,
            'name' => $name,
            'cellphone' => $cellphone,
            'admin_title' => $adminTitle,
            'is_sub_manager' => $isSubManager,
            'is_login_ga' => $isLogingGa,
            'is_locked' => $isLocked,
        ];

        if (!empty($loginPassword)) {
            $updateAdmin['password'] = $loginPassword[0];
            $updateAdmin['salt'] = $loginPassword[1];
        }

        if (!empty($authKey)) {
            $updateAdmin['auth_key'] = $authKey;
        }
        // 数据是否变更
        if (array_intersect_key($admin, $updateAdmin) != $updateAdmin) {
            $upState = $adminModel->id($admin['admin_id'])->update($updateAdmin);

            if ($upState === false) {
                return return_format(400, '保存失败');
            }

            return return_format(200, '保存成功');
        }

        return return_format(201, '信息无变化，无需保存');
    }

    /**
     * 获取ga以及QR
     */
    public function getga_action()
    {
        $ga = input('ga', '');

        $admin_name = input('admin_name', '');

        $aa = getInstance('ga_lib')->getQRCodeGoogleUrl($admin_name, $ga);
        return $aa;
    }

    /**
     * 去查看ga以及QR
     */
    public function catga_action()
    {
        $ga = input('ga', '');
        $admin_name = input('admin_name', '');

        $template = array(
            'ga' => $ga,
            'admin_name' => $admin_name,
        );
        $this->_assign($template);
        $this->_render();

    }
}
