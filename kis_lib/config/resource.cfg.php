<?php

/**
 * 应用静态资源URL配置
 */

$request = getInstance('lib_app_request');

$resUrl = $request->scheme().'://' . $request->host();

return [

    'public_static' => $resUrl . '/static/public',

];
