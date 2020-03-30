<?php
/**
 * 微信登录接口
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-28
 *
 */

class lib_util_fopen
{


    /**
     * fopen 打开URL
     */
    public static function open($url, $params = [], $isPost = true, $timeout = 15, $block = true)
    {
        $limit = 500000;

        $return = '';
        $cookie = '';

        $matche = parse_url($url);

        $scheme = $matche['scheme'] ?? '';
        $host = $matche['host'] ?? '';
        $path = $matche['path'] ?? '/';
        $port = $matche['port'] ?? 80;

        $params = http_build_query($params);

        if ($isPost) {
            $out = "POST {$path} HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
            $header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
            $header .= "Host: {$host}\r\n";
            $header .= 'Content-Length: '.strlen($params)."\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cache-Control: no-cache\r\n";
            $header .= "Cookie: {$cookie}\r\n\r\n";
            $out .= $header.$params;
        } else {
            $path = "{$path}?{$params}";

            $out  = "GET {$path} HTTP/1.0\r\n";
            $header = "Accept: */*\r\n";
            $header .= "Accept-Language: zh-cn\r\n";
            $header .= "User-Agent: {$_SERVER['HTTP_USER_AGENT']}\r\n";
            $header .= "Host: {$host}\r\n";
            $header .= "Connection: Close\r\n";
            $header .= "Cookie: {$cookie}\r\n\r\n";
            $out .= $header;
        }

        $fpflag = 0;

        if (!$fp = self::fsocketopen($host, $port, $errno, $errstr, $timeout)) {
            $context = array(
                'http' => array(
                    'method'  => $post ? 'POST' : 'GET',
                    'header'  => $header,
                    'content' => $post ? : '',
                    'timeout' => $timeout,
                ),
            );
            $context = stream_context_create($context);
            $fp = @fopen("{$scheme}://{$host}:{$port}{$path}", 'b', false, $context);
            $fpflag = 1;
        }

        if (!$fp) {
            return return_format(502, '数据请求失败', [$errno, $errstr]);
        }

        stream_set_blocking($fp, $block);
        stream_set_timeout($fp, $timeout);
        @fwrite($fp, $out);

        $status = stream_get_meta_data($fp);

        if (!$status['timed_out']) {
            while (!feof($fp) && !$fpflag) {
                if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
                    break;
                }
            }

            $stop = false;
            while (!feof($fp) && !$stop) {
                $data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
                $return .= $data;
                if ($limit) {
                    $limit -= strlen($data);
                    $stop = $limit <= 0;
                }
            }
        }

        @fclose($fp);

        return return_format(200, 'OK', $return);
    }


    private static function fsocketopen($host, $port = 80, &$errno, &$errstr, $timeout = 15)
    {
        $fp = '';

        if(function_exists('fsockopen')) {
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        } else if (function_exists('pfsockopen')) {
            $fp = @pfsockopen($host, $port, $errno, $errstr, $timeout);
        } else if (function_exists('stream_socket_client')) {
            $fp = @stream_socket_client($host.':'.$port, $errno, $errstr, $timeout);
        }
        return $fp;
    }



    public static function curl($url, $params = '', $isPost = true, $option = null, $headers = null)
    {
        // 初始化curl
        $ch = curl_init();

        $curlOpts = [
            // 在发起连接前等待的时间，如果设置为0，则无限等待
            CURLOPT_CONNECTTIMEOUT  => 5,
            // cURL允许执行的最长秒数
            CURLOPT_TIMEOUT         => 8,
            // 在HTTP请求中包含一个"User-Agent: "头的字符串
            // CURLOPT_USERAGENT       => 'xiangmoo-apiclient-php-2.0',
            // CURL_HTTP_VERSION_NONE (默认值，让cURL自己判断使用哪个版本)，CURL_HTTP_VERSION_1_0 (强制使用 HTTP/1.0)或CURL_HTTP_VERSION_1_1 (强制使用 HTTP/1.1)
            CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
            // 赋值至变量而不直接输出
            CURLOPT_RETURNTRANSFER  => true,
            // 启用时会将头文件的信息作为数据流输出
            CURLOPT_HEADER          => false,
            // 启用时会将服务器服务器返回的"Location: "放在header中递归的返回给服务器，使用CURLOPT_MAXREDIRS可以限定递归返回的数量
            CURLOPT_FOLLOWLOCATION  => false,
            // 是否POST请求
            CURLOPT_POST            => $isPost
        ];

        // headers
        if (is_array($headers)) {
            $curlOpts[CURLOPT_HTTPHEADER] = $headers;
        }

        // HTTPS
        // 禁用后cURL将终止从服务端进行验证。
        // 使用CURLOPT_CAINFO选项设置证书使用CURLOPT_CAPATH选项设置证书目录
        // 如果CURLOPT_SSL_VERIFYPEER(默认值为2)被启用，CURLOPT_SSL_VERIFYHOST需要被设置成TRUE否则设置为FALSE。
        if (stripos($url, 'https://') === 0) {
            $curlOpts[CURLOPT_SSL_VERIFYPEER] = false;
        }

        // GET请求, 组装http参数
        if ($isPost){
            $curlOpts[CURLOPT_URL] = $url;
            $curlOpts[CURLOPT_POSTFIELDS] = $params;
        } else {
            if (is_array($params)) {
                $query = http_build_query($params, '', '&');
                $delimiter = strpos($url, '?') === false ? '?' : '&';
                $url = $url . $delimiter . $query;
            }

            $curlOpts[CURLOPT_URL] = $url;
        }

        // 合并curl option
        if (is_array($option)) {
            foreach ($option as $key => $value){ $curlOpts[$key] = $value; }
        }

        curl_setopt_array($ch, $curlOpts);

        $result = curl_exec($ch);

        curl_close($ch);

        return $result;
    }



}
