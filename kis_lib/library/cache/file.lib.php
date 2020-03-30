<?php
/**
 * 文件缓存
 * 单例
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_cache_file
{



    // 缓存是否可用
    private $available = false;

    // 缓存存储位置
    private $path      = '/tmp/kis_file_cache/';

    // 默认缓存生存时间
    private $expire    = 8640000;



    public function __constract()
    {
        if (!is_dir($this->path)) {
            if (!mkdir($this->path, 0777, true)) {
                return ;
            }
        }

        $this->available = true;
    }


    private function getFileName($name)
    {
        return $this->path.$name;
    }

    public function get($name)
    {
        $filename = $this->getFileName($name);

        if (!($this->available && is_file($filename))) {
            return false;
        }

        $content = file_get_contents($filename);

        if( false !== $content) {
            $expire = (int)substr($content, 0, 12);

            if ($expire != 0 && time() > filemtime($filename) + $expire) {
                unlink($filename);
                return false;
            }
            $content = substr($content,12);
            $content = unserialize($content);
            return $content;
        }
        else
        {
            return false;
        }
    }

    public function set($name, $value, $expire = null)
    {
        is_null($expire) && $expire = $this->expire;

        $filename = $this->getFileName($name);
        $serdata  = serialize($value);

        $fileDir  = dirname($filename);
        if (!is_dir($fileDir)) {
            if (!mkdir($fileDir, 0777, true)) {
                return false;
            }
        }

        $serdata  = sprintf('%012d',$expire).$serdata;
        $result   = file_put_contents($filename,$serdata);

        if($result) {
            clearstatcache();
            return true;
        }

        return false;
    }

    public function del($name)
    {
        return unlink($this->getFileName($name));
    }

    public function clear()
    {
        return del_dir($this->path);
    }

}
