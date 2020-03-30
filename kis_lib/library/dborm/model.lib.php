<?php
/**
 * mysql model基类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_dborm_model
{


    /**
     * 数据表主键
     * @var string
     */
    protected $_pkey  = 'id';

    /**
     * 数据表名称，全称
     * @var string
     */
    protected $_table = null;

    /**
     * 数据表字段列表，可为空，field 反向过滤需要定义此字段
     * @var array
     */
    protected $_field = [];

    /**
     * 数据表全名称，包括所在数据库
     * @var string
     */
    protected $_tableFullName = '';

    /**
     * 字段过滤数组
     * @var array
     */
    protected $_filter = [];

    /**
     * 数据库名称
     * @var string
     */
    protected $_database = '';

    /**
     * 数据库实例
     * @var object
     */
    protected $_builder = null;

    /**
     * 数据表配置
     * @var
     */
    protected $_hashConfig = [];



    public function __construct()
    {
        // 检查数据库
        if (!$this->_database) {
            throw new \Exception('undefined _database');
        }

        // 检查表名
        if (!$this->_table) {
            throw new \Exception('undefined _table');
        }

        $this->_initTable();

        DB::initConfig($this->_database);
        $hashConfig = config::get('database/hash/' . $this->_database);

        $this->_hashConfig = $hashConfig[$this->_table] ?? [];
        if (!$hashConfig || !$this->_hashConfig) {
            throw new \Exception("Database Config Undefined", 1);
        }

        // 数据库名称的表全名
        $this->_tableFullName = $this->_hashConfig['database'] . '.' . $this->_table;
    }

    /**
     * 初始化函数
     * @return
     */
    protected function _initTable() {}

    /**
     * 获取数据库实例
     * @return [type] [description]
     */
    public function getBuilder()
    {
        if (!$this->_builder) {
            $this->_builder = new lib_dborm_database($this->_hashConfig['table']);
        }

        return $this->_builder;
    }

    /**
     * 获取主键
     * @return string
     */
    public function getPkey()
    {
        return $this->_pkey;
    }

    /**
     * 获取表名称
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * 获取数据表全名称
     * @return string
     */
    public function getTableFullName()
    {
        return $this->_tableFullName;
    }

    /**
     * 获取数据表配置
     * @return array
     */
    public function getHashConfig()
    {
        return $this->_hashConfig;
    }

    /**
     * 获取数据库名称
     * @return string
     */
    public function getDatabase()
    {
        return $this->_database;
    }

    // 开始事务
    public function startTrans()
    {
        return null;
    }

    // 提交事务
    public function commit()
    {
        return null;
    }

    // 回滚事务
    public function rollback()
    {
        return null;
    }

    /**
     * 根据主键查找数据
     * @param  mix $id 数据主键
     * @return object
     */
    public function id($id)
    {
        $this->getBuilder()->where($this->_pkey, $id);
        return $this;
    }

    /**
     * 指定字段
     * @param  string $fieldString 字段列表 用 ，号 分割
     * @param  boolean $isFilter 是否反向过滤，反向过滤需定义_field列表
     * @return object
     */
    public function field($fieldString, $isFilter = false)
    {
        if ($isFilter) {
            if (!$this->_field) {
                throw new \Exception("undefined field");
            }

            $fieldArr = explode(',', $fieldString);
            $getFieldArr = [];

            foreach ($this->_field as $key => $value) {
                if (!in_array($value, $fieldArr)) {
                    $getFieldArr[] = $value;
                }
            }

            $fieldString = implode(',', $getFieldArr);
        }

        $this->getBuilder()->field($fieldString);

        return $this;
    }

    public function queryWithNoHash($sql)
    {
        if (!$sql) {
            return false;
        }
        return db::QueryWithNoHash($sql, $this->_database, $this->_hashConfig['database']);
    }

    /**
     * 访问数据库构建对象实例
     * @param  string $method 方法名称
     * @param  array $parameters 参数
     * @return mix
     */
    public function __call($method, $parameters)
    {
        $client = $this->getBuilder();

        if (!method_exists($client, $method)) {
            throw new \Exception("call to undefined method dborm database->{$method}");
        }

        $result = call_user_func_array([$client, $method], $parameters);

        if (is_a($result, get_class($client))) {
            return $this;
        }

        return $result;
    }

    /**
     * 批量格式化
     * @param  array $data 数据
     * @return
     */
    public function formatMulti($data)
    {
        if (!$data) {
            return $data;
        }

        if (method_exists($this, 'format')) {
            foreach ($data as $key => &$value) {
            	$value = $this->format($value);
            }
            unset($value);
        }

        return $data;
    }

    /**
     * 格式化数据
     * @param  array $data 数据
     * @return array
     */
    public function format($data)
    {
        if (isset($data['add_time'])) {
            $data['add_time_text'] = date('Y-m-d H:i:s', $data['add_time']);
        }

        return $data;
    }

    public function __get($name)
    {
        if (isset($this->$name)) {
            return $this->$name;
        }

        return false;
    }

}
