<?php

/**
 * IP相关的东西啦
 *
 * @package		KIS
 * @author		dianjiemian
 * @since		2018-11-21
 * @example
 *
 */
class hlp_ip {

    /**
     * 获取客户端IP地址
     * @param  boolean $proxy_override 是否代理优先 默认是 不要传false  除非你确定你知道你在干什么！
     * @return [type]                  [description]
     */
    public static function get($proxy_override = true) {
        //return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '-';
        if ($proxy_override) {
            /* 优先从代理那获取地址或者 HTTP_CLIENT_IP 没有值 */
            $ip = empty($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_CLIENT_IP"] : $_SERVER["HTTP_X_FORWARDED_FOR"];
        } else {
            /* 取 HTTP_CLIENT_IP, 虽然这个值可以被伪造, 但被伪造之后 NS 会把客户端真实的 IP 附加在后面 已经没有NS了 不需要这段了 */
            //$ip = empty($_SERVER["HTTP_CLIENT_IP"]) ? NULL : $_SERVER["HTTP_CLIENT_IP"];
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        /* 真实的IP在以逗号分隔的最后一个, 当然如果没用代理, 没伪造IP, 就没有逗号分离的IP 无NS 无逗号 已没用了 */
        //带逗号的解析方式 基于CDN或者代理 传过来的格式，如调整 需要调整下面的解析方式
        if ($p = strrpos($ip, ',')) {
            $ip = trim(explode(',', $ip)[0]);
            //$ip = trim(substr($ip, $p + 1)); //坑爹有空格
        }
        if (empty($ip)) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            if (self::isLocal($ip)) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }
        }
        return trim($ip);
    }

    /**
     * 获得服务器IP
     */
    public static function getServerIp() {
        return isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '127.0.0.1';
    }

    /**
     * 是否是局域网保留IP段
     * @param  string  $ip 要判断的IP地址
     * @return boolean     是否是局域网IP
     */
    public static function isLocal($ip) {
        $l = ip2long($ip);
        if (
                ($l > 167772160 && $l < 184549375) ||
                ($l > 2886729728 && $l < 2887778303) ||
                ($l > 3232235520 && $l < 3232301055)
        ) {
            return true;
        }
        return false;
    }

}
