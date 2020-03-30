<?php
/**
 * 配置文件类
 *
 * @package     NDS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class Config
{

	/**
	 * 从文件加载出来的配置，保存起来，防止重复加载文件
	 * @var array
	 */
	public static $item = array();

	/**
	 * 从文件加载出来的配置，保存起来，防止重复加载文件
	 * @var array
	 */
	public static $item_channel = array();

	/**
	 *读取配置文件中的变量
	 * Config::get('memcached.g1');//读取前缀名喂memcached的配置文件中 返回数组中key为['memcached']['g1']的值，可递增，点号分开
	 * Config::get('memcached.g1.0.1')// 同上返回 $array['memcached']['g1'][0][1]
	 * @var string   由点号(.)分隔的配置  第一部分同时作为配置文件的前缀名，以便自动加载
	 */
	public static function get($config, $public_config = true, $ignore_not_found = false){
		static::parse($config, $public_config, $ignore_not_found );
		// var_dump(static::$item);
		return static::getByKey($config, $public_config);
	}

	/**
	 * 加载频道目录下的config文件
	 * @param  [type] $config [description]
	 * @return [type]         [description]
	 */
	public static function load($config, $ignore_not_found = false){
		return static::get($config, false, $ignore_not_found );
	}


	/**
	 * 解析配置文件，然后保存在static::$item变量中，若已解析则不需要重复解析
	 */
	protected static function parse($config, $public_config = true, $ignore_not_found = false){
		$config_info = explode('.', $config);
		if($public_config){
			$item_key_prefix = 'pub:';
		} else {
			$item_key_prefix = '';
		}
		if(empty(static::$item[$item_key_prefix.$config_info[0]])) {
			// var_dump($config_info);
			static::$item[$item_key_prefix.$config_info[0]] = loader::loadConfig($config_info[0], $public_config, $ignore_not_found);
			return $config_info[0];
		}
	}


	/**
	 * 获取static::$item变量中对应的数组值，对应关系同static::get()
	 * $key = 'memcached.g1.host'    返回 static::$item['memcached']['g1']['host']
	 */
	protected static function getByKey($key, $public_config = true){
		foreach (explode('.', $key) as $segment) {

			if(empty($array)){
				if($public_config){
					$item_key_prefix = 'pub:';
				} else {
					$item_key_prefix = '';
				}
				$array = self::$item[$item_key_prefix.$segment];
				continue;
			}
			//var_dump($segment,$array,self::$item[$segment]);
			if ( ! is_array($array) or ! array_key_exists($segment, $array))
			{
				static::trigger_error(E::EC_FW_CONFIG_NF, $key);
				return false;
			}
			$array = $array[$segment];
		}
		return $array;
	}

	/**
	 * 内部错误触发器
	 * @param  int $errno 错误码
	 * @param  string $error 错误信息
	 * @return null
	 */
	protected static function trigger_error($errno, $error ='') {
		E::trigger($errno, $error);
	}
}
