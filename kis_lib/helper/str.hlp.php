<?php

/**
 * 字符串公用操作类
 * @author dianjiemian
 * @version 1.0
 * @created 2018-11-21 17:04:07
 */
class hlp_str {
	/**
	 * 把字符串转成安全字符串
	 *
	 * @param	string	$str	要转的字符串
	 * @return	string			返回经处理的字符串
	 */
	public static function safe($str = '')
	{
		$str	= htmlspecialchars_decode($str, ENT_QUOTES);
		$str	= htmlspecialchars($str, ENT_QUOTES);
		$str	= str_replace('&amp;', '&', $str);
		return $str;
	}

	/**
	 * 获取前多少个字符
	 *
	 */
	public static function cutchar($str, $num, $ext = '..', $charset = 'utf-8')
	{
		$retval		= mb_substr($str, 0, $num, $charset);
		$retval		= strlen($retval) < strlen($str) ? $retval.$ext : $str;
		return $retval;
	}

	/**
	 * 获取前多少个字节
	 *
	 */
	public static function cut($str, $num, $ext = '..', $charset = 'utf-8')
	{
		$retval		= mb_strcut($str, 0, $num, $charset);
		$retval		= strlen($retval) < strlen($str) ? $retval.$ext : $str;
		return $retval;
	}

	/**
	 * 转成utf8数组
	 *
	 * @param	array	$array	要转化的数据
	 * @return					无返回值，直接对该数组转换
	 */
	public static function utf8(&$array)
	{
		$array		= is_array($array) ? $array : array();
		foreach($array as $key => $value)
		{
			if(!is_array($value))
			{
				$array[$key]	= mb_convert_encoding($value, 'utf-8', 'gbk');
			}
			else
			{
				self::utf8($array[$key]);
			}
		}
	}

	/**
	 * 转成gbk数组
	 *
	 * @param	array	$array	要转化的数据
	 * @return					无返回值，直接对该数组转换
	 */
	public static function gbk(&$array)
	{
		$array		= is_array($array) ? $array : array();
		foreach($array as $key=>$value)
		{
			if(!is_array($value))
			{
				$array[$key]	= mb_convert_encoding($value, 'gbk', 'utf-8');
			}
			else
			{
				self::gbk($array[$key]);
			}
		}
	}

	/**
	 * 字符串转数字
	 *
	 * @param	string	$str	要转换的字符串
	 * @return	int				返回无符号型整型数字，即unsigned int
	 */
	public static function str2int($str = '')
	{
		//如果无参数则返回0
		$str		= trim($str);
		if($str == '')
		{
			return 0;
		}

		//散列成数字
		$crc		= crc32($str);

		//转成unsigned并返回
		$uncrc		= sprintf('%u', $crc);
		return $uncrc;
	}

    /**
     * 过滤掉 emoji 表情 并返回 emoji 数量
     *
     * @param $str
     * @return mixed
     */
    public static function filterEmoji($str)
    {
        $count = 0;
        $reg = "/./u";
        $str = preg_replace_callback(
            $reg,
            function (array $match) use (&$count) {
                if (strlen($match[0]) >= 4) {
                    $count++;

                    return '';
                } else {
                    return $match[0];
                }
            },
            $str);

        return [$str, $count];
    }

    /**
     * 把用户输入的文本转义
     *（主要针对特殊符号和emoji表情）
     *
     * @param $str
     * @return mixed|string
     */
    public static function emojiEncode($str)
    {
        if (!is_string($str)) {
            return $str;
        }
        if (!$str || $str == 'undefined') {
            return '';
        }

        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i", function ($str) {
            return addslashes($str[0]);
        }, $text);

        //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
        return json_decode($text);
    }


    /**
     * 解码上面的转义
     *
     * @param $str
     * @return mixed
     */
    public static function emojiDecode($str)
    {
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback('/\\\\\\\\/i', function ($str) {
            return '\\';
        }, $text);

        //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }

    public static function removeEmoji($str)
    {
        $str = preg_replace_callback('/./u', function (array $match) {
            return strlen($match[0]) >= 4 ? '' : $match[0];
        }, $str);

        return $str;
    }
}
