<?php

/**
 * 权限操作
 *
 * @authName    权限操作
 * @note        对后台权限的操作
 * @package     KIS
 * @author      marsked <marsked@163.com>
 * @since       2019-03-22
 *
 */
class admin_auth_controller extends controller_lib
{

    /**
     * 控制器后缀名称
     */
    protected $classSuffix = '.ctl.php';

    /**
     * 控制器根目录
     */
    protected $classBasePath = '';


    /**
     * 权限列表
     */
    public function index_action()
    {
        // 权限名称
        $authName = input('auth_name', '');

        $where = [];
        $authName && $where['auth_name'] = ['LIKE', "%{$authName}%"];

        $authModel = getInstance('admin_auth_model');
        $data = $authModel->where($where)->limit(500)->order('id desc')->select();
        $data = $authModel->formatMulti($data);

        $tpldata = [
            'data' => $data,
            'authName' => htmlspecialchars($authName),
        ];

        $this->_assign($tpldata);
        $this->_render();
    }

    /**
     * 更新权限
     */
    public function update_action()
    {
        if (!is_post()) {
            return;
        }

        $result = $this->doUpdateAuth();

        $this->_success('权限更新成功');
    }

    /**
     * 重读权限
     */
    public function flush_action()
    {
        if (!is_post()) {
            return;
        }

        $result = $this->doFlushAuth();

        $this->_success('权限重读成功');
    }

    /**
     * 修改权限
     */
    protected function doUpdateAuth()
    {
        // 扫描最新控制器
        $this->classBasePath = KIS_APPLICATION_ROOT . 'controller/';

        $sweepList = $this->getClassList($this->classBasePath);

        $authModel = getInstance('admin_auth_model');
        $roleauthModel = getInstance('admin_roleauth_model');

        // 获取历史权限列表
        $selectList = $authModel->field('id,auth_name,controller')->selectWithUnique('controller');

        // 获取 最新权限列表 & 历史权限列表 交集
        $intersectArray = array_intersect_key($selectList, $sweepList);

        // 删除不存在的权限
        // 获取需要删除的权限及 对应权限分组所拥有的权限
        $deleteArray = array_diff_key($selectList, $intersectArray);

        if (!empty($deleteAuthIds)) {
            $authModel->where('id', ['IN', $deleteArray])->delete();
            $roleauthModel->where('controller', ['IN', $deleteArray])->delete();
        }

        // 插入新增的权限
        $ntime = time();
        // 获取需要新增的权限
        $insertArray = array_diff_key($sweepList, $intersectArray);

        foreach ($insertArray as $key => $value) {
            $value['add_time'] = $ntime;
            $insertAuthList[] = $value;
        }

        if (!empty($insertAuthList)) {
            $authModel->insert($insertAuthList);
        }

        return true;
    }

    /**
     * 更新权限
     */
    protected function doFlushAuth()
    {
        // 扫描最新控制器
        $this->classBasePath = KIS_APPLICATION_ROOT . 'controller/';

        $sweepList = $this->getClassList($this->classBasePath);

        // 删除原权限列表
        $authModel = getInstance('admin_auth_model');
        $roleauthModel = getInstance('admin_roleauth_model');

        // 获取历史权限列表
        $selectList = $authModel->field('id,auth_name,controller')->selectWithUnique('controller');

        // 获取 最新权限列表 & 历史权限列表 交集
        $intersectArray = array_intersect_key($selectList, $sweepList);

        // 删除不存在的权限
        // 获取需要删除的权限及 对应权限分组所拥有的权限
        $deleteArray = array_diff_key($selectList, $intersectArray);

        // 删除原保存的权限
        $authModel->where('id', ['IN', $selectList])->delete();

        // 删除权限分组 映射的权限
        if (!empty($deleteAuthIds)) {
            $roleauthModel->where('controller', ['IN', $deleteArray])->delete();
        }

        // 插入最新权限列表
        $ntime = time();

        foreach ($sweepList as $key => $value) {
            $value['add_time'] = $ntime;
            $insertAuthList[] = $value;
        }

        if (!empty($insertAuthList)) {
            $authModel->insert($insertAuthList);
        }

        return true;
    }

