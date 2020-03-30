<?php
/**
 * http控制器基类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_app_controller extends controller
{

    /**
     * request实例
     * @var object
     */
    protected $_req = null;

    /**
     * 模板数据
     * @var array
     */
    protected $_tplData  = [];

    /**
     * 网页模板文件名称
     * @var string
     */
    protected $_template = '';

    /**
     * 当前控制器名称
     * @var string
     */
    protected $_controller = '';

    /**
     * 当前操作名称
     * @var string
     */
    protected $_action = '';

    /**
     * 执行的最后结果
     * @var array
     */
    protected $_renderResult = [];

    /**
     * 构造方法
     * @return
     */
    public function __construct()
    {
        $this->_controller = Router::getController();
        $this->_action = Router::getAction();

        $this->_req = getInstance('lib_app_request');
    }

    /**
     * 数据渲染
     * @param  mix $code 操作状态
     * @param  string $msg 消息
     * @param  array $data 渲染数据
     * @return string json
     */
    protected function _render($code = 200, $msg = 'OK', $data = [])
    {
        if (is_array($code) && isset($code['code']) && isset($code['msg'])) {
            $msg = $code['msg'];
            $data = $code['data'] ?? '';
            $code = $code['code'];
        }

        $this->_renderResult = return_format($code, $msg, $data);

        // json数据类型
        if ($this->_req->isJson()) {
            $data && $this->_assign($data);
            $jsonObject = new \stdClass();

            foreach ($this->_tplData as $key => $value) {
                if (is_null($value)) {
                    $value = new \stdClass();
                }
                $jsonObject->$key = $value;
            }

            return response::jsonp($code, $msg, $jsonObject, false);
        }

        // html类型
        $this->_viewRender();
    }

    /**
     * 设置html模板文件
     * @param string $template 模板文件
     */
    protected function _setTemplate($template)
    {
        $this->_template = $template;
    }

    /**
     * 数据赋值
     * @param  mix $name array | key
     * @param  string $value value
     * @return null
     */
    protected function _assign($name = null, $value = '')
    {
        if (is_array($name)) {
            $this->_tplData = array_merge($this->_tplData, $name);
        } else if(is_null($name)) {
            return $this->_tplData;
        } else if ($name) {
            $this->_tplData[$name] = $value;
        }
    }

    /**
     * 操作失败
     * @param  string $msg 提示消息
     * @param  array $data 数据
     * @return string json
     */
    protected function _error($msg = '', $data = [])
    {
        if ($this->_req->isJson()) {
            return $this->_render(400, $msg, $data);
        }

        $this->_setTemplate(config::get('app.jump_error', false));
        $this->_redirectTemplate(return_format(404, $msg, [
            'url' => $data['url'] ?? ''
        ]));
    }

    /**
     * 操作成功
     * @param  string $msg 提示消息
     * @param  array $data 数据
     * @return string json
     */
    protected function _success($msg = '', $data = [])
    {
        if ($this->_req->isJson()) {
            return $this->_render(200, $msg, $data);
        }

        $this->_setTemplate(config::get('app.jump_success', false));
        $this->_redirectTemplate(return_format(200, $msg, [
            'url' => $data['url'] ?? ''
        ]));
    }

    /**
     * 跳转到指定模板
     * @param  array $data 模板数据
     * @return
     */
    protected function _redirectTemplate($data)
    {
        $data = array_merge($data, [
            'request' => $this->_req
        ]);

        $this->_assign($data);
        $this->_viewRender();
    }

    /**
     * 跳转到指定URL
     * @param  int $code 状态
     * @param  string $msg 提示消息
     * @param  string $url url
     * @return
     */
    protected function _redirect($code, $msg, $url)
    {
        if ($this->_req->isJson()) {
            return $this->_render($code, $msg);
        }

        header("Location:{$url}");
    }


    /**
     * 获取分页信息
     * @param  int $pageRows 每页数量
     * @param  integer $count 数据总数
     * @return array 分页信息
     */
    protected function _getDataPage($pageRows, $count = 0)
    {
        $page = input('page', 1, 'intval');

        if ($page < 1) {
            $page = 1;
        }

        return [
            'start'      => ($page-1)*$pageRows,
            'end'        => $pageRows,
            'num'        => $page,
            'pageRows'   => $pageRows,
            'totalCount' => $count,
            'totalPage'  => floor(($count + $pageRows-1)/$pageRows),
        ];
    }

    /**
     * 获取html模板内容
     * @param  [type] $template [description]
     * @return [type] [description]
     */
    protected function _viewFetch()
    {
        return getInstance('lib_view_smarty')->fetch($this->_template, $this->_tplData);
    }

    /**
     * 输出html模板
     * @return string 模板内容
     */
    protected function _viewRender()
    {
        echo $this->_viewFetch();
    }


}
