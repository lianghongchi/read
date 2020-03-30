<?php

/*
 * 【建议参数命名规则】(支持GBK中文，注意防止重名)
 *  <PUV名称>  [<应用/目录名称>_<页面名称>]
 *  <动作名称> [<应用/页面名称>_<动作名称>]
 * 【设置一级频道名】
 *  Stat::setChannel($channel);
 * 【设置不自动检测登录态，非登录态下自动检查登录态会重置SESSION,在safe,passport频道下会有问题】
 *  Stat::noAutoCheckLogin();
 * 【PUV统计】
 *  Stat::PageView($page_name='首页');
 *  Stat::PageView($page_name='系统通知', $channel_2 = '收件箱', $channel_3 = '', $account = null);
 * 【动作统计】
 *  Stat::ActionStat($action_name='打招呼');
 *  Stat::ActionStat($action_name='打招呼', $channel_2 = '附近的人', $channel_3 = '', $account = null);
 *
 *
 *
 * *******************以下仅限内部使用，乱调用可能你的统计会全部乱掉的哦亲~~~*****************************************
 * 【详细用法】：
 * load_library('pub:core/stat', false);
 * Stat::Online($account = 'ring_51', $channel = '一级频道', $application = '二级频道', $page = '三级频道', $auto_check_login = true);
 * Stat::PV($pv_name = '邮箱注册页PV', $channel = null, $application = '注册', $page = '邮箱注册', $account = null, $auto_check_login = true);
 * Stat::Action($pv_name = '邮箱注册页_输入验证码', $channel = null, $application = '注册', $page = '邮箱注册', $account = null, $auto_check_login = true);
 * 说明：$auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
 * ****************************************************
 */

class hlp_stat {

    private static $_channel_arr = [
        'sdk' => 1,
        'game' => 1,
        'login' => 1,
        'cavalry' => 1,
        'pay' => 1,
        'www' => 1,
        'jkht' => 1,
        'huodong' => 1,
        'kfadmin' => 1,
        '公共登录层' => 1,
        '登录' => 1,
    ];
    private static $channel_length_max_from_server_name = 10; //自动获取频道号最长的长度，超过此长度则认为是异常DNS数据，用于过滤
    private static $channel_error_stat_filtrate = true; //是否过滤异常频道号，过滤则不统计，用于过滤异常DNS
    private static $channel_global_name = '_GLOBAL_CHANNEL_NAME_FOR_STAT_'; //默认频道名，框架中设定
    private static $noautochecklogin_global_name = '_GLOBAL_NOAUTOCHECKLOGIN_NAME_FOR_STAT_';
    private static $application_global_name = '_GLOBAL_APPLICATION_NAME_FOR_STAT_'; //默认应用名，框架中设定
    private static $page_global_name = '_GLOBAL_PAGE_NAME_FOR_STAT_'; //默认页面名，框架中设定
    private static $channel_error = false; //用于存储是否检测到异常频道号，用于过滤异常DNS

    /**
     * 设置页面名
     * @param type $page_name
     * @return boolean
     */

    public static function setPageName($page_name) {
        if (!$page_name) {
            return false;
        }
        $GLOBALS[self::$page_global_name] = self::cleanString($page_name);
        return true;
    }

    /*
     * @desc	设置频道名
     * @param	string	$channel 频道名
     * @return	boolean
     */

    public static function setChannel($channel = null) {
        if (!$channel || !isset(self::$_channel_arr[$channel])) {
            return false;
        }
        $GLOBALS[self::$channel_global_name] = $channel;
        return true;
    }

    /**
     * 设置应用
     * @param type $application
     * @return boolean
     */
    public static function setApplication($application = null) {
        if (!$application) {
            return false;
        }
        $GLOBALS[self::$application_global_name] = self::cleanString($application);
        return true;
    }

