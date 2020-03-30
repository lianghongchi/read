<?php
/**
 * 阿里云短信接口
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_sms_driver_aliyun
{


    /**
     * API key
     * @var [type]
     */
    protected $key = '';

    /**
     * API keuSecret
     * @var [type]
     */
    protected $keySecret = '';

    /**
     * 阿里云短信签名
     * @var [type]
     */
    protected $signName = '';


    /**
     * API 地址集合
     * @var [type]
     */
    protected $url = [
        // 单条短信发送URL
        'single' => 'https://dysmsapi.aliyuncs.com',
    ];



    public function __construct($isDev = false)
    {
        // 是否开发环境
        $this->isDev = $isDev;

        if ($this->isDev) {
            return ;
        }

        if (!$this->key) {
            throw new \Exception("SMS Aliyun Key Undefined");
        }

        if (!$this->keySecret) {
            throw new \Exception("SMS Aliyun keySecret Undefined");
        }

        if (!$this->signName) {
            throw new \Exception("SMS Aliyun signName Undefined");
        }
    }




    /**
     * 发送短信
     * @param  string $cellphone 手机号
     * @param  string $templateCode 短信模板
     * @param  array $parameter 参数
     * @return array 发送结果
     */
    protected function sendSMS($cellphone, $templateCode, $parameter)
    {
        if (!$cellphone || !$templateCode) {
            return return_format(400, '参数错误[2]');
        }

        $data = [
            'PhoneNumbers'  => $celephone,
            'SignName'      => $this->signName,
            'TemplateCode'  => $templateCode,
            'TemplateParam' => is_array($parameter) ? json_encode($parameter) : '',
        ];

        $result = $this->request($this->url['single'], $data);

        if ($result['code'] != 200) {
            return $result;
        }

        $requestResult = $result['data'];

        // 检查发送状态
        if (!isset($requestResult['Code']) || $requestResult['Code'] != 'OK') {
            return return_format(400, '短信发送失败', $requestResult);
        }

        return return_format(200, '短信发送成功');
    }


    public function request($domain, $data)
    {
        $data = array_merge($data, [
            "RegionId" => "cn-hangzhou",
            "Action" => "SendSms",
            "Version" => "2017-05-25",
        ]);

        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => $this->key,
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $data);
        ksort($apiParams);

        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, $this->keySecret . "&",true));

        $signature = $this->encode($sign);

        $url = "{$domain}/?Signature={$signature}{$sortedQueryStringTmp}";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if(substr($url, 0,5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

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


    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

}
