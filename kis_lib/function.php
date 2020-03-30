<?php

/**
 * 调试打印
 * @param  mix $var 需要打印的数据
 * @param  boolean $echo 是否直接输出，否则返回字符串
 * @return string 格式化内容
 */
function d($var, $echo = true)
{
    ob_start();
    var_dump($var);
    $output = ob_get_clean();
    $output = preg_replace('/\]\=\>\n(\s+)/m', '] => ', $output);

    if ('cli' == PHP_SAPI) {
        $output = PHP_EOL . $output . PHP_EOL;
    } else {
        $output = htmlspecialchars($output, ENT_QUOTES);
        $output = '<pre>' . $output . '</pre>';
    }

    if ($echo) {
        echo ($output);
        return null;
    }

    return $output;
}

/**
 * 统一接口格式
 * @param  int $code 操作代号
 * @param  string $msg 提示消息
 * @param  mix $data 数据
 * @return array 格式化数据
 */
function return_format($code, $msg = '', $data = null)
{
    return ['code' => $code, 'msg' => $msg, 'data' => $data];
}

/**
 * 获取类的实例，会自动添加为单例
 * @param  string $name 类名称
 * @return objec 单例
 */
function getInstance($name)
{
    return lib_app::getInstance($name);
}

/**
 * xml转 array
 * @param  string $xml xml字符串
 * @return array 数组
 */
function xml2array($xml)
{
    $res = @simplexml_load_string($xml, NULL, LIBXML_NOCDATA);
    $res = json_decode(json_encode($res), true);
    return $res;
}

/**
 * xss移除
 * @param  string $val 需要移除的字符串
 * @return string 处理后的字符串
 */
function remove_xss($val)
{
    return lib_util_safe::removeXss($val);
}

/**
 * 当前是否debug模式
 * @return boolean 是否debug模式
 */
function is_debug()
{
    return Controller::debug() ? true : false;
}

/**
 * 当前是否开发模式
 * @return boolean 是否开发模式
 */
function is_dev()
{
    return KIS_ENV == 'DEV' ? true : false;
}

/**
 * URL参数添加/追加
 * @param  string $url URL
 * @param  array $parameters 参数
 * @return string URL
 */
function url_append_parameter($url, array $parameters)
{
    $delimiter = strpos($url, '?') === false ? '?' : '&';
    return $url . $delimiter . http_build_query($parameters);
}

// 是否身份证号
function is_idcard($idcard)
{
    return helper('user\\Support::isIdcard', $idcard);
}

// 是否url
function is_url($url)
{
    return (bool)filter_var($url, FILTER_VALIDATE_URL);
}

// 是否email
function is_email($email)
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

// 是否手机号
function is_cellphone($celephone)
{
    return preg_match("/^(13|14|15|16|17|18|19)[0-9]{9}$/", $celephone);
}

// 是否银行卡(Luhn算法)
function is_bankcard($bankcard)
{
    $res = '';
    foreach(array_reverse(str_split($bankcard)) as $i => $c ) {
        $res .= ($i % 2 ? $c * 2 : $c );
    }
    return array_sum(str_split($res)) % 10 == 0;
}

/**
 * 获取http输入字段
 * @param  string $name 参数名称
 * @param  mix $default 若参数未定义
 * @param  string $filterFunc 可选过滤函数
 * @return mix 返回的数据
 */
function input($name, $default = null, $filterFunc = null)
{
    return getInstance('lib_app_request')->input($name, $default, $filterFunc);
}

// 是否get请求
function is_get()
{
    return getInstance('lib_app_request')->isGet();
}

// 是否post请求
function is_post()
{
    return getInstance('lib_app_request')->isPost();
}

// 是否put请求
function is_put()
{
    return getInstance('lib_app_request')->isPut();
}

// 是否delete请求
function is_delete()
{
    return getInstance('lib_app_request')->isDelete();
}

// 是否options请求
function is_options()
{
    return getInstance('lib_app_request')->isOptions();
}

/**
 * 获取redis实例
 * @param  string $group 集群名称
 * @return objec 单例
 */
function redis($group = 'default')
{
    return lib_cache_redis::getInstance($group);
}

// 获取当前时间，将缓存，再次获取时间不变
function get_time()
{
    return lib_app::getTime();
}

/**
 * 获取客户端IP
 * @return string IP地址
 */
function get_ip()
{
    return getInstance('lib_app_request')->ip();
}

// 获取request
function get_request()
{
    return getInstance('lib_app_request');
}

/**
 * 发送错误日志到服务器
 * @param  string $fileName 日志名称
 * @param  string | array $msg 保持的数据，（可以是数组）
 * @return bool 保存结果
 */
function send_error_log($fileName, $msg)
{
    return E::sendErrorLog($fileName, $msg);
}

/**
 * 从一个数组中提取自定义键值对
 * @param  array $data 源数据
 * @param  array $fields 需要提取的键值
 * @param  boolean $isReverse 是否反向提取
 * @return array 提取的数组
 */
function get_data_by_filter($data, $fields, $isReverse = false)
{
    if (!$fields || !is_array($fields)) {
        return $data;
    }
    $fieldsArray = [];

    foreach ($fields as $key => $value) {
        $fieldsArray[$value] = true;
    }

    if ($isReverse) {
        return array_diff_key($data, $fieldsArray);
    }

    return array_intersect_key($data, $fieldsArray);
}

/**
 * 是否命令行模式
 * @return boolean
 */
function is_cli()
{
    return 'cli' == PHP_SAPI;
}
