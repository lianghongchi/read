<?php

/**
 * 应用URL配置
 */

$request  = getInstance('lib_app_request');
$scheme   = $request->scheme();
$mainHost = $request->mainHost();

$mobileSite   = $scheme . '://m.' . $mainHost;
$apiSite      = $scheme . '://api.' . $mainHost;
$passportSite = $scheme . '://passport.' . $mainHost;

return [

    // 移动端
	'mobile'   => $mobileSite,
    // api
    'api'      => $apiSite,
    // 账号中心
    'passport' => $passportSite,

];
