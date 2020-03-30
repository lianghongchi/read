<?php

// 后台管理菜单列表

return array (


    // 首页
    [
        'name'       => '首页',
        // 一级菜单可设置 icon小图标，填空为默认图标
        // 图标请参考 layui文档图标部分
        'icon'       => 'layui-icon-home',
        'controller' => 'index',
        'action'     => 'home',
    ],
    // admin :
    [
        'name'       => '系统管理',
        'icon'       => 'layui-icon-set',
        'controller' => [
            [
                'name'       => '管理员设置',
                'controller' => [
                    [
                        'name'       => '管理员总列表',
                        'controller' => 'admin_admin',
                        'action'     => 'index',
                    ],
                ],
            ],
            [
                'name'   => '系统日志',
                'controller' => [
                    [
                        'name'       => '管理员操作日志',
                        'controller' => 'admin_log_action',
                        'action'     => 'index'
                    ],
                    [
                        'name'       => '管理员登录日志',
                        'controller' => 'admin_log_login',
                        'action'     => 'index'
                    ]
                ]
            ],
            [
                'name' => '菜单设置',
                'controller' => [
                    [
                        'name'       => '菜单列表',
                        'controller' => 'menu_menu',
                        'action'     => 'index',
                    ],
                ],
            ]
        ],
    ],
    // admin


    // 开发者模式:
    is_dev() ? [
        'name'       => '开发者管理',
        'icon'       => 'layui-icon-component',
        'controller' => [
            [
                'name'   => '页面组件示例',
                'controller' => 'develop_template',
                'action' => 'index',
            ],
        ],
    ] : null,

);
