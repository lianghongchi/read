<?php
/**
 * 英语--单词模块
 *
 * @authName    英语--单词模块
 * @note        英语--单词模块
 * @package     KIS
 * @author      Freedom
 * @since       2019-09-11
 * 管理单词的模块
 */
class english_word_info_controller extends controller_lib {
    /**
     * 单词页面
     */
    public function index_action() {
        $word = input('word', '');
        //根据word查询
        $pageRow = 0;
        $pageRow || $pageRow = input('pagerows', 0);
        $pageRow < 1 && $pageRow = 30;
        $pageRow > 200 && $pageRow = 200;
        $count = lib_english_model_operation::searchWordCount($word);
        $page = $this->_getPageData($pageRow, $count);
        $list = lib_english_model_operation::searchWordList($word, $page['start'], $pageRow);
        $data = [
            'word' => $word,
            'list' => $list,
        ];
        $this->_assign($data);
        $this->_render();
    }
}