    private static function checkChannel($channel = null) {
        if (!$channel) {
            return false;
        }
        return isset(self::$_channel_arr[$channel]);
    }

    /*
     * @desc	设置不开启自动检测登录态【在非登录态下检测登录态会充值SESSION，在密码找回或passport等需要未登录态记录session的地方会出问题】
     * @param	boolean	是否【不自动检测】，默认是
     */

    private static function noAutoCheckLogin($no_auto_check = true) {
        $GLOBALS[self::$noautochecklogin_global_name] = $no_auto_check;
        return true;
    }

    /**
     * 全站PUV统计的代码
     *
     * @param $pv_name	 PUV名称 【支持中文】
     * @param $application	【第2级】应用级别  各频道自定义【支持中文】
     * @param $page		【第3级】页面级别  自定义【支持中文】
     * @param string $account	内部帐号$_SESSION['Account']
     * @param boolean $auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
     * @return string
     */
    public static function PageView($pv_name = null, $application = '', $page = '', $account = null, $auto_check_login = true) {
        self::PV($pv_name, null, $application, $page, $account, $auto_check_login);
        return true;
    }

    /**
     * 全站动作统计的代码
     *
     * @param $pv_name	 动作名称 【支持中文】
     * @param $application	【第2级】应用级别  各频道自定义【支持中文】
     * @param $page		【第3级】页面级别  自定义【支持中文】
     * @param string $account	内部帐号$_SESSION['Account']
     * @param boolean $auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
     * @return string
     */
    public static function ActionStat($action_name = null, $application = '', $page = '', $account = null, $auto_check_login = true) {
        return self::Action($action_name, null, $application, $page, $account, $auto_check_login);
    }

    public static function NoPv() {
        defined('_DO_NOT_STAT_PV_FOR_DSS_') || define('_DO_NOT_STAT_PV_FOR_DSS_', true);
    }

    /**
     * 全站PUV统计的代码
     *
     * @param $pv_name	 PUV名称 【支持中文】
     * @param $channel	【第1级】频道名称  为空时取域名前缀【支持中文】
     * @param $application	【第2级】应用级别  各频道自定义【支持中文】
     * @param $page		【第3级】页面级别  自定义【支持中文】
     * @param string $account	内部帐号$_SESSION['Account']
     * @param boolean $auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
     * @return string
     */
    public static function PV($pv_name = null, $channel = null, $application = '', $page = '', $uin = null, $auto_check_login = true) {

        $i = 0;
        if (empty($_SERVER["HTTP_HOST"])) {
            return false;
        }

        if (defined("_DO_NOT_STAT_PV_FOR_DSS_") && _DO_NOT_STAT_PV_FOR_DSS_) {
            return false;
        }

        $channel = self::getChannel($channel);

        if (!$channel && !isset($GLOBALS[self::$channel_global_name])) {
            return false;
        }

        if ('UNKNOWN' == $channel) {
            //unknown的频道不统计            
            return false;
        }

        $pv_name = self::cleanString($pv_name);

        $global_key = '_FO_STAT_PAGEVIEW_ONLY_ONE_TIME_PER_WEBPAGE_';
        if (isset($GLOBALS[$global_key]) && $GLOBALS[$global_key]) {
            //一个页面只统计一次            
            return false;
        } else {
            $GLOBALS[$global_key] = true;
        }

        if (!$pv_name) {
            $pv_name = 'UNKNOWN';
        }
        if (!$page) {
            if (isset($GLOBALS[self::$page_global_name]) && $GLOBALS[self::$page_global_name]) {
                $page = $GLOBALS[self::$page_global_name];
            } else {
                $page = 'null';
            }
        }
        if (self::$channel_error && self::$channel_error_stat_filtrate) {
            return false;
        }

        if (!$uin) {
            if ($auto_check_login) {
                self::checkLoginStatus();
                //$uin = lib_passport_login::getLoginUin();
            }
            $uin = self::getUid();
        }

        if (!$application) {
            if (isset($GLOBALS[self::$application_global_name])) {
                $application = $GLOBALS[self::$application_global_name];
            } else {
                $application = 'null';
            }
        }

        if ('UNKNOWN' == $channel) {
            $application = php_uname('n');
            $page = $_SERVER["HTTP_HOST"];
            hlp_log::log('dss_pv_unknow', $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]);
        }

