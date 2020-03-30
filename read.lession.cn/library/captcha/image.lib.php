<?php
/**
 * 图形验证码管理
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-12-18
 *
 */

class captcha_image_lib
{


    const CAPTCHA_SALT = 'hshf73';


    /**
     * 创建一个图形验证码值
     */
    public static function build($randType = 10, $option = [])
    {
        $randType || $randType = 10;

        $code = strtoupper(lib_util_func::randString(4, $randType, '123456789'));

        $idhash = md5($code . self::CAPTCHA_SALT);
        $libPath = KIS_APPLICATION_ROOT . '/library/captcha/seccode/';

        require $libPath . 'Seccode.class.php';

        $seccode = new \Seccode();
        $seccode->root_path = $libPath;
        //验证码内容
        $seccode->code = $code;
        //验证码类型,0:英文图片、1：中文图片、2：Flash 验证码、3：语音验证码、4：位图验证码
        $seccode->type = 2;
        //验证码宽度
        $seccode->width = 100;
        //验证码高度
        $seccode->height = 30;
        //随机图片背景
        $seccode->background = 0;
        //随机背景图形
        $seccode->adulterate = 1;
        //验证码
        $seccode->ttf = 1;
        //随机倾斜度
        $seccode->angle = 0;
        //随机颜色
        $seccode->color = 1;
        //随机大小
        $seccode->size = 0;
        //文字阴影
        $seccode->shadow = 1;
        //GIF 动画
        $seccode->animator = 0;
        //随机扭曲
        $seccode->warping = 0;
        //字体包路径
        $seccode->fontpath = $libPath . 'font/';
        //背景图片、字体、声音等文件路径
        $seccode->datapath = $libPath;
        //依赖类库目录
        $seccode->includepath = $libPath;

        if ($option && is_array($option)) {
            foreach ($option as $key => $value) {
                $seccode->$key = $value;
            }
        }

        //输出验证码图片
        $seccode->display();

        $seccodeSessionName = config::get('app.image_captcha_name', false);
        $_SESSION[$seccodeSessionName] = $idhash;

        return $idhash;
    }

    /**
     * 检查输入图形验证码是否正确
     * @param  string $idhash md5验证码
     * @return bool
     */
    public static function check($inputcode)
    {
        if (!$inputcode) {
            return false;
        }
        // 图片验证码session 名称
        $seccodeSessionName = config::get('app.image_captcha_name', false);

        $code = $_SESSION[$seccodeSessionName] ?? '';

        if (!$code) {
            return false;
        }

        $inputcode = md5(strtoupper($inputcode) . self::CAPTCHA_SALT);

        if ($code == $inputcode) {
            return true;
        }

        return false;
    }


}
