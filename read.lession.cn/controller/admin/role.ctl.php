<?php

/**
 * 权限分组列表
 *
 * @authName    权限分组列表
 * @note        权限分组的展示
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-22
 *
 */
class admin_role_controller extends controller_lib
{
    /**
     * 权限分组列表
     */
    public function index_action()
    {
        // 姓名
        $name = input('name', '');
        $where = [];

        $name && $where['name'] = ['like', "%{$name}%"];

        $model = getInstance('admin_role_model');

        $page = $this->_getPageData();

        $data = $model->where($where)->limit($page['start'], $page['pageRows'])->order('role_id desc')->selectWithUnique('role_id');
        $data = $model->formatMulti($data);

        // 查询权限分组对应拥有的权限
        $roleIds = [];
        foreach ($data as $key => &$value) {
            $roleIds[] = $value['role_id'];
            $value['auth_list'] = [];
        }
        unset($value);

        // 查询出权限分组所拥有的权限
        if ($data) {
            $roleauthModel = getInstance('admin_roleauth_model');
            $authModel = getInstance('admin_auth_model');

            $sql = "SELECT ar.id,ar.role_id,ar.controller,a.auth_name FROM {$roleauthModel->getTableFullName()} as ar LEFT JOIN {$authModel->getTableFullName()} as a ON ar.controller = a.controller WHERE ar.role_id IN (" . implode(",", $roleIds) . ")";

            $authList = $roleauthModel->queryWithNoHash($sql);

            if (is_array($authList)) {
                foreach ($authList as $key => $value) {
                    if (isset($data[$value['role_id']]['auth_list'])) {
                        $data[$value['role_id']]['auth_list'][] = $value;
                    }
                }
            }
        }

        $tpldata = [
            'data' => $data,
            'name' => htmlspecialchars($name),
        ];

        $this->_assign($tpldata);
        $this->_render();
    }


}
