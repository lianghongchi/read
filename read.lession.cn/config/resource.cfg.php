<?php

$request = getInstance('lib_app_request');

$resUrl = $request->scheme().'://' . $request->host();

return [

    // 后台管理 静态资源URL
    'admin_static' => $resUrl . '/static/ss002',

];
