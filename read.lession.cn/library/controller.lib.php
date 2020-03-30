<?php
/**
 * 管理后台控制器基类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-13
 *
 */

class controller_lib extends lib_app_controller
{


    /**
     * 管理员ID
     * @var var
     */
    protected $_adminid = 0;

    /**
     * 管理员基本信息
     * @var array
     */
    protected $_admin = [];

    /**
     * 后台管理域名URL
     * @var string
     */
    protected $_baseUrl = '';

    /**
     * 此控制器是否加入权限控制
     * 使用自定义权限后，$_isAuthControl 参数将失效
     * @var bool
     */
    protected $_isAuthControl = true;

    /**
     * 是否使用自定义权限控制
     * 使用自定义权限后，$_isAuthControl 参数将失效
     * @var bool
     */
    protected $_isCustomAuthControl = false;

    /**
     * 当前操作是否需要登录
     * 非强制登录的控制器无法使用权限控制
     * @var bool
     */
    protected $_isLoginRequired = true;

    /**
     * 管理员拥有的权限分组
     * @var array
     */
    protected $_adminRoleList = null;

    /**
     * 管理员拥有的权限
     * @var array
     */
    protected $_adminAuthList = null;

    /**
     * 是否记录操作日志
     * 非 get 请求的操作将记录日志
     * @var bool
     */
    protected $_isRecordActionLog = true;


    /**
     * 构造方法
     * @return
     */
    public function __construct()
    {
        parent::__construct();

        // 获取管理员信息
        $admin = auth_lib::getAdminForToken();

        if ($admin) {
            $this->_admin = $admin;
            $this->_adminid = $admin['admin_id'];
        }

        if (is_get() && !$this->_req->isJson()) {
            $this->_loadHtmlConfig();

            $this->_assign([
                '_admin' => $this->_admin,
                '_adminid' => $this->_adminid,
            ]);
        }
    }

    /**
     * 静态资源配置
     * @return
     */
    protected function _loadHtmlConfig()
    {
        $appsite = config::get('appsite', false);
        $this->_baseUrl = $appsite['admin'];

        $config = [
            '_resource' => config::get('resource', false),
            '_appsite' => $appsite,
            '_baseUrl' => $this->_baseUrl,
        ];

        $this->_assign($config);
    }

    /**
     * 登录跳转
     * @return
     */
    public function _goLogin()
    {
        $this->_redirect(1001, '该操作需要登录', $this->_baseUrl . '/login/index');
    }

    /**
     * 检查权限
     * 由 actionBefore 触发，最好不要主动调用
     * @return bool 是否通过
     */
    protected function _checkAuth()
    {
        // 控制器不需要登录
        if (!$this->_isLoginRequired) {
            return true;
        }

        // 检查是否登录
        if (!$this->_adminid) {
            $this->_goLogin();
            return false;
        }

        // 检查操作权限
        $matchState = $this->_matchAuth($this->_controller);

        if ($matchState) {
            return true;
        }

        $this->_error('抱歉，此功能暂无访问权限');
        return false;
    }

    /**
     * 自定义权限控制
     * @param  array $admin 管理员信息
     * @return bool
     */
    public function _customAuthControl($admin): bool
    {
        return false;
    }

    /**
     * 根据控制器名称匹配权限
     * 可在任意位置主动调用检查权限
     * @param  string $controller 控制器名称
     * @return bool
     */
    protected function _matchAuth($controller)
    {
        $controllerClass = $controller . '_controller';

        // 是否超级管理员
        if ($this->_admin['is_super']) {
            return true;
        }

        // // 如果使用自定义权限，系统控制的权限将无效
        // 匹配的控制器名称是当前控制器实例
        if ($controller == $this->_controller) {
            // 自定义权限控制
            if ($this->_isCustomAuthControl) {
                return $this->_customAuthControl($this->_admin);
            }

            if (!$this->_isAuthControl) {
                return true;
            }
            // 其它控制器
        } else {
            $ref = new \ReflectionClass($controllerClass);
            $properties = $ref->getDefaultProperties();
            // 自定义权限控制
            if (!empty($properties['_isCustomAuthControl'])) {
                // 新建的 $controllerClass 实例不会再次执行 检查权限的方法(_matchAuth)，因为 检查权限的方法由 core 在路由时执行的 _actionBefore触发
                return (new $controllerClass())->_customAuthControl($this->_admin);
            }

            if (empty($properties['_isAuthControl'])) {
                return true;
            }
        }

        // 拥有的权限分组
        $adminRoleList = $this->_getAdminRoleList();
        if (!$adminRoleList) {
            return false;
        }

        // 拥有的权限
        $adminAuthList = $this->_getAdminAuthList();
        if (!$adminAuthList) {
            return false;
        }

        // 判断权限是否匹配
        foreach ($adminAuthList as $key => $value) {
            if ($value['controller'] == $controller && $value['is_open']) {
                return true;
            }
        }

        return false;
    }

