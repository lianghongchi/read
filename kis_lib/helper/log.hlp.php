<?php
/**
 * 系统日志，给文件名自动添加日期前缀
 * 数据全部放在/tmp下
 * hlp_log::log('a.txt','I love nextjoy.com',true);
 * 文件的绝对路径是：/tmp/a.txt.20140728
 * 文件名全部转小写后，只保留数字，字母，下划线，点
 * 第三个参数=false时不自动换行
 */
class hlp_log {
    /**
     *
     * @param string $fileName 文件名
     * @param string $content 日志内容
     * @param bool $isAutoWrap 是否自动添加\n换行，true时自动添加
     */
    public static function log($fileName, $content, $isAutoWrap = true) {
        $fileName = substr(strtolower(preg_replace("/[^a-zA-Z0-9_.]/i", "", $fileName)), 0, 128);
        if ($fileName == '') {
            $fileName = 'default.log';
        }
        $filePath = '/tmp/' . $fileName . '.' . date("Ymd");
        $isAutoWrap && $content .= "\n";
        $handle = fopen($filePath, 'a');
        $now = "[" . date("Y-m-d H:i:s") . "]";//直接添加时间
        fwrite($handle, $now . $content);
        fclose($handle);
    }
}
