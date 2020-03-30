<?php
/**
 * dh3t短信接口
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_sms_driver_dh3t
{


    /**
     * account
     * @var [type]
     */
    protected $account = 'dh47353';

    /**
     * API keuSecret
     * @var [type]
     */
    protected $password = '6qpQk2kW';

    /**
     * 阿里云短信签名
     * @var [type]
     */
    protected $signName = '【点界面】';


    /**
     * API 地址集合
     * @var [type]
     */
    protected $url = [
        // 单条短信发送URL
        'single' => 'http://www.dh3t.com/json/sms/Submit',
    ];



    public function __construct($isDev = false)
    {
        // 是否开发环境
        $this->isDev = $isDev;
        $this->password = md5($this->password);

        if ($this->isDev) {
            return ;
        }

        if (!$this->account) {
            throw new \Exception("SMS Dh3t Key Undefined");
        }

        if (!$this->password) {
            throw new \Exception("SMS Dh3t keySecret Undefined");
        }

        if (!$this->signName) {
            throw new \Exception("SMS Dh3t signName Undefined");
        }
    }




    /**
     * 发送短信
     * @param  string $cellphone 手机号
     * @param  string $content 短信模板
     * @param  array $parameter 参数
     * @return array 发送结果
     */
    public function sendSMS($cellphone, $content)
    {
        if (!$cellphone || !$content) {
            return return_format(400, '参数错误[2]');
        }

        $data = [
            'account'  => $this->account,
            'password' => $this->password,
            'msgid'    => '',
            'phones'   => $cellphone,
            'content'  => $content,
            'sign'     => $this->signName,
            // 'subcode'  => '',
            // 'sendtime' => time()
        ];

        $result = $this->request($this->url['single'], $data);

        if ($result['code'] != 200) {
            return $result;
        }

        $requestResult = $result['data'];

        // 检查发送状态
        if (!isset($requestResult['result']) || $requestResult['result'] != '0') {
            return return_format(400, '短信发送失败', $requestResult);
        }

        return return_format(200, '短信发送成功');
    }


    public function request($url, $data)
    {
        $data = json_encode($data);

        $ch = curl_init ($url);
    	curl_setopt ($ch, CURLOPT_POST, 1);
    	curl_setopt ($ch, CURLOPT_HEADER, 0);
    	curl_setopt ($ch, CURLOPT_FRESH_CONNECT, 1);
    	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
    	curl_setopt ($ch, CURLOPT_FORBID_REUSE, 1);
    	curl_setopt ($ch, CURLOPT_TIMEOUT, 30);
    	curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8', 'Content-Length: ' . strlen($data)) );
    	curl_setopt ($ch, CURLOPT_POSTFIELDS, $data );

        $resultJson = curl_exec($ch);
        $result = json_decode($resultJson, true);

        if (!$result) {
            $error = curl_error($ch);
            curl_close($ch);
            return return_format(400, 'request fail', $error);
        }

        curl_close($ch);
        return return_format(200, 'request ok', $result);
    }

}
