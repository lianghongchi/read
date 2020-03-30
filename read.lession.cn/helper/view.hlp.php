<?php
/**
 * 自动区分PC/移动端加载不同view
 *
 * @package		KIS
 * @author		塑料做的铁钉<ring.msn@gmail.com>
 * @since		2016-01-23
 * @example
 *
 */

class view_hlp{

	
	
	public static function load($view, $params = array(), $output = true, $public_view = false){
		return self::make($view, $params, $output, $public_view);
	}

	public static function make($view, $params = array(), $output = false, $public_view = false){

		if(IS_MOBILE) {
			$prefix_path = 'view_mobile' . DS;
		} else {
			$prefix_path = 'view_pc' . DS;
		}
		return View::make($prefix_path . $view, $params, $output, $public_view);
	}
}