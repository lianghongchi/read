<?php

$request = getInstance('lib_app_request');
$scheme = $request->scheme();
$host = $request->host();

$adminSite   = $scheme . '://' . $host;

return [

    // 后台管理
	'admin' => $adminSite,

];
