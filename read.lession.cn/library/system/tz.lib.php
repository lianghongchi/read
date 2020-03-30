<?php
/**
 * 管理员认证token管理类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-13
 *
 */

class system_tz_lib
{



    public function getOSName()
    {
        foreach (glob('/etc/*release') as $name) {
            if ($name == '/etc/centos-release' || $name == '/etc/redhat-release' || $name == '/etc/system-release') {
                if ($osname = file($name)) {
                    return array_shift($osname);
                }
            }

            $releaseInfo = @parse_ini_file($name);

            if (isset($releaseInfo['DISTRIB_DESCRIPTION'])) {
                return $releaseInfo['DISTRIB_DESCRIPTION'];
            }

            if (isset($releaseInfo['PRETTY_NAME'])) {
                return $releaseInfo['PRETTY_NAME'];
            }
        }

        return php_uname('s').' '.php_uname('r');
    }


    function getCpuInfo()
    {
        if (!is_file('/proc/cpuinfo')) {
            return FALSE;
        }

        if (FALSE === ($str = file('/proc/cpuinfo'))) {
            return FALSE;
        }

        $cpu = [];

        $str = implode("", $str);

        // 型号
        @preg_match_all("/model\s+name\s{0,}\:+\s{0,}([\w\s\)\(\@.-]+)([\r\n]+)/s", $str, $model);
        // 频率
        @preg_match_all("/cpu\s+MHz\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $mhz);
        // 二级缓存
        @preg_match_all("/cache\s+size\s{0,}\:+\s{0,}([\d\.]+\s{0,}[A-Z]+[\r\n]+)/", $str, $cache);
        // 计算能力
        @preg_match_all("/bogomips\s{0,}\:+\s{0,}([\d\.]+)[\r\n]+/", $str, $bogomips);

        if (isset($model[1]) && is_array($model[1])) {
            $res['num'] = sizeof($model[1]);

            for($i = 0; $i < $res['num']; $i++) {
                $cpu[] = [
                    'name'  => $model[1][$i],
                    'mhz'   => $mhz[1][$i],
                    'cache' => $cache[1][$i],
                    'bogomips' => $bogomips[1][$i],
                ];
            }
            $res['info'] = $cpu;
        }

        return $res;
    }


    public function getSysInfo()
    {
        return [
            'stat'   => $this->getStat(),
            'stime'  => date('Y-m-d H:i:s'),
            'uptime' => $this->getUptime(),
            'meminfo' => $this->getMeminfo(),
            'loadavg' => $this->getLoadavg(),
        ];
     }


    public function getStat()
    {
        if (!is_file('/proc/stat')) {
            return FALSE;
        }

        $file = file('/proc/stat');
        return array_slice(preg_split('/\s+/', trim(array_shift($file))), 1);
    }

    public function getUptime()
    {
        if (!($str = @file('/proc/uptime'))) {
            return false;
        }

        $zh = (substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) === 'zh');

        $uptime = '';
        $str = explode(' ', implode('', $str));
        $str = trim($str[0]);
        $min = $str / 60;
        $hours = $min / 60;
        $days = floor($hours / 24);
        $hours = floor($hours - ($days * 24));
        $min = floor($min - ($days * 60 * 24) - ($hours * 60));
        $duint = !$zh ? (' day'. ($days > 1 ? 's ':' ')) : '天';
        $huint = !$zh ? (' hour'. ($hours > 1 ? 's ':' ')) : '小时';
        $muint = !$zh ? (' minute'. ($min > 1 ? 's ':' ')) : '分钟';

        if ($days !== 0) {
            $uptime = $days.$duint;
        }
        if ($hours !== 0) {
            $uptime .= $hours.$huint;
        }
        $uptime .= $min.$muint;

        return $uptime;
    }


    public function getMeminfo()
    {
        $info = array();

        if (!($str = @file('/proc/meminfo'))) {
            return false;
        }

        $str = implode('', $str);
        preg_match_all("/MemTotal\s{0,}\:+\s{0,}([\d\.]+).+?MemFree\s{0,}\:+\s{0,}([\d\.]+).+?Cached\s{0,}\:+\s{0,}([\d\.]+).+?SwapTotal\s{0,}\:+\s{0,}([\d\.]+).+?SwapFree\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buf);
        preg_match_all("/Buffers\s{0,}\:+\s{0,}([\d\.]+)/s", $str, $buffers);

        $info['memTotal'] = round($buf[1][0]/1024, 2);
        $info['memFree'] = round($buf[2][0]/1024, 2);
        $info['memBuffers'] = round($buffers[1][0]/1024, 2);
        $info['memUsed'] = $info['memTotal']-$info['memFree'];
        $info['memCached'] = round($buf[3][0]/1024, 2);
        $info['memRealUsed'] = $info['memTotal'] - $info['memFree'] - $info['memCached'] - $info['memBuffers'];
        $info['memRealFree'] = $info['memTotal'] - $info['memRealUsed'];
        $info['swapTotal'] = round($buf[4][0]/1024, 2);
        $info['swapFree'] = round($buf[5][0]/1024, 2);
        $info['swapUsed'] = round($info['swapTotal']-$info['swapFree'], 2);
        $info['memPercent'] = (floatval($info['memTotal'])!=0)?round($info['memUsed']/$info['memTotal']*100,2):0;
        $info['memCachedPercent'] = (floatval($info['memCached'])!=0)?round($info['memCached']/$info['memTotal']*100,2):0;
        $info['memRealPercent'] = (floatval($info['memTotal'])!=0)?round($info['memRealUsed']/$info['memTotal']*100,2):0;
        $info['swapPercent'] = (floatval($info['swapTotal'])!=0)?round($info['swapUsed']/$info['swapTotal']*100,2):0;

        foreach ($info as $key => $value) {
            if (strpos($key, 'Percent') > 0) {
                continue;
            } if ($value < 1024) {
                $info[$key] .= 'M';
            } else {
                $info[$key] = round($value/1024, 3) . ' G';
            }
        }

        return $info;
    }


    public function getLoadavg()
    {
        if (!($str = @file('/proc/loadavg'))) {
            return false;
        }

        $str = explode(' ', implode('', $str));
        $str = array_chunk($str, 4);
        $loadavg = implode(' ', $str[0]);

        return $loadavg;
    }


    public function getDiskinfo()
    {
        $info = array();

        $info['diskTotal'] = round(@disk_total_space('.')/(1024*1024*1024),3);
        $info['diskFree'] = round(@disk_free_space('.')/(1024*1024*1024),3);
        $info['diskUsed'] = $info['diskTotal'] - $info['diskFree'];
        $info['diskPercent'] = 0;
        if (floatval($info['diskTotal']) != 0) {
            $info['diskPercent'] = round($info['diskUsed']/$info['diskTotal']*100, 2);
        }
        return $info;
    }


    public function getHostname()
    {
        return gethostname();
    }


 }
