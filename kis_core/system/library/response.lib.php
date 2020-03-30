<?php
/**
 * http 响应类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class Response
{

	/**
	 * HTTP Status codes
	 * @var array
	 */
	static public $statusTexts = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Reserved',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',  // RFC4918
        423 => 'Locked',                // RFC4918
        424 => 'Failed Dependency',     // RFC4918
        425 => 'Reserved for WebDAV advanced collections expired proposal',   // RFC2817
        426 => 'Upgrade Required',      // RFC2817
        428 => 'Precondition Required', // RFC-nottingham-http-new-status-04
        429 => 'Too Many Requests',     // RFC-nottingham-http-new-status-04
        431 => 'Request Header Fields Too Large',   // RFC-nottingham-http-new-status-04
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates (Experimental)', // [RFC2295]
        507 => 'Insufficient Storage',  // RFC4918
        508 => 'Loop Detected',         // RFC5842
        510 => 'Not Extended',          // RFC2774
        511 => 'Network Authentication Required',   // RFC-nottingham-http-new-status-04
    );

    /**
     * 构造函数一下
     * @param string  $content [description]
     * @param integer $status  [description]
     * @param array   $headers [description]
     */
	public function __construct($content = '', $status = 200, $headers = array())
    {
        $this->setStatusCode($status);
    }

    /**
     * 返回或输出JSON或JSONP  有$_GET['callback']时返回jsonp
     * @param  integer $errno 状态码 默认0
     * @param  string  $error 状态描述 默认空
     * @param  [type]  $data  数据 默认空数组
     * @param  boolean $exit  是否exit结束进程 默认true
     * @return [type]         [description]
     */
    public static function jsonp($code = 200, $msg = '', $data = [], $exit = true){
        $response = json_encode(['code' => $code, 'msg' => $msg, 'data'=>$data]);
        if(isset($_GET['callback']) && $_GET['callback']) {
            $callback = htmlentities($_GET['callback'], ENT_QUOTES);
            echo $callback . '(' . $response . ');';
        } else {
            header('Content-type:application/json');
            echo $response;
            //die;
        }
        if($exit) {
            Router::finish();
            exit();
        }
    }

    /**
     * 设置状态码
     * @param [type] $status [description]
     */
    public function setStatusCode($status){
    	$this->statusCode = $status;
    	$this->statusText = self::$statusTexts[$this->statusCode];
    }

    /**
     * 触发返回给浏览器错误
     * @param  integer $errno 错误码 一般是HTTP状态码
     * @param  string  $msg   错误信息
     * @return null
     */
	public static function error($errno = 404, $msg = ''){
		$res = new Response('',$errno);
		$res->sendHeader();
		switch ($errno) {
			case '301':
			case '302':
				break;
			case '404':
				//TODO 404 view here...
                // echo '404 Not Found.' . $msg;
				break;

			default:
				# code...
				break;
		}

	}

    /**
     * 重定向
     * @param string $url        重定向URL
     * @param string $statusCode HTTP重定向状态码，默认301  或302
     */
	public static function Redirect($url, $statusCode = '301'){
		$res = new Response($statusCode);
		$res->sendHeader();
		header("Location:{$url}");
	}

    /**
     * header给浏览器头部信息
     * @return null
     */
	public function sendHeader(){
        $str = sprintf('HTTP/%s %s %s', '1.0', $this->statusCode, $this->statusText);//header('HTTP/1.1 404 Not Found');
		header($str);
	}
}
