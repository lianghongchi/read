<?php
/**
 * Model类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class model
{

	/**
	 * model计数器，每个model被new的次数
	 * @var array
	 */
	public static $counter = array();

	/**
	 * 构造函数，会计数
	 * @param string $argv 预留参数
	 */
	public function __construct($argv = null){
		self::$counter[get_class($this)] ++;
	}

	/**
	 * 析构函数，可以计时
	 */
	public function __destruct(){

	}
}
