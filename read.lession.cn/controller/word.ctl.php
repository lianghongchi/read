<?php
class word_controller extends controller_lib {

    public function index_action() {
        lib_word_operation::operation();
    }
}