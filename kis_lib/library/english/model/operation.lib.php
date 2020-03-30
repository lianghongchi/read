<?php
/**
 * Created by PhpStorm.
 * User: freedom
 * Date: 2019/9/10
 * Time: 16:37
 */
class lib_english_model_operation {
    /**\
     * @param String $word
     * @param int $offset
     * @param int $limit
     * @return array
     * 查询单词列表
     */
    public static function searchWordList(String $word, int $offset = 0, int $limit = 0) {
        $where = '';
        if(!empty($word)) {
            $where .= "word like '%". $word."%' AND";
        }
        $list = lib_english_dao_word::searchWordListByWhere($where, $offset, $limit);
        return $list;
    }

    /**
     * @param String $word
     * @return mixed
     * 查询单词总数
     */
    public static function searchWordCount(String $word) {
        $where = '';
        if(!empty($word)) {
            $where .= "word like '%". $word."%' AND";
        }
        $count = lib_english_dao_word::searchWordCountByWhere($where);
        return $count;
    }
}