    /**
     * 获取管理员权限分组列表
     * @return
     */
    protected function _getAdminRoleList()
    {
        if (is_null($this->_adminRoleList)) {
            $this->_adminRoleList = getInstance('admin_rolemap_model')->where('admin_id', $this->_adminid)->select();
        }
        return $this->_adminRoleList;
    }

    /**
     * 获取管理员权限列表
     * @return
     */
    protected function _getAdminAuthList()
    {
        if (is_null($this->_adminAuthList)) {
            $this->_adminAuthList = getInstance('admin_roleauth_model')->where('role_id', ['IN', $this->_getAdminRoleList()])->select();
        }
        return $this->_adminAuthList;
    }


    /**
     * 获取分配配置
     * @param  integer $dataSize 每页数据数量，不填写获取get参数pagerows的值
     * @param  integer $count 总数据量
     * @return array
     */
    protected function _getPageData($pageRows = 0, $totalRows = 0)
    {
        $page = input('page', 1, 'intval');

        if ($page < 1) {
            $page = 1;
        }

        $pageRows || $pageRows = input('pagerows', 0);
        $pageRows < 1 && $pageRows = 30;
        $pageRows > 200 && $pageRows = 200;

        $pageData = [
            'page' => $page,
            'start' => ($page - 1) * $pageRows,
            'pageRows' => $pageRows,
            'totalRows' => $totalRows,
            'totalPage' => floor(($totalRows + $pageRows - 1) / $pageRows),
        ];

        $this->_assign('_page', $pageData);

        return $pageData;
    }

    /**
     * 系统方法，由 core controller执行的操作前置方法，若返回false 将不再执行action，用来检查权限
     * @return bool
     */
    public function __actionBefore()
    {
        if (!$this->_checkAuth()) {
            return false;
        }
    }


    /**
     * 系统方法，由 core controller执行的操作后置方法，当前用来记录操作日志
     * @return
     */
    public function __actionAfter()
    {
        // 管理员操作记录日志
        if ($this->_isAuthControl && $this->_isRecordActionLog && !is_get()) {
            $this->_doAddActionLog();
        }
    }

    /**
     * 记录操作日志
     * @return
     */
    protected function _doAddActionLog()
    {
        // 获取当前操作URL 安全过滤
        $url = $this->_req->url();
        $url = remove_xss($url);
        if (strlen($url) > 254) {
            $url = substr($url, 0, 254);
        }

        // $_REQUEST 请求参数
        $request = $this->_req->input();
        // $_REQUEST 请求参数安全过滤
        array_walk_recursive($request, function (&$value, $key) {
            $value = strlen($value) > 200 ? '' : remove_xss($value);
        });

        if ($request) {
            $request = json_encode($request);

            if (strlen($request) > 1000) {
                $request = substr($request, 0, 1000);
            }
        }

        // 获取agent 与 安全过滤
        $agent = $this->_req->agent();
        $agent = remove_xss($agent);

        if (strlen($agent) > 254) {
            $agent = substr($agent, 0, 254);
        }

        // 操作结果
        $renderResult = $this->_renderResult;

        array_walk_recursive($renderResult, function (&$value, $key) {
            $value = strlen($value) > 200 ? '' : remove_xss($value);
        });

        if ($renderResult) {
            $renderResult = json_encode($renderResult);
        }

        // 操作日志
        $log = [
            'admin_id' => $this->_adminid,
            'controller' => $this->_controller,
            'method' => $this->_req->method(),
            'action' => $this->_action,
            'url' => $url,
            'request' => $request ?: '',
            'agent' => $agent ?: '',
            'result' => $renderResult ?: '',
            'ip' => $this->_req->ip(),
            'add_time' => get_time(),
        ];

        getInstance('admin_log_action_model')->insert($log);
    }

}