    /**
     * 获取控制器类列表
     */
    protected function getClassList($path)
    {
        if (!is_dir($path)) {
            return false;
        }

        $dh = opendir($path);
        $classList = [];

        if (!$dh) {
            throw new \Exception("Class Dir Open Fail", 1);
        }

        // 循环读取目录文件
        while (($value = readdir($dh)) !== false) {
            if ($value == '.' || $value == '..') {
                continue;
            }
            if (!ctype_alpha(substr($value, 0, 1))) {
                continue;
            }

            $file = $path . $value;

            // 如果是目录，递归解析
            if (is_dir($file . '/')) {
                $classList = array_merge($classList, $this->getClassList($file . '/') ?: []);

            } else if (stripos($value, $this->classSuffix) !== false && is_file($file)) {
                // 获取控制器名称
                $classPrePath = substr($path, strlen($this->classBasePath));
                $classLastName = substr($value, 0, strlen($value) - strlen($this->classSuffix));

                $controllerName = str_replace('/', '_', $classPrePath) . $classLastName;

                // 获取控制器权限信息
                if (include_once $file) {
                    $detail = $this->getControllerAuthContent($controllerName);
                    $detail && $classList[$detail['controller']] = $detail;
                }
            }
        }
        closedir($dh);

        return $classList;
    }

    /**
     * 获取控制权限内容
     */
    protected function getControllerAuthContent($controller)
    {
        $controllerName = $controller . '_controller';

        // 反射控制器信息
        if (!class_exists($controllerName)) {
            return false;
        }

        // 注解实例
        $docparser = getInstance('lib_util_docparser');

        $ref = new \ReflectionClass($controllerName);
        $properties = $ref->getDefaultProperties();

        if (!$properties) {
            return false;
        }

        // 不加入系统权限控制
        // 1、声明使用自定义权限的不加入  2、不强制登录的控制器不加入  3、声明不使用系统权限控制的不加入
        if (!empty($properties['_isCustomAuthControl']) || empty($properties['_isLoginRequired']) || empty($properties['_isAuthControl'])) {
            return false;
        }
        // 权限名称
        $authName = '';
        // 权限备注说明
        $note = '';
        // 解析控制器注释
        $doc = $ref->getDocComment();

        if ($doc) {
            $docParameters = $docparser->parse($doc);
            $authName = empty($docParameters['authName']) ? '' : trim($docParameters['authName']);
            $note = empty($docParameters['note']) ? '' : trim($docParameters['note']);
        }

//         // 获取控制器内 action列表
//         $actions = [];
//         foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $key => $value) {
//             $actionNameLen = strlen($value->name);
//             if ($value->class == $controllerName && $actionNameLen > 7 && substr($value->name, -7, 7) == '_action') {
//                 $methodDoc = $value->getDocComment();
//                 $methodDocParameters = $docparser->parse($methodDoc);
//                 $actionName = substr($value->name, 0, $actionNameLen - 7);
//
//                 $actions[] = [
//                     'action'    => $actionName,
//                     'auth_name' => empty($methodDocParameters['authName']) ? '' : trim($methodDocParameters['authName'])
//                 ];
//             }
//         }

        if (substr_count($controller, '_') > 0) {
            $tag = substr($controller, 0, stripos($controller, '_'));
        }

        return [
            'tag_name' => empty($tag) ? '' : $tag,
            'auth_name' => $authName,
            'note' => $note,
            'controller' => $controller,
        ];
    }

    /**
     * 编辑权限信息
     */
    public function edit_action()
    {
        $id = input('id', 0, 'intval');
        $authModel = getInstance('admin_auth_model');

        $auth = $authModel->id($id)->find();

        if (!$auth) {
            return $this->_error('此权限不存在');
        }

        if (is_post()) {
            // 权限名称
            $authName = input('auth_name', '');
            // 是否启用
            $isOpen = input('is_open', 0, 'intval');
            $isOpen = $isOpen ? 1 : 0;

            // 备注说明
            $note = input('note', '', 'htmlentities');
            if ($note) {
                $note = lib_util_safe::removeXss($note);
            }

            if (mb_strlen($authName) < 2 || mb_strlen($authName) > 12) {
                return $this->_error('请输入2-10个字符的权限名称');
            }

            // 检查参数
            $authNameReg = "/^[\x{4e00}-\x{9fa5}A-Za-z0-9\[\]\{\}\(\)\（\）]+$/u";

            if (!preg_match($authNameReg, $authName)) {
                return $this->_error('名称只能由中文、数字、字母、[]、{}、（）、()等字符组成');
            }

            $updateAuth = [
                'auth_name' => $authName,
                'is_open' => $isOpen,
                'note' => $note ?: '',
            ];

            // 数据是否变更
            if (array_intersect_key($auth, $updateAuth) == $updateAuth) {
                return $this->_error('信息无变化，无需保存');
            }

            $upState = getInstance('admin_auth_model')->id($id)->update($updateAuth);

            if (!$upState) {
                return $this->_success('保存失败');
            }

            // 权限开启状态被修改
            if ($isOpen != $auth['is_open']) {
                getInstance('admin_roleauth_model')->where('controller', $auth['controller'])->update('is_open', $isOpen);
            }

            return $this->_success('保存成功');
        }

        $this->_assign('data', $auth);
        $this->_render();
    }


}
