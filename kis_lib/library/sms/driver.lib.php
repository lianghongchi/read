<?php
/**
 * 短信接口
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_sms_driver
{


    /**
     * 是否开发模式
     * @var bool
     */
    protected $isDev  = false;

    /**
     * 发送发送平台，目前可选 aliyun | dh3t
     * @var string
     */
    protected $driver = 'dh3t';

    /**
     * 每个手机号每小时只能发送5次短信
     * @var int
     */
    protected $sendCodeHourLimit = 50;

    /**
     * 每个手机号每天只能发送10次短信
     * @var int
     */
    protected $sendCodeDayLimit = 10;

    /**
     * 每个ip半小时只能发送100条短信
     * @var int
     */
    protected $sendCodeIpLimit = 100;

    /**
     * 验证码缓存名称
     * @var string
     */
    protected $codeCacheName = 'sms:cellphone.code:';

    /**
     * 缓存对象
     * @var object
     */
    protected $cache = null;



    public function __construct()
    {
        // 是否开发模式
        if (KIS_ENV == 'DEV') {
            $this->isDev = true;
        }
        // 短信平台客户端
        $clientString = 'lib_sms_driver_' . $this->driver;
        $this->client = new $clientString($this->isDev);
        // 缓存对象
        $this->cache  = redis();
    }


    /**
     * 发送短信验证码
     * @param  string $cellphone 手机号码
     * @param  string $content 短信内容、模板
     * @param  string $code 验证码
     * @return array 发送状态
     */
    public function sendCode($cellphone, $content, $code)
    {
        if (!$cellphone || !$content || !$code) {
            return return_format(700, '参数错误');
        }

        // 开发模式不真实发送验证码， 验证码默认 1169
        if ($this->isDev) {
            $code = '1169';
        }

        // 一个手机号1小时只能发送5次
        $hourKey = 'sms:cellphone.hour.' . $cellphone;

        $hourCount = $this->cache->get($hourKey);

        if ($hourCount && $hourCount > $this->sendCodeHourLimit) {
            return return_format(710, '验证码发送次数已达上限，请稍候再试');
        }

        // 每个手机号每天发送次数限制
        $dayKey = 'sms:cellphone.day.' . $cellphone;
        $dayCount = $this->cache->get($dayKey);

        if ($dayCount && $dayCount > $this->sendCodeHourLimit) {
            return return_format(710, '验证码发送次数已达上限，请明天再试');
        }

        // 每个ip 1小时发送100次
        $ip = get_ip();
        $ipKey = 'sms:cellphone.ip.' . $ip;
        $ipCount = $this->cache->get($ipKey);

        if ($ipCount && $ipCount > $this->sendCodeIpLimit) {
            return return_format(710, '验证码发送次数已达上限，请稍候再试');
        }

        // 发送验证码
        if (!$this->isDev) {

            switch ($this->driver) {
                case 'aliyun':
                    $result = $this->client->sendSMS($cellphone, $content, ['code' => $code]);
                    break;
                case 'dh3t':
                    $result = $this->client->sendSMS($cellphone, $content);
                    break;

                default:
                    throw new \Exception("SMS Driver Not Found");
                    break;
            }
        } else {
            $result = return_format(200, 'OK');
        }

        // 发送失败
        if ($result['code'] != 200) {
            return return_format(712, '验证码发送失败，请稍后再试');
        }

        // 发送成功保存验证码，有效时间10分钟
        $setState = $this->cache->setex($this->codeCacheName . $cellphone, 600, $code);

        // 状态保持失败
        if (!$setState) {
            return return_format(713, '验证码发送失败，请稍后再试');
        }

        // 添加发送记录
        if ($hourCount) {
            $this->cache->incr($hourKey, 1);
        } else {
            $this->cache->setex($hourKey, 1800, 1);
        }

        if ($dayCount) {
            $this->cache->incr($dayKey, 1);
        } else {
            $this->cache->setex($dayKey, 24 * 86400, 1);
        }

        if ($ipCount) {
            $this->cache->incr($ipKey, 1);
        } else {
            $this->cache->setex($ipKey, 1800, 1);
        }

        return return_format(200, '短信验证码发送成功');
    }

    /**
     * 检查验证码是否正确
     * @param  string $cellphone 手机号码
     * @param  string $code 验证码
     * @return array 验证结果
     */
    public function checkCode($cellphone, $code)
    {
        if (!$cellphone || !$code) {
            return return_format(412, '参数错误');
        }
        // 获取验证码
        $cacheKey = $this->codeCacheName . $cellphone;

        $getCode = $this->cache->get($cacheKey);

        if (!$getCode) {
            return return_format(710, '验证码已失效，请重新发送');
        }

        if ($getCode != $code) {
            return return_format(710, '验证码错误，请重新输入');
        }
        $this->cache->delete($cacheKey);

        return return_format(200, '验证成功');
    }

    /**
     * 执行短信驱动其它功能
     * @param  string $method 功能方法名称
     * @param  array $parameter 参数
     * @return array 执行结果
     */
    public function __call($method, $parameter)
    {
        if (!method_exists($this->client, $method)) {
            throw new \Exception("SMS Client method Not Found", 1);
        }

        $result = call_user_func_array([$this->client, $method], $parameter);

        return $result;
    }


}
