<?php

/**
 * 权限分组操作
 *
 * @authName    权限分组操作
 * @note        对权限分组的操作 增删改
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-22
 *
 */
class admin_rolepost_controller extends controller_lib
{


    /**
     * 添加权限分组
     */
    public function add_action()
    {
        // 获取权限列表
        $authModel = getInstance('admin_auth_model');
        $authList = $authModel->selectWithUnique('controller');
        $tagAuthList = $authModel->formatWithTag($authList);

        if (is_post()) {
            // 角色名称
            $name = input('name', '', 'trim');
            // 勾选的权限
            $authArray = input('auth', []);

            // 检查参数
            $nameReg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\[\]\{\}\(\)\（\）]+$/u";

            if (!preg_match($nameReg, $name)) {
                return $this->_error('名称只能由中文、数字、字母、[]、{}、（）、()等字符组成');
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 20) {
                return $this->_error('请输入2-20个字的分组名称');
            }

            // 检查勾选的权限是否合法
            if ($authArray && is_array($authArray) && array_intersect_key($authArray, $authList) != $authArray) {
                return $this->_error('选择的权限不匹配，请刷新页面后重试');
            }

            // 检查用户名是否被注册
            $findAdmin = getInstance('admin_role_model')->where('name', $name)->find();

            if ($findAdmin) {
                return $this->_error('此分组名称已存在');
            }

            $data = [
                'name' => $name,
                'add_time' => time()
            ];

            $insertRoleId = getInstance('admin_role_model')->insert($data);

            if (!$insertRoleId) {
                return $this->_error('权限分组添加失败');
            }

            if (!$authArray) {
                return $this->_success('权限分组添加成功，注意：此分组尚未勾选权限！');
            }

            // 添加分组权限
            if ($authArray && is_array($authArray)) {
                foreach ($authArray as $key => $value) {
                    $insertAuthList[] = [
                        'role_id' => $insertRoleId,
                        'controller' => $key,
                        'is_open' => $authList[$key]['is_open'] ?? 1,
                    ];
                }
            }
            getInstance('admin_roleauth_model')->insert($insertAuthList);

            return $this->_success('权限分组添加成功');
        }

        $this->_assign('tagAuthList', $tagAuthList);
        $this->_render();
    }

    /**
     * 编辑权限分组
     */
    public function edit_action()
    {
        $roleid = input('role_id', 0, 'intval');

        $model = getInstance('admin_role_model');
        $authModel = getInstance('admin_auth_model');
        $roleauthModel = getInstance('admin_roleauth_model');
        // 所有权限
        $authList = $authModel->selectWithUnique('controller');
        $tagAuthList = $authModel->formatWithTag($authList);

        // 分组信息
        $data = $model->id($roleid)->find();

        // 分组所拥有的权限
        $myAuthList = $roleauthModel->field('id,role_id,controller')->where('role_id', $roleid)->selectWithUnique('controller');

        if (!$data) {
            return $this->_error('角色不存在');
        }

        if (is_post()) {
            // 角色名称
            $name = input('name', '', 'trim');
            // 勾选的权限
            $authArray = input('auth', []);

            // 检查参数
            $nameReg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\[\]\{\}\(\)\（、）]+$/u";

            if (!preg_match($nameReg, $name)) {
                return $this->_error('名称只能由中文、数字、字母、[]、{}、（）、()等字符组成');
            }

            if (mb_strlen($name) < 2 || mb_strlen($name) > 20) {
                return $this->_error('请输入2-20位的角色名称');
            }

            // 检查勾选的权限是否合法
            if ($authArray && is_array($authArray) && array_intersect_key($authArray, $authList) != $authArray) {
                return $this->_error('选择的权限不匹配，请刷新页面后重试');
            }

            // 权限是否被修改
            $isChangeAuth = false;
            if (array_diff_key($myAuthList, $authArray) || array_diff_key($authArray, $myAuthList)) {
                $isChangeAuth = true;
            }

            if (!$isChangeAuth && $name == $data['name']) {
                return $this->_error('信息无变化，无需保存');
            }

            if ($name != $data['name']) {
                $upState = $model->id($roleid)->update('name', $name);

                if (!$upState) {
                    return $this->_error('保存失败');
                }
            }

            // 修改权限
            if ($authArray && is_array($authArray)) {
                foreach ($authArray as $key => $value) {
                    $insertAuthList[] = [
                        'role_id' => $roleid,
                        'controller' => $key,
                        'is_open' => $authList[$key]['is_open'] ?? 1,
                    ];
                }
            }

            if ($myAuthList) {
                $deleteState = $roleauthModel->where('role_id', $roleid)->delete();
            }

            if (!empty($insertAuthList)) {
                $roleauthModel->insert($insertAuthList);
            }

            return $this->_success('保存成功');
        }

        $tpldata = [
            'tagAuthList' => $tagAuthList,
            'myAuthList' => $myAuthList,
            'data' => $data,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 删除权限分组
     */
    public function delete_action()
    {
        if (!is_post()) {
            return;
        }
        $roleid = input('role_id', 0, 'intval');

        $model = getInstance('admin_role_model');

        if (!$roleid) {
            return $this->_error('分组ID错误');
        }

        $deleteState = $model->id($roleid)->delete();

        if (!$deleteState) {
            return $this->_error('删除失败');
        }

        // 删除分组 && 权限 关系
        getInstance('admin_roleauth_model')->where('role_id', $roleid)->delete();
        // 删除管理员 && 权限分组 关系
        getInstance('admin_rolemap_model')->where('role_id', $roleid)->delete();

        return $this->_success('删除成功');
    }

}
