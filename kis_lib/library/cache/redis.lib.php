<?php
/**
 * redis管理类
 * 单例
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_cache_redis
{

    /**
     * 单例
     * @var object
     */
    private static $single = null;

    /**
     * redis区组
     * @var string
     */
    protected $group = '';

    /**
     * redis数据库
     * @var string
     */
    protected $db = 0;

    /**
     * redis 实例集合
     * @var array
     */
    protected $clients = [];



    /**
     * 禁止 new object
     * @return NULL
     */
    private function __constract() {}

    /**
     * 获取redis实例
     * @param  string $group redis区组
     * @param  integer $db 使用的数据库
     * @return object 本类单例
     */
    public static function getInstance($group = 'default', $db = 0)
    {
        // 单例
        if (!self::$single) {
            self::$single = new static();
        }

        $instance = self::$single;
        $instance->setGroup($group, $db);

        return $instance;
    }

    /**
     * 设置redis区组与数据库
     * @param string $group redis区组
     * @param integer $db redis数据库
     */
    public function setGroup($group, $db = 0)
    {
        $this->group = strtolower($group);
        $this->db = $db;
    }

    /**
     * 获取redis客户端实例
     * @param  string $hashKey 操作的key值
     * @param  boolean $isReconnect 是否重新连接redis
     * @param  boolean $isKey2Md5 key值是否使用md5
     * @return object redis客户端实例
     */
    protected function getClient($hashKey, $isReconnect = false, $isKey2Md5 = true)
    {
        if (!$hashKey) {
            throw new \Exception("redis getClient hash key undefined");
        }

        // 获取redis配置
        $config = config::get('redis.' . $this->group);

        $config = $config['master'] ?? $config;

        if (!$config || !is_array($config)) {
            throw new \Exception("redis config undefined");
        }

        // 获取redis实例编号
        $clientCongigIndex = 0;

        if (count($config) > 1) {
            $isKey2Md5 && $hashKey = md5($hashKey);
            $clientCongigIndex = floor(hexdec(substr(($hashKey), 0, 2))*count($config) / 256);
        }
        $config = $config[$clientCongigIndex];
        $clientKey = $this->group . ':' . $clientCongigIndex;

        // 实例不存在，创建redis
        if (!isset($this->clients[$clientKey]) || $isReconnect) {
            try {
                $client = new Redis();
                $client->connect($config['host'], $config['port'], 1);
                if ($this->db) {
                    $client->select($this->db);
                }
                $this->clients[$clientKey] = $client;

            } catch (Exception $e) {
                $this->triggerError('Connect Error', $e);
                return false;
            }
        }

        return $this->clients[$clientKey];
    }

    /**
     * 覆盖redis实例的connect，禁止调用redis实例的connect方法
     * @return null
     */
    public function connect($host = null, $port = null, $timeout = null) {}

    /**
     * 选择redis的数据库
     * @param  integer $hash redis实例编号
     * @param  integer $db redis数据库编号
     * @return unknow
     */
    public function select($hash = 0, $db = 0)
    {
        $client = $this->getInstance($hash, false, false);
        if (!$client) {
            return false;
        }
        return $client->select($db);
    }

    /**
     * 获取redis
     * @param  integer $hash redis实例编号
     * @return unknow
     */
    public function info($hash = 0)
    {
        $client = $this->getInstance($hash, false, false);
        if (!$client) {
            return false;
        }
        return $client->info();
    }

    /**
     * 查找所有符合给定模式的键
     * @param  integer $hash redis实例编号
     * @param  integer $keys 匹配模式
     * @return unknow
     */
    public function keys($hash = 0, $keys = 0)
    {
        $client = $this->getInstance($hash, false, false);
        if (!$client) {
            return false;
        }
        return $client->keys($keys);
    }

    /**
     * 删除当前数据库里面的所有数据
     * @param  integer $hash redis实例编号
     * @return unknow
     */
    public function flushdb($hash = 0)
    {
        $client = $this->getInstance($hash, false, false);
        if (!$client) {
            return false;
        }
        return $client->flushdb();
    }

    /**
     * 记录错误日志
     * @param  string $code [description]
     * @param  string $msg [description]
     * @return [type] [description]
     */
    protected function triggerError($code = '', $msg = '')
    {
        send_error_log('/nds/redsi_error', "{$code}\t{$msg}");
    }
    /**
     * 访问redis实例方法
     * @param  string $funcName redis实例方法名称
     * @param  array $arguments 参数列表
     * @return unknow
     */
    public function __call($funcName, $arguments = [])
    {
        if(!is_string($arguments[0]) || $arguments[0] == ''){
            return false;
        }
        // 获取redis客户端实例
        $client = $this->getClient($arguments[0]);
        if (!$client) {
            return false;
        }

        if (method_exists($client, $funcName)) {
            $time = microtime(true);
            try {
                // 访问
                $result = call_user_func_array(array($client, $funcName), $arguments);

            } catch (Exception $e) {
                $this->triggerError('Call Error', $funcName."\t".implode("\t", $arguments)."\t" . $e);
                // 处理失败后尝试重连
                usleep(1500);
                $client = $this->getClient($arguments[0]);
                if (!$client) {
                    return false;
                }

                try {
                    $result = call_user_func_array(array($client, $funcName), $arguments);
                } catch (Exception $e) {
                    $this->triggerError('Retry Call Error', $func_name."\t".implode("\t", $arguments)."\t".$e);
                }
            }
            return $result;

        } else {
            throw new Exception("Method Not Fount", 9999);
        }
    }



}
