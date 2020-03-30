<?php
/**
 * 管理员模型
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-22
 *
 */

class menu_menu_model extends lib_dborm_model
{


    /**
     * 数据库名称
     * @var string
     */
    protected $_database = 'admin';

    /**
     * 模型表名称
     * @var string
     */
    protected $_table = 't_menu';

    /**
     * 主键
     * @var int
     */
    protected $_pkey = 'id';

    /**
     * 菜单按级别排序
     */
    public function getSortMenu($list, $pid = 0)
    {
        $result = array();
        foreach ($list as $v) {
            if ($v['pid'] == $pid) {
                $info = getInstance('menu_menu_model')->where(['id' => $pid])->find();
                if (empty($info)) {
                    $infoTitle = '顶级菜单';
                } else {
                    $infoTitle = $info['title'];
                }
                $v['ptitle'] = $infoTitle;
                $v['items'] = $this->getSortMenu($list, $v['id']);
                $result[] = $v;
            }
        }
        array_multisort(array_column($result, 'sort'), SORT_DESC, $result);
        return $result;
    }

    public function getConMenu($list, $pid = 0)
    {
        $result = array();
        foreach ($list as $key => $v) {
            if ($v['pid'] == $pid) {
                $v['name'] = $list[$key]['title'];
                unset($v['title']);
                $controller = $this->getConMenu($list, $v['id']);
                $v['controller'] = empty($controller) ? $list[$key]['controller'] : $controller;
                $result[] = $v;
            }
        }
        array_multisort(array_column($result, 'sort'), SORT_DESC, $result);
        return $result;
    }
}
