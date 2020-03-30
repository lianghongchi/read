<?php
/**
 * smarty模板
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-5
 *
 */

class lib_view_smarty
{

    /**
     * 模板引擎对象
     * @var object
     */
    protected $engine = null;

    /**
     * 模板配置
     * @var array
     */
    protected $templateSuffix = '.html';



    public function __construct()
    {
        $this->viewPath = KIS_APPLICATION_ROOT . 'view/';

        require KIS_CORE_LIB_ROOT . 'library/view/smarty/Autoloader.php';
        Smarty_Autoloader::register();

        $this->engine = new \Smarty();

        $this->engine->left_delimiter  = '{[';
        $this->engine->right_delimiter = ']}';

        $this->engine->caching = false;
        $this->engine->template_dir = KIS_APPLICATION_ROOT . 'view/';
        $this->engine->compile_dir  = '/tmp/kis_view/compile';
        $this->engine->cache_dir    = '/tmp/kis_view/cache';
    }

    public function fetch($template = '', $data = [])
    {
        if ($template && stripos($template, '.') !== false) {
            throw new \Exception("The template path contains illegal characters");
        }

        if (!$template) {
            $controller = router::getController();
            if ($controller && stripos($controller, '_')) {
                $controller = str_replace('_', '/', $controller);
            }

            $template = $controller . '/' . router::getAction();
        }

        $file = $this->viewPath . $template . $this->templateSuffix;

        if (!$file) {
            throw new \Exception("View Template Not Found");
        }

        $this->engine->assign($data);

        return $this->engine->fetch($file);
    }

    public function clearCache()
    {
        $this->engine->clearCompiledTemplate();
        $this->engine->clearAllCache();
    }


}