        $param = [
            'time' => time(),
            'sessionid' => $_COOKIE['FO_TUID'] ? $_COOKIE['FO_TUID'] : session_id(),
            'uin' => $uin,
            'channel_1' => $channel,
            'channel_2' => self::cleanString($application),
            'channel_3' => self::cleanString($page),
            'pv_name' => self::cleanString($pv_name),
            'pv_id' => hlp_zh2pinyin::Pinyin($pv_name),
            'uri' => substr($_SERVER["HTTP_HOST"] . str_replace(',', ';', $_SERVER["REQUEST_URI"]), 0, 255),
            'refer' => substr(str_replace(',', ';', $_SERVER['HTTP_REFERER']), 0, 255),
        ];
        foreach ($param as $key => $value) {
            $param[$key] = ($value == '') ? '1' : str_replace(["\r", "\n", ",", " "], "", $value);
        }
        $param['channel_1'] = $param['channel_1'];
        $param['channel_2'] = $param['channel_2'];
        $param['channel_3'] = $param['channel_3'];
        $param['pv_name'] = $param['pv_name'];
        $param['uri'] = $param['uri'];
        $param['refer'] = $param['refer'];
        $param['time'] = date("Y-m-d H:i:s", $param['time']);
        $param['ip'] = hlp_ip::get();
        //self::ActionTest();
        //self::OnlineTest();
        $pv_content = implode(',', $param);
        $_GET['dss'] == 'ok' && error_log($pv_content . "\n", 3, "/tmp/pv_com." . date("Ymd"));
        $rtn = hlp_msg::pv($pv_content);
        //exit("PVPVPVPV");
        return $rtn;
    }

    private static function ActionTest() {
        $channel = ['www', 'pay', 'login'];
        $application = ['app1', 'app2', 'app3'];
        $page = ['page1', 'page2', 'page3'];
        $action_name = ['action1', 'action2', 'action3'];
        shuffle($channel);
        shuffle($application);
        shuffle($page);
        shuffle($action_name);
        self::Action(array_pop($action_name), array_pop($channel), array_pop($application), array_pop($page));
    }

    /**
     * 全站动作统计的代码
     *
     * @param $action_name	 动作名称 【支持中文】
     * @param $channel	【第1级】频道名称  为空时取域名前缀【支持中文】
     * @param $application	【第2级】应用级别  各频道自定义【支持中文】
     * @param $page		【第3级】页面级别  自定义【支持中文】
     * @param string $account	内部帐号$_SESSION['Account']
     * @param boolean $auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
     * @return string
     */
    public static function Action($action_name = null, $channel = null, $application = '', $page = '', $uin = null, $auto_check_login = true) {
        $action_name = self::cleanString($action_name);
        if (!$action_name) {
            return false;
        }
        $channel = self::getChannel($channel);
        if (self::$channel_error && self::$channel_error_stat_filtrate) {
            return false;
        }

        if (!$uin) {
            if ($auto_check_login) {
                self::checkLoginStatus();
                //$uin = lib_passport_login::getLoginUin();
            }
            $uin = self::getUid();
        }
        $action_uri = substr(($_SERVER["HTTP_REFERER"] ? $_SERVER["HTTP_REFERER"] : ('http://' . $_SERVER["HTTP_HOST"] . str_replace(',', ';', $_SERVER["REQUEST_URI"]) )), 0, 255);
        //$action_uri = str_replace(["\n", "\r"], [""], $action_uri);
        $param = [
            'time' => time(),
            'sessionid' => $_COOKIE['FO_TUID'] ? $_COOKIE['FO_TUID'] : session_id(),
            'uin' => $uin,
            'channel_1' => $channel,
            'channel_2' => self::cleanString($application),
            'channel_3' => self::cleanString($page),
            'action_name' => self::cleanString($action_name),
            //'action_id' => hlp_zh2pinyin::Pinyin($action_name),
            'uri' => $action_uri,
            'ip' => hlp_ip::get()
        ];
        foreach ($param as $key => $value) {
            $param[$key] = ($value == '') ? '1' : str_replace(["\r", "\n", ",", " "], "", $value);
        }
        $param['time'] = date("Y-m-d H:i:s", $param['time']);

        $action_content = implode(',', $param);
        $_GET['dss'] == 'ok' && error_log($action_content . "\n", 3, "/tmp/action_com." . date("Ymd"));
        $rtn = hlp_msg::action($action_content);
        return $rtn;
    }

    private static function OnlineTest() {
        $channel = ['www', 'pay', 'login'];
        $application = ['app1', 'app2', 'app3'];
        $page = ['page1', 'page2', 'page3'];

        shuffle($channel);
        shuffle($application);
        shuffle($page);

        self::Online(array_pop($channel), array_pop($application), array_pop($page));
    }

    /**
     * 全站在线情况统计的代码
     *
     * @param $channel	【第1级】频道名称  为空时取域名前缀
     * @param $application	【第2级】应用级别  各频道自定义
     * @param $page		【第3级】页面级别  自定义
     * @param string $account	内部帐号$_SESSION['Account']
     * @param boolean $auto_check_login 是否自动检测登录态，默认true【注意：在非登录态下自动检测登录态会导致SESSION被重置】
     * @return string
     */
    public static function Online($channel = null, $application = '', $page = '', $uin = null, $auto_check_login = true) {
        if (!$uin) {
            if ($auto_check_login) {
                self::checkLoginStatus();
            }
            $uin = self::getUid();
        }

        if (!$uin) {
            //没有取到用户           
            return false;
        }
        $channel = self::getChannel($channel);

        $ip = hlp_ip::get(true);
        $arr = [
            'channel' => self::cleanString($channel),
            'application' => self::cleanString($application),
            'page' => self::cleanString($page),
            'uin' => $uin,
            'time' => time(),
            'ip' => $ip,
            'url' => substr($_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"], 0, 255)
        ];
        //array(3) { ["prov"]=> string(4) "上海" ["city"]=> string(4) "上海" ["isp"]=> string(4) "电信" }
        $ip_info = hlp_ip2area::provCity($ip);
        if (!$ip_info['prov']) {
            $arr['prov'] = "";
            $arr['city'] = "";
        } else {
            $arr['prov'] = $ip_info['prov'];
            $arr['city'] = $ip_info['city'];
        }
        $arr['channel'] = strip_tags($arr['channel']);
        if (!$arr['channel'] || strlen($arr['channel']) > 32) {
            $arr['channel'] = 'channel_error';
        }
        $arr['application'] = strip_tags($arr['application']);
        if (!$arr['application'] || strlen($arr['application']) > 32) {
            $arr['application'] = '';
        }
        $arr['page'] = strip_tags($arr['page']);
        if (!$arr['page'] || strlen($arr['page']) > 32) {
            $arr['page'] = '';
        }

        $keys = array('time', 'uin', 'channel', 'application', 'page', 'ip', 'prov', 'city', 'url');
        $param = array();
        foreach ($keys as $key) {
            $param[] = isset($arr[$key]) ? $arr[$key] : '1';
        }
        $param[0] = date("Y-m-d H:i:s", $arr['time']);

        $msg = implode(',', $param);

        $_GET['dss'] == 'ok' && error_log($msg . "\n", 3, "/tmp/online_com." . date("Ymd"));
        $rtn = hlp_msg::online($msg);
        return $rtn;
    }

    private static function checkLoginStatus() {
        if (isset($GLOBALS[self::$noautochecklogin_global_name]) && $GLOBALS[self::$noautochecklogin_global_name]) {
            return true;
        }
        // 这里会清session 暂不使用
        lib_login_weblogin::checkLogin();
        return true;
    }

    public static function getUid() {
        //$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : null;
//        $uid = lib_login_weblogin::getLoginUin();
//        $uid = intval($uid);
//        if ($uid <= 0) {
//            $uid = isset($_SESSION['_eid_']) ? $_SESSION['_eid_'] : 0;
//        }
        return 0;
    }

    public static function getChannel($channel = null) {
        if ($channel && !isset(self::$_channel_arr[$channel])) {
            $channel = null;
        }
        if (!$channel && isset($GLOBALS[self::$channel_global_name]) && $GLOBALS[self::$channel_global_name]) {
            $channel = $GLOBALS[self::$channel_global_name];
        }
        if (!$channel) {
            if (strtolower(substr($_SERVER["HTTP_HOST"], -11)) == '.wuming.com') {
                $channel = strtolower(substr($_SERVER["HTTP_HOST"], 0, -11));
                if (strlen($channel) > self::$channel_length_max_from_server_name) {
                    self::$channel_error = true;
                }
            }
        }
        if (!isset(self::$_channel_arr[$channel])) {
            //$channel = 'UNKNOWN';
        }
        $channel = self::cleanString($channel);
        return $channel;
    }

    static function cleanString($str) {
        $str = strip_tags(trim($str));
        $replace_arr = ["\\", "\"", ",", "<", ">", "&", ";", "'", "*", "$", "#", "%", "\n", "\r"];
        $str = str_replace($replace_arr, [''], $str);
        return $str;
    }

    public static function mysql_error_report($errno, $error, $sql, $db_ip = '') {
        if (isset($_SERVER["HTTP_HOST"])) {
            $stat_key = $_SERVER["HTTP_HOST"] . '_' . php_uname('n');
        } else {
            $stat_key = php_uname('n');
        }
        //DSS
        self::Action('MYSQL错误_' . $stat_key, 'warning', 'MYSQL_ERROR', $_SERVER["HTTP_HOST"], null, false);
        $db_ip && self::Action('MYSQL错误_' . 'DBIP_' . $db_ip, 'warning', 'MYSQL_ERROR', 'DBIP_' . $db_ip, null, false);

        //file log
        error_log(date('Ymd H:i:s') . "\tMYSQL_ERROR\t{$errno}\t$error\t$sql\t{$_SERVER["SERVER_ADDR"]}\t{$_SERVER["HTTP_HOST"]}\t{$_SERVER["REQUEST_URI"]}\tdb_ip:{$db_ip}\n", 3, '/tmp/php_mysql.error.' . date('Ymd') . '.log');
    }

    public static function memcache_error_report($group, $error, $type = 'TIMEOUT') {
        if (isset($_SERVER["HTTP_HOST"])) {
            $stat_key = $_SERVER["HTTP_HOST"] . '_' . php_uname('n');
        } else {
            $stat_key = php_uname('n');
        }
        //DSS
        if ($type == 'TOO_LARGE') {
            $channel = 'log';
        } else {
            $channel = 'warning';
        }
        self::Action('MEMCACHE错误_' . $stat_key, $channel, 'MEMCACHE_' . $type, $group, null, false);

        //file log
        error_log(date('Ymd H:i:s') . "\tMEMCACHE_{$type}\t{$group}\t{$error}\t{$_SERVER["SERVER_ADDR"]}\t{$_SERVER["HTTP_HOST"]}\t{$_SERVER["REQUEST_URI"]}\n", 3, "/tmp/php_memcache.{$type}." . date('Ymd') . '.log');
    }

}
