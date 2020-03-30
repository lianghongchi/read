<?php
/**
 * 首页 控制器
 *
 * @authName    首页
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class index_controller extends controller_lib
{


    /**
     * 后台管理框架模板
     */
    protected $adminFrameworkTemplate = 'layui_template';

    /**
     * 是否加入权限控制
     */
    protected $_isAuthControl = false;


    /**
     * mobile 首页
     */
    public function index_action()
    {
        // 获取菜单列表
        $menuModel = getInstance('menu_menu_model');
        $data = $menuModel->select();
        $menus = $menuModel->getConMenu($data);

        // 检查并设置菜单
        $menus = $this->checkAndSetMenus($menus);
        $tpldata = [
            'menus' => $menus,
            'homeUrl' => $this->_baseUrl . '/index/home',
            'logoutUrl' => $this->_baseUrl . '/login/logout',
        ];

        $this->_assign($tpldata);
        $this->_setTemplate($this->adminFrameworkTemplate);
        $this->_render();
    }

    /**
     * 检查菜单权限
     */
    protected function checkAndSetMenus($menus)
    {
        $authMenus = [];

        // 递归过滤无权访问的菜单
        foreach ($menus as $key => $value) {

            if (is_string($value['controller'])) {
                if ($this->_matchAuth($value['controller'])) {
                    // 设置菜单URL
                    $value['route'] = str_replace('_', '/', $value['controller']) . '/' . $value['action'];
                    $authMenus[] = $value;
                }

            } else if (is_array($value['controller'])) {
                $subMenus = $this->checkAndSetMenus($value['controller']);

                if ($subMenus) {
                    $value['controller'] = $subMenus;
                    $authMenus[] = $value;
                }
            }
        }

        return $authMenus;
    }

    /**
     * 后台管理首页
     */
    public function home_action()
    {

        // 登录日志
        $loginLogModel = getInstance('admin_log_login_model');
        $loginList = $loginLogModel->where('admin_id', $this->_adminid)->limit(10)->order('add_time desc')->select();
        $loginList = $loginLogModel->formatMulti($loginList);

        // 权限分组
        $adminRoleList = $this->_getAdminRoleList();
        $adminRoles = getInstance('admin_role_model')->where('role_id', ['IN', $adminRoleList])->select();
        $tz = getInstance('system_tz_lib');
        $stat = $tz->getStat();

        $tpldata = [
            'headline' => '首页',
            'coreVersion' => KIS_CORE_VERSION,
            'hostname' => $tz->getHostname(),
            'os' => $tz->getOSName(),
            'phpVersion' => PHP_VERSION,
            'cpu' => $tz->getCpuInfo(),
            'disk' => $tz->getDiskinfo(),
            'stat' => $stat ? json_encode($stat) : '',
            'loginList' => $loginList,
            'roles' => $adminRoles,
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    public function get_sysinfo_action()
    {
        $tz = getInstance('system_tz_lib');

        $info = $tz->getSysInfo();

        $this->_assign($info);
        $this->_render();
    }


}
