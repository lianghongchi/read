<?php

/**
 * IP转地区（省市）
 */
class hlp_ip2area {

    private static $address = array(
        //memcache+redis
        // __NDS_MC_HOST_HOME_1__,
        // __NDS_MC_HOST_HOME_2__,
        // __NDS_MC_HOST_GAME_1__,
        // __NDS_MC_HOST_GAME_2__,
        __KIS_IP2AREA_HOST_DEFAULT_1__,
    );
    private static $service_port = 8310;
    private static $magic_num = 0x1405080F;

    /**
     * 根据ip获取城市，如果不传ip取本机ip所在的城市
     * @param type $ip
     * @param type $return_id
     * @return boolean
     */
    public static function provCity($ip = null, $return_id = false) {
        if (!$ip) {
            $ip = hlp_ip::get();
        }

        if (self::isLocalIP($ip)) {
            return array('prov' => '', 'city' => ''); //本地局域网
        }
        // $xcache_key = 'xcache_ip2area_' . $ip . '_' . intval($return_id);
        // $cache = xcache_get($xcache_key);
        // if ($cache) {
        //     return $cache;
        // }
        $server_id = mt_rand(0, 99999) % count(self::$address);
        /* 创建socket */
        if (!$socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) {
            return false;
        }
        /* 链接socket */
        if (!$result = @socket_connect($socket, self::$address[$server_id], self::$service_port)) {
            return false;
        }
        /* 发送数据 */
        $in = pack('N', self::$magic_num) . pack('N', strlen($ip)) . $ip;

        socket_set_option($socket, SOL_SOCKET, SO_SNDTIMEO, array("sec" => 0, "usec" => 50000));
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array("sec" => 0, "usec" => 50000));
        socket_write($socket, $in);

        /* 解析数据 */
        $out = socket_read($socket, 4);
        $ret = unpack("N", $out);
        if ($ret[1] == self::$magic_num) {
            $out = socket_read($socket, 4);
            $res = unpack("N", $out);

            $len = $res[1];
            $red_str = socket_read($socket, $len);
            $red_str = str_replace("区", "", iconv("gbk", "utf-8", $red_str));
            $red = explode("|", $red_str);

            $place['prov'] = isset($red[0]) ? $red[0] : "";
            $place['city'] = isset($red[1]) ? $red[1] : "";
            $place['isp'] = isset($red[2]) ? $red[2] : "";
            if ($return_id) {
                //这里取id，改用调用hlp_area
                //$place['prov_id'] = isset(self::$prov2id[$place['prov']]) ? self::$prov2id[$place['prov']] : "";
                //$place['city_id'] = isset(self::$city2id[$place['city']]) ? self::$city2id[$place['city']] : "";
                $place['prov_id'] = hlp_area::get($place['prov']);
                $place['city_id'] = hlp_area::get($place['prov'], $place['city']);
                $place['isp_id'] = isset(self::$isp2id[$place['isp']]) ? self::$isp2id[$place['isp']] : "";
            }
        }

        socket_close($socket);
        // if (isset($place['prov']) && $place['prov']) {
        //     xcache_set($xcache_key, $place, 600);
        // }
        return isset($place) ? $place : "";
    }

    /**
     * 获取省市的文字
     * @param type $ip
     * @param type $getISP
     * @return type
     */
    public static function getArea($ip, $getISP = true) {
        $area_info = self::provCity($ip);
        $area = '';
        if (is_array($area_info)) {
            foreach ($area_info as $k => $v) {
                ($getISP || $k != 'isp') && $v != 'unknown' && $area .= $v;
            }
        }
        return $area;
    }

    /**
     * 获取城市——文字
     * @param type $ip
     * @return type
     */
    public static function getCity($ip = null) {
        $area_info = self::provCity($ip);
        if (in_array($area_info['prov'], array('北京', '天津', '上海', '重庆'))) {
            return $area_info['prov'];
        } else {
            if ($area_info['city'] != 'unknown') {
                return $area_info['prov'] . $area_info['city'];
            } else {
                return $area_info['prov'];
            }
        }
    }

    public static function isLocalIP($ip = null) {
        if (!$ip) {
            $ip = hlp_ip::get(true);
        }
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

    private static $isp2id = array(
        '教育网' => '1',
        '电信' => '2',
        '联通' => '3',
        '长城宽带' => '4',
        'CZ88.NET' => '5',
        'unknown' => '6',
        '有线通' => '7',
        '铁通' => '8',
        '移动' => '13',
        '网通' => '14'
    );

}
