<?php

class lib_util_dbc2sbc
{




    public static function unicode2char($unicode)
    {
        if($unicode < 128) {
            return chr($unicode);
        }

        if($unicode < 2048) {
            return chr(($unicode >> 6) + 192) . chr(($unicode & 63) + 128);
        }

        if($unicode < 65536) {
            return chr(($unicode >> 12) + 224) . chr((($unicode >> 6) & 63) + 128) . chr(($unicode & 63) + 128);
        }

        if ($unicode < 2097152) {
            return chr(($unicode >> 18) + 240) .
                   chr((($unicode >> 12) & 63) + 128) .
                   chr((($unicode >> 6) & 63) + 128) .
                   chr(($unicode & 63) + 128);
        }
        return false;
    }


    public static function char2unicode($char)
    {
         switch (strlen($char)) {
             case 1 :
                return ord($char);

             case 2 :
                return (ord($char{1}) & 63) | ((ord($char{0}) & 31) << 6);

             case 3 :
                return (ord($char{2}) & 63) | ((ord($char{1}) & 63) << 6) | ((ord($char{0}) & 15) << 12);

             case 4 :
                return (ord($char{3}) & 63) |
                        ((ord($char{2}) & 63) << 6) |
                        ((ord($char{1}) & 63) << 12) |
                        ((ord($char{0}) & 7)  << 18);

             default :
                 return false;
        }
    }

    public static function sbc2dbc($str)
    {
        return preg_replace(
            // 全角字符
            '/[\x{3000}\x{ff01}-\x{ff5f}]/ue',
            // 编码转换
            // 0x3000是空格，特殊处理，其他全角字符编码-0xfee0即可以转为半角
            '($unicode=char2Unicode(\'\0\')) == 0x3000 ? " " : (($code=$unicode-0xfee0) > 256 ? unicode2Char($code) : chr($code))',
            $str
        );
    }

    function dbc2sbc($str)
    {
        return preg_replace(
            // 半角字符
            '/[\x{0020}\x{0020}-\x{7e}]/ue',
            // 编码转换
            // 0x0020是空格，特殊处理，其他半角字符编码+0xfee0即可以转为全角
            '($unicode=char2Unicode(\'\0\')) == 0x0020 ? unicode2Char（0x3000） : (($code=$unicode+0xfee0) > 256 ? unicode2Char($code) : chr($code))',
            $str
        );
    }


}
