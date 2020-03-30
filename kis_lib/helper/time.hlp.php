<?php

/**
 *  Time 公用操作类
 *
 * @author dianjiemian
 * @version 1.0
 * @created 2018-11-21 17:04:07
 * @modified zhuyb 2018-11-21 17:04:12
 */
class hlp_time
{

    /**
     * 把秒数转换为时分秒的格式
     *
     * @param Int $times 时间，单位 秒
     * @return String
     */
    public static function secToTime($times)
    {
        $result = '00:00:00';
        if ($times > 0) {
            $hour = floor($times / 3600);
            $minute = floor(($times - 3600 * $hour) / 60);
            $second = floor((($times - 3600 * $hour) - 60 * $minute) % 60);


            $hour = strlen($hour) == 1 ? '0' . $hour : $hour;
            $minute = strlen($minute) == 1 ? '0' . $minute : $minute;
            $second = strlen($second) == 1 ? '0' . $second : $second;
            $result = $hour . ':' . $minute . ':' . $second;
        }

        return $result;
    }

}
