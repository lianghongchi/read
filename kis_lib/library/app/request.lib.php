<?php
/**
 * request类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_app_request
{

    protected $appHeaderName = 'HTTP_DEVICE';

    protected $dataTypeHeaderName = 'HTTP_DATATYPE';


    // 当前域名 (包括协议 )
    protected $domain     = null;

    // 当前完整url
    protected $url        = null;

    // 子集域名
    protected $subdomains = null;

    // pathinfo
    protected $pathinfo   = null;

    // 客户端IP
    protected $ip         = null;

    // 服务器IP
    protected $serverIp   = null;

    // HOST
    protected $host       = null;

    // 主域名
    protected $mainHost   = null;

    // 请求AGENT
    protected $agent      = null;

    // scheme类型
    protected $scheme     = null;

    // 设备类型
    protected $device     = null;

    // 请求方法
    protected $method     = null;

    // API类型
    protected $dataType   = null;

    // 是否JSON数据格式
    protected $isJson     = null;


    // 请求类型
    protected $isPost     = null;
    protected $isGet      = null;
    protected $isPut      = null;
    protected $isDelete   = null;
    protected $isOptions  = null;
    protected $isAjax     = null;
    protected $isSsl      = null;
    protected $isWap      = null;
    protected $isWechat   = null;
    protected $isSpider   = null;
    protected $isIPServer = null;



    public function method()
    {
        if (is_null($this->method)) {
            $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'get');
        }
        return $this->method;
    }

    public function isPost()
    {
        if (is_null($this->isPost)) {
            $this->isPost = $this->method() == 'POST' ? true : false;
        }
        return $this->isPost;
    }

    public function isGet()
    {
        if (is_null($this->isGet)) {
            $this->isGet = $this->method() == 'GET' ? true : false;
        }
        return $this->isGet;
    }

    public function isPut()
    {
        if (is_null($this->isPut)) {
            $this->isPut = $this->method() == 'PUT' ? true : false;
        }
        return $this->isPut;
    }

    public function isDelete()
    {
        if (is_null($this->isDelete)){
            $this->isDelete = $this->method() == 'DELETE' ? true : false;
        }
        return $this->isDelete;
    }

    public function isOptions()
    {
        if (is_null($this->isOptions)){
            $this->isOptions = $this->method() == 'OPTIONS' ? true : false;
        }
        return $this->isOptions;
    }

    public function isAjax()
    {
        if (is_null($this->isAjax)){
            $this->isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                            (strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') ||
                            (isset($_GET['_ajax_request']) && $_GET['_ajax_request']) ?
                            true : false);
        }
        return $this->isAjax;
    }

    public function isSsl()
    {
        if (is_null($this->isSsl)){
            if (
                (isset($_SERVER['HTTPS']) && ('1' == $_SERVER['HTTPS'] || 'on' == strtolower($_SERVER['HTTPS']))) ||
                (isset($_SERVER['REQUEST_SCHEME']) && 'https' == $_SERVER['REQUEST_SCHEME']) ||
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && 'https' == $_SERVER['HTTP_X_FORWARDED_PROTO'])
            )
            {
                $this->isSsl = true;
            } else {
                $this->isSsl = false;
            }
        }
        return $this->isSsl;
    }

    public function dataType()
    {
        if (is_null($this->dataType)) {
            $this->dataType  = isset($_SERVER[$this->dataTypeHeaderName]) ? strtoupper($_SERVER[$this->dataTypeHeaderName]) : 'HTML';
        }
        return $this->dataType;
    }

    public function isJson()
    {
        if (is_null($this->isJson)){
            $this->isJson = $this->dataType() == 'JSON' || $this->isAjax();
        }

        return $this->isJson;
    }

    public function isWap()
    {
        if (is_null($this->isWap)){
            if (
                (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) ||
                (strpos(strtoupper($_SERVER['HTTP_ACCEPT'] ?? ''), "VND.WAP.WML")) ||
                (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE']))||
                (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT']))
            )
            {
                $this->isWap = true;
            } else {
                $this->isWap = false;
            }
        }
        return $this->isWap;
    }

    public function isWechat()
    {
        if (is_null($this->isWechat)) {
            $this->isWechat = (strpos($this->agent(), 'MicroMessenger') !== false && !$this->isMiniapp()) ? true : false;
        }
        return $this->isWechat;
    }

    public function device()
    {
        if (is_null($this->device)){
            $appHeaderName = $_SERVER[$this->appHeaderName] ?? '';

            if ($appHeaderName && strlen($appHeaderName) < 18 && !ctype_alnum($appHeaderName)) {
                $appHeaderName = '';
            }
            $this->device = $appHeaderName ? : 'WEB';
        }
        return $this->device;
    }

    public function isSpider()
    {
        if (is_null($this->isSpider)){
            $kwSpiders = ['bot', 'crawl', 'spider' ,'slurp', 'sohu-search', 'lycos', 'robozilla'];
            $this->isSpider = false;
            $agent = strtolower($this->agent());

            foreach($kwSpiders as $value)
            {
                if(strpos($agent, $value) !== false)
                {
                    $this->isSpider = true;
                    break;
                }
            }
        }
        return $this->isSpider;
    }

    public function isIPServer()
    {
        if (is_null($this->isIPServer)){
            $this->isIPServer = filter_var($_SERVER['HTTP_HOST'], FILTER_VALIDATE_IP) ? true : false;
        }
        return $this->isIPServer;
    }

    public function input($name = '', $default = null, $filterFunc = null)
    {
        if ($this->isPost()){
            static $_inputPost = null;

            if (is_null($_inputPost)) {
                $_inputPost = file_get_contents('php://input');
            }

            if ($_inputPost) {
                if (is_array($_inputPost)) {
                    $_REQUEST = array_merge($_REQUEST, $_inputPost);
                } else if(($inputTemp = json_decode($_inputPost, true))) {
                    $_inputPost = $inputTemp;
                    $_REQUEST = array_merge($_REQUEST, $inputTemp);
                }
            }
        }

        $data = $_REQUEST;

        if ('' == $name){
            return $data;
        }

        if (!isset($data[$name])) {
            return $default;
        }

        if ($filterFunc && $data[$name] !== '') {
            if (is_string($filterFunc)) {
                $filterFunc = [$filterFunc];
            }
            foreach ($filterFunc as $key => $func) {
                $data[$name] = call_user_func($func, $data[$name]);
            }
        }
        return $data[$name];
    }

    public function domain()
    {
        if (is_null($this->domain)){
            $this->domain = $this->scheme().'://'.$this->host();
        }
        return $this->domain;
    }

    public function scheme()
    {
        if (is_null($this->domain)){
            $this->scheme = $this->isSsl() ? 'https' : 'http';
        }
        return $this->scheme;
    }

    public function host()
    {
        if (is_null($this->host)){
            $this->host = $_SERVER['HTTP_HOST'] ?? '';
        }
        return $this->host;
    }

    public function mainHost()
    {
        if (is_null($this->mainHost)){
            $hostArray = explode('.', $this->host());
            $host = array_pop($hostArray);
            $main = array_pop($hostArray);
            $this->mainHost = $main . '.' . $host;
        }
        return $this->mainHost;
    }

    public function url()
    {
        if (is_null($this->url)){
            $this->url = $this->scheme().'://'.$this->host().($_SERVER['REQUEST_URI'] ?? '');
        }
        return $this->url;
    }

    public function subdomains()
    {
        if (is_null($this->subdomains)){
            $subs = explode('.', $this->host());
            array_pop($subs);
            array_pop($subs);

            if (count($subs) > 1) {
                $subs = array_reverse($subs);
            }

            $this->subdomains = $subs;
        }
        return $this->subdomains;
    }

    public function ip()
    {
        if (is_null($this->ip)){
            if (isset($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP']) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR']) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else if ($_SERVER['REMOTE_ADDR'] && $_SERVER['REMOTE_ADDR']) {
                $ip = $_SERVER['REMOTE_ADDR'];
            } else {
                $ip = '0.0.0.0';
            }

            if (false !== strpos($ip, ',')) {
                $fip = explode(',', $ip);
                $ip  = reset($fip);
            }

            $this->ip = $ip;
        }
        return $this->ip;
    }

    public function serverIp()
    {
        if (is_null($this->serverIp)) {
            $this->serverIp = $_SERVER['SERVER_ADDR'] ?? '0.0.0.0';
        }
        return $this->serverIp;
    }


    public function pathinfo()
    {
        if (is_null($this->pathinfo)){
            $pathinfo = '';

            if (!isset($_SERVER['PATH_INFO']) || !$_SERVER['PATH_INFO']) {
                $upv = $this->app->make('config')->get('http.pathinfo_val');
                if (isset($_GET[$upv])) {
                    $pathinfo = $_GET[$upv];
                    unset($_GET[$upv]);
                    unset($_REQUEST[$upv]);
                }
                $_SERVER['PATH_INFO'] = $pathinfo;
            }

            $restatic = $this->app->make('config')->get('http.static_suffix');

            if ($restatic) {
                $_SERVER['PATH_INFO'] = str_replace($restatic, '', $_SERVER['PATH_INFO']);
            }
            $this->pathinfo = $_SERVER['PATH_INFO'] ? rtrim($_SERVER['PATH_INFO'], '/') : '';
        }
        return $this->pathinfo;
    }

    public function agent()
    {
        if (is_null($this->agent)) {
            $this->agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }
        return $this->agent;
    }


    public function __call($method, $params)
    {
        if (strpos($method, 'is') === 0) {
            $pf = strtolower(substr($method, 2));
            $device = $this->device();
            return $device == strtolower($pf);
        }

        throw new \Exception('Call to undefined http_request::' . $method);
    }

}
