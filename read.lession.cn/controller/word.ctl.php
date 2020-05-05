<?php
class word_controller extends controller_lib {

    /**
     * 是否需要登录账号
     * @var bool
     */
    protected $_isLoginRequired = false;

    /**
     * 是否加入权限控制
     * @var bool
     */
    protected $_isAuthControl = false;

    public function index_action() {
        lib_word_operation::operation();
    }
}