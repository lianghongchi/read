<?php
/**
 * 菜单操作
 *
 * @authName    菜单操作
 * @note        后台左侧菜单操作 增删改
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-22
 *
 */

class menu_menupost_controller extends controller_lib
{
    /**
     * 添加菜单
     */
    public function add_action()
    {
        if (is_post()) {
            // 添加管理员账号
            $addState = $this->doAddMenu();

            // 添加失败
            if ($addState['code'] != 200) {
                return $this->_error($addState['msg']);
            }

            return $this->_success('菜单添加成功');
        }
        $where['pid'] = 0;
        // 获取权限分组信息
        $menuList = getInstance('menu_menu_model')->where($where)->select();

        $this->_assign('menuList', $menuList);
        $this->_render();
    }

    /**
     * 添加菜单信息
     */
    protected function doAddMenu()
    {
        // 名称
        $title = input('title', '');
        $icon = input('icon', '');
        $controller = input('controller', '');
        $action = input('action', '');
        $pid = input('pid', '');
        $spid = input('spid', '');
        $sort = input('sort', '');

        if (!$title) {
            return return_format(400, '菜单名称不可为空');
        }
        if ($sort == 0 || $sort > 10) {
            return return_format(400, '权重输入错误');
        }
        if ($pid && $spid) {
            $pid = $spid;
        } else if (!$pid) {
            $pid = 0;
        }
        $data = [
            'title' => $title,
            'icon' => $icon,
            'controller' => $controller,
            'action' => $action,
            'pid' => $pid,
            'sort' => $sort,
        ];

        $insertMenuId = getInstance('menu_menu_model')->insert($data);

        if (!$insertMenuId) {
            return return_format(400, '菜单添加失败');
        }
        $menu['id'] = $insertMenuId;

        return return_format(200, '菜单添加成功', $menu);
    }

    /**
     * 获取二级菜单
     */
    public function getReMenu_action()
    {

        // 用户名
        $pid = input('pid', 0);

        $where['pid'] = $pid;

        // 获取权限分组信息
        $menuList = getInstance('menu_menu_model')->where($where)->select();

        $tpldata = [
            'menuList' => $menuList,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 删除菜单
     */
    public function delete_action()
    {

        //id
        $id = input('id', 0);

        //判断数据是否存在
        if ($id == 0) {
            return $this->_error('数据不存在');
        }

        $where['pid'] = $id;
        $menuModel = getInstance('menu_menu_model');
        $menuList = $menuModel->where($where)->select();

        if (!empty($menuList)) {
            return $this->_error('此菜单下有子菜单无法删除');
        }

        $deleteState = $menuModel->id($id)->delete();

        if (!$deleteState) {
            return $this->_error('删除失败');
        }

        return $this->_success('删除成功');

    }

    /**
     * 修改菜单
     */
    public function edit_action()
    {

        //id
        $id = input('id', 0);

        //获取当前菜单信息
        $menuModel = getInstance('menu_menu_model');
        $where['id'] = $id;
        $menuInfo = $menuModel->where($where)->find();
        //获取一级列表
        $where = [];
        $where['pid'] = 0;
        $levelOneMenu = $menuModel->where($where)->select();

        //获取此菜单对应的一级菜单或二级菜单
        if ($menuInfo['pid'] == 0) {
            //一级菜单情况
            $levelOneId = 0;
            $levelTwoId = 0;
            $levelTwoMenu = array();
        } else {
            $where = [];
            $where['id'] = $menuInfo['pid'];
            $infoO = $menuModel->where($where)->find();

            if ($infoO['pid'] == 0) {
                //二级菜单
                $levelOneId = $menuInfo['pid'];
                $levelTwoId = 0;
                $where = [];
                $where['pid'] = $infoO['id'];
                $levelTwoMenu = $menuModel->where($where)->select();
            } else {
                // 三级菜单
                $where = [];
                $where['id'] = $infoO['pid'];
                $infoE = $menuModel->where($where)->find();
                if ($infoE['pid'] == 0) {
                    $levelOneId = $infoE['id'];
                    $levelTwoId = $infoO['id'];
                    $where = [];
                    $where['pid'] = $infoE['id'];
                    $levelTwoMenu = $menuModel->where($where)->select();
                }

            }
        }
        if (is_post()) {
            // 修改菜单
            $addState = $this->doUpdMenu();

            // 修改失败
            if ($addState['code'] != 200) {
                return $this->_error($addState['msg']);
            }

            return $this->_success('菜单修改成功');
        }
        $tpldata = [
            'data' => $menuInfo,
            'levelOneMenu' => $levelOneMenu,
            'levelTwoMenu' => $levelTwoMenu,
            'levelOneId' => $levelOneId,
            'levelTwoId' => $levelTwoId,
        ];
        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 修改菜单
     */
    public function doUpdMenu()
    {
        $id = input('id', 0);
        $title = input('title', '');
        $icon = input('icon', '');
        $controller = input('controller', '');
        $action = input('action', '');
        $pid = input('pid', 0);
        $spid = input('spid', 0);
        $sort = input('sort', 0);

        if ($id == 0) {
            return return_format(400, '数据不存在');
        }
        if (!$title) {
            return return_format(400, '菜单名称不可为空');
        }
        if ($sort == 0 || $sort > 10) {
            return return_format(400, '权重输入错误');
        }

        $where['pid'] = $id;
        $menuModel = getInstance('menu_menu_model');
        $menuList = $menuModel->where($where)->select();
        $where = [];
        $where['id'] = $id;
        $menuOn = $menuModel->where($where)->find();

        if ($pid && $spid) {
            $pid = $spid;
        } else if (!$pid) {
            $pid = 0;
        }

        if (!empty($menuList)) {
            if ($controller != $menuOn['controller'] || $action != $menuOn['action'] || $pid != $menuOn['pid']) {
                return return_format(400, '此菜单下有子菜单无法修改');
            }
        }
        $updateData = [
            'title' => $title,
            'icon' => $icon,
            'controller' => $controller,
            'action' => $action,
            'pid' => $pid,
            'sort' => $sort,
        ];
        $where = [];
        $where['id'] = $id;
        $menuModel = getInstance('menu_menu_model');
        $menuList = $menuModel->where($where)->find();
        // 数据是否变更
        if (array_intersect_key($menuList, $updateData) == $updateData) {
            return return_format(400, '信息无变化，无需保存');
        }

        $updateStat = getInstance('menu_menu_model')->id($id)->update($updateData);

        if (!$updateStat) {
            return return_format(400, '修改失败');
        }

        return return_format(200, '修改成功');

    }


}
