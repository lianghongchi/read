<?php
/**
 * 首页 控制器
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-17
 *
 */

class authtoken_controller extends controller_lib
{


    /**
     * 不需要登录
     * @var bool
     */
    protected $_isLoginRequired = false;

    /**
     * 是否加入权限控制
     * @var bool
     */
    protected $_isAuthControl = false;

    /**
     * 管理员登录
     * @return
     */
    public function image_action()
    {
        $option = [];
        $height = input('height', 0, 'intval');
        $width = input('width', 0, 'intval');
        $isText = input('is_text', 0, 'intval');

        $height && $option['height'] = $height;
        $width && $option['width'] = $width;

        $randType = 10;

        if ($isText) {
            $randType = 1;
        }

        captcha_image_lib::build($randType, $option);
    }


}
