<?php
/**
 * 数据库操作类
 *
 * @package		KIS
 * @author		塑料做的铁钉<ring.msn@gmail.com>
 * @since		2014-06-23
 * @example
 *
 * 【SQL格式说明】
 * 	·以冒号(:)加键名(keyname)作为预绑定键名 示例：	(可读性更强，解析效率更高，强烈建议使用)
 *   	"UPDATE user_info SET sex=:sex WHERE uid=:uid" , ['sex'=>$sex,'uid'=>$uid]
 *  ·以问号(?)作为与绑定键名 示例：					(可读性较弱，解析较慢，特别是参数比较多时)
 *  	"UPDATE user_info SET sex=? WHERE uid=?""  ,		 [$sex, $uid]
 *
 * 【公用函数】
 * 	//执行SQL 只返回成功失败 一般用于增删改操作等写操作
 * 	DB::Query("UPDATE user_info SET sex=:sex WHERE uid=:uid", ['sex'=>$sex,'uid'=>$uid]);
 * 	//查询多行	返回二维数组 一般用于select操作
 *  DB::SELECT("SELECT * FROM user_info WHERE uid=:uid",['uid'=>123]);
 *  //DB::SELECT()同名函数
 *  DB::getAll($sql, $params);//as same as DB::SELECT($sql, $params);
 *  //查询一行 返回一维数组
 *  DB::getOne($sql, $params);//as same as DB::SELECT($sql, $params)[0];
 *  //插入并返回插入的自增ID  失败返回FALSE
 *  DB::insert($sql, $params);
 *  //删除并返回影响行数 	失败返回FALSE
 *  DB::delete($sql, $params);
 *  //更新并返回影响行数 	失败返回FALSE
 *  DB::update($sql, $params);
 */

class DB{
    //默认的数据库驱动，配置中不包含时使用
    const default_driver = 'mysql';
    //默认的数据库端口，配置中不包含时使用
    const default_port = 3306;
    //超过这个时间认为耗时较长，要打日志啥的
    const time_too_long = 1;
    
    protected static $query_callback = null;
    /**
     * PDO实例数组
     * @var array[PDO]
     */
    protected static $instances;
    
    protected static $pdo_key_curr;
    
    /**
     * @var PDOStatement对象
     */
    protected static $PDOStatement;
    
    /**
     * @var string 马上要执行的SQL语句
     */
    protected static $sql;
    
    /**
     * 被尝试执行的SQL
     * @var array
     */
    protected static $executed_sql = array();
    
    /**
     * @var 执行SQL操作返回的错误
     */
    protected static $error = array();
    
    /**
     * @var 前端SQL中table名hash到对应配置文件用的hash一维数组 key是前端表名 value是该表hash文件名
     */
    protected static $hash_table_config = array();
    
    /**
     * 2.0 后已弃用
     * 默认必须要加载的配置文件名前缀(替代下面的*号)，后期用户可以自定义补充新的配置文件
     * 配置位于kis/config/database/*.cfg.php
     * @var string
     */
    protected static $hash_table_config_base = 'hash';
    
    /**
     * 已经被加载的配置表，防止重复加载报配置表名重复的错误提示
     * @var array
     */
    protected static $hash_table_config_loaded = array();
    
    /**
     * 数据库哈希配置信息
     * @var array
     */
    public static $hash_config = array();
    
    /**
     * 存储hash出来后直接用于操作数据库的host username password database_name table_name等信息
     * @var array
     */
    protected static $config = array();
    
    /**
     * 是否从库操作
     * @var bool
     */
    protected static $isSlave = false;

    /**
     * 设置是否从库操作
     * @param $isSlave
     */
    public static function setSlave($isSlave) {
        self::$isSlave = $isSlave;
    }
    
    /**
     * 设置回调
     * @param [type] $callback_function [description]
     */
    public static function setQueryCallback($callback_function){
        self::$query_callback = $callback_function;
    }
    
    /**
     * self::SELECT()同名函数 查询多行数据
     * @param  string $sql    要执行的SQL
     * @param  array  $params 要绑定的参数
     * @param  string $with_column_key 如果需要某一列作为二维数组key，则此参数为该列名，默认不需要
     * @return array/false    二维数组或失败
     */
    public static function getAll($sql, $params = array(), $with_column_key = false , $hash_need_md5 = true){
        return self::select($sql, $params, $with_column_key , $hash_need_md5);
    }
    
    /**
     * 查询一行 as same as DB::SELECT($sql, $param)[0]
     * @param  string $sql    要执行的SQL
     * @param  array  $params 要绑定的参数
     * @return array/false    一维数组或失败
     */
    public static function getOne($sql, $params = array() , $hash_need_md5 = true){
        $res =  self::select($sql, $params, false, $hash_need_md5);
        if($res) {
            return $res[0];
        }
        return $res;
    }
    
    /**
     * 查询多行数据
     * @param  string $sql    要执行的SQL
     * @param  array  $params 要绑定的参数
     * @param  string $with_column_key 如果需要某一列作为二维数组key，则此参数为该列名，默认不需要
     * @return array/false    二维数组或失败
     */
    public static function select($sql, $params = array(), $with_column_key = false , $hash_need_md5 = true){
        if(self::Query($sql, $params , $hash_need_md5)){
            if($with_column_key) {
                $rows = array();
                while ($row = self::$PDOStatement->fetch(PDO::FETCH_ASSOC)) {
                    $rows[ $row[$with_column_key] ] = $row;
                }
                return $rows;
            } else {
                return self::$PDOStatement->fetchAll(PDO::FETCH_ASSOC);
            }
        } else {
            // self::trigger_error();
            return false;
        }
    }
    
    /**
     * 插入并返回lastInsertId
     * @param [type] $sql    [description]
     * @param array  $params [description]
     */
    public static function insert($sql, $params = array() , $hash_need_md5 = true){
        return self::InsertWithLastId($sql, $params , $hash_need_md5);
    }
    /**
     * insert同名函数
     * @param [type] $sql    [description]
     * @param array  $params [description]
     */
    public static function InsertWithLastId($sql, $params = array() , $hash_need_md5 = true){
        $last_id = 'last_id';
        $ret = self::Query($sql, $params , $hash_need_md5, $last_id);
        if($ret){
            return $last_id ?: $ret;
        }
        return $ret;
    }
    
    /**
     * 删除并返回影响行数 失败返回FALSE
     * @param  [type] $sql    [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public static function delete($sql, $params = array() , $hash_need_md5 = true){
        return self::deleteOrUpdate($sql, $params , $hash_need_md5);
    }
    
    /**
     * 更新并返回影响行数 失败返回FALSE
     * @param  [type] $sql    [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    public static function update($sql, $params = array() , $hash_need_md5 = true){
        return self::deleteOrUpdate($sql, $params , $hash_need_md5);
    }
    
    /**
     * 删除或更新并返回影响行数  失败返回FALSE
     * @param  [type] $sql    [description]
     * @param  array  $params [description]
     * @return [type]         [description]
     */
    protected static function deleteOrUpdate($sql, $params = array() , $hash_need_md5 = true){
        $rowCount = 'rows';
        $ret = self::Query($sql, $params , $hash_need_md5, $rowCount);
        if($ret){
            return $rowCount /*?: $ret*/;
        }
        return $ret;
    }
    
    public static function affectedRows(){
        return self::$PDOStatement->rowCount();
    }
    
    /**
     * 执行一条SQL语句，并返回执行结果
     * @author 塑料做的铁钉<81938561@qq.com>
     * @datetime 2018-06-20T16:44:06+0800
     * @param    [type]                   $sql           SQL语句
     * @param    array                    $params        绑定的参数
     * @param    boolean                  $hash_need_md5 是否hash拆表
     * @param    [type]                   &$ext_info     返回额外信息 last_id 或 rows
     */
    public static function Query($sql, $params = array() , $hash_need_md5 = true, &$ext_info = null){
        self::$sql = $sql;
        //$time[] = microtime(true);
        //根据SQL解析出表名和hash_key
        $hash = self::getTableAndHashKey($sql, $params);
        if(!$hash) {
            return false;
        }
        // if($_GET['ring'])var_dump(__LINE__,$hash,$hash['table'], $hash['hash_key'],$config);
        //根据配置文件hash出具体的表名和数据库信息等
        $config = self::hash($hash['table'], $hash['hash_key'], $hash['operation'], $hash_need_md5);

        if(!$config){
            return false;
        }
        //解析出hash后的SQL
        $sql = self::getRealQuerySQL($sql);
//        if(in_array('wechat', $params) || in_array('qq', $params)){
//            hlp_log::log("db_sql", $sql);
//            hlp_log::log("db_sql", json_encode($params));
//        }
        
        //创建PDO
        $time = microtime(true);
        self::$pdo_key_curr = self::getInstance();
        if(!self::$pdo_key_curr){
            return false;//db连接失败
        }
        //$time[] = microtime(true);
        self::$PDOStatement = self::$instances[self::$pdo_key_curr]->prepare($sql);

        //$time[] = microtime(true);
        if($params && is_array($params)) {
            foreach ($params as $key => $value) {
                if(is_int($value)) {
                    $PARAM_TYPE = PDO::PARAM_INT;
                } else {
                    $PARAM_TYPE = PDO::PARAM_STR;
                }
                if (is_numeric($key)) {
                    self::$PDOStatement->bindValue($key+1,   $value, $PARAM_TYPE);
                } else {
                    self::$PDOStatement->bindValue(':'.$key, $value, $PARAM_TYPE);
                }
            }
        }

        $ret =  self::$PDOStatement->execute();
        $time = microtime(true) - $time;
        if($time > self::time_too_long) {
            self::trigger_error(E::EC_DB_QUERY_TIMEOUT, E::$EC_MSG[E::EC_DB_QUERY_TIMEOUT], array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() ));
        }
        if($ret) {
            switch ($ext_info) {
                case 'last_id':
                    $ext_info = self::$instances[self::$pdo_key_curr]->lastInsertId();
                    break;
                case 'rows':
                    $ext_info = self::$PDOStatement->rowCount();
                    break;
                default:
                    # code...
                    break;
            }
            self::$query_callback && call_user_func_array(self::$query_callback, ['sql'=>$sql, 'params' => $params]);
            KIS_TRACE_MODE && (!KIS_CORE_DAEMON) && self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time);
            // if($returnLastInsertId) {
            // 	return self::$instances[self::$pdo_key_curr]->lastInsertId();
            // }
        } else {
            self::$error[] = self::$PDOStatement->errorInfo();
            self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() );
            self::trigger_error(E::EC_DB_QUERY_ERR, end(self::$executed_sql));
        }
        return $ret;
    }
    
    /**
     * 执行已经hash过的 不需要自动hash的原始SQL
     * @param [type]  $sql         已经解析过的原始SQL
     * @param [type]  $host_config DB IP 用户名配置
     * @param string  $hash_key    选择IP用的hash_key 一般是0-f
     * @param boolean $log         暂无用
     */
    public static function QueryWithNoHash($sql, $host_config,$database, $hash_key = '', $params = [], $log = false){
        $host = self::getConfig('database/host/' , $host_config);
        $host['host'] = $host['host'][floor(hexdec(substr($hash_key, 0, 1))*count($host['host'])/16)];
        $host['database'] = $database;

        $time = microtime(true);
        self::$pdo_key_curr = self::getInstance($host);
        if(!self::$pdo_key_curr){
            return false;//db连接失败
        }
        self::$PDOStatement = self::$instances[self::$pdo_key_curr]->prepare($sql);
        if($params && is_array($params)) {
            foreach ($params as $key => $value) {
                if(is_int($value)) {
                    $PARAM_TYPE = PDO::PARAM_INT;
                } else {
                    $PARAM_TYPE = PDO::PARAM_STR;
                }
                if (is_numeric($key)) {
                    self::$PDOStatement->bindValue($key+1,   $value, $PARAM_TYPE);
                } else {
                    $rr = self::$PDOStatement->bindValue(':'.$key, $value, $PARAM_TYPE);
                }
            }
        }
        $ret =  self::$PDOStatement->execute();
        if($time > self::time_too_long) {
            self::trigger_error(E::EC_DB_QUERY_TIMEOUT, E::$EC_MSG[E::EC_DB_QUERY_TIMEOUT], array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() ));
        }
        $time = microtime(true) - $time;
        if($ret) {
            self::$query_callback && call_user_func_array(self::$query_callback, ['sql'=>$sql, 'params' => $params]);
            KIS_TRACE_MODE && (!KIS_CORE_DAEMON) && self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time);
            return self::$PDOStatement->fetchAll(PDO::FETCH_ASSOC) ?: $ret;
        } else {
            self::$error[] = self::$PDOStatement->errorInfo();
            self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() );
            self::trigger_error(E::EC_DB_QUERY_ERR, end(self::$executed_sql));
        }
        return $ret;
    }
    
    /**
     * [QueryWithNoHashAnd4Bind 执行不需要自动解析的sql] 用于脚本遍历数据库操作
     * @param [string] $sql         [无需解析的sql]
     * @param [array] $params      [绑定的参数组]
     * @param [string] $host_config [DB IP 用户名配置]
     */
    public static function QueryWithNoHashAnd4Bind($sql,$params,$host_config){
        $host = self::getConfig('database/host/' , $host_config);
        $host['host'] = $host['host'][floor(hexdec(substr($hash_key, 0, 1))*count($host['host'])/16)];
        $time = microtime(true);
        self::$pdo_key_curr = self::getInstance($host);
        if(!self::$pdo_key_curr){
            return false;//db连接失败
        }
        self::$PDOStatement = self::$instances[self::$pdo_key_curr]->prepare($sql);
        if($params && is_array($params)) {
            foreach ($params as $key => $value) {
                if(is_int($value)) {
                    $PARAM_TYPE = PDO::PARAM_INT;
                } else {
                    $PARAM_TYPE = PDO::PARAM_STR;
                }
                if (is_numeric($key)) {
                    self::$PDOStatement->bindValue($key+1,   $value, $PARAM_TYPE);
                    // var_dump('rrrrrr',$rr);
                } else {
                    $rr = self::$PDOStatement->bindValue(':'.$key, $value, $PARAM_TYPE);
                }
            }
        }
        $ret =  self::$PDOStatement->execute();
        $time = microtime(true) - $time;
        if($time > self::time_too_long) {
            self::trigger_error(E::EC_DB_QUERY_TIMEOUT, E::$EC_MSG[E::EC_DB_QUERY_TIMEOUT], array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() ));
        }
        if($ret) {
            self::$query_callback && call_user_func_array(self::$query_callback, ['sql'=>$sql, 'params' => $params]);
            KIS_TRACE_MODE && (!KIS_CORE_DAEMON) && self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time);
            return self::$PDOStatement->fetchAll(PDO::FETCH_ASSOC) ?: $ret;
        } else {
            self::$error[] = self::$PDOStatement->errorInfo();
            self::$executed_sql[] = array('sql'=>$sql, 'params' => $params, 'time'=>$time, 'error'=>self::$PDOStatement->errorInfo() );
            self::trigger_error(E::EC_DB_QUERY_ERR, end(self::$executed_sql));
        }
        return $ret;
    }
    
    public static function getStat($type = 'html'){
        switch ($type) {
            case 'html':
                $html_all = '';
                $i = 1;
                // var_dump(self::$executed_sql);
                foreach (self::$executed_sql as $key => $row) {
                    $html = "[$i] {$row['sql']}\t";
                    if($row['params'])
                        $html .= json_encode($row['params']) . "\t";
                        $row['time'] = round($row['time'],5);
                        if($row['time'] > self::time_too_long) {
                            $html .= "<font color=red>{$row['time']}s</font>\t";
                        } else {
                            $html .= "{$row['time']}s\t";
                        }
                        if($row['error'])
                            $html = "<font color=red>{$html} Error:".json_encode($row['error'])."</font>\t";
                            $i++;
                            $html_all .= $html . PHP_EOL;
                }
                if($html_all)
                    return "<pre>DB STAT:\n{$html_all}</pre>";
                    break;
                    
            default:
                # code...
                break;
        }
    }
    
    /**
     * 通用hash方法，通过配置表名和hash值(一般是表的主键)hash出对应真是数据库服务器,真是库名表名
     * @param string 配置表名，即前端SQL中的表名
     * @param string hash值, 不存在表示单表不需要hash
     * $param string operation 当前sql操作
     * @param bool   hash值是否需要MD5 [defautl true]
     * @return array hash出来的连接数据库库表名等信息，详见self::$config
     */
    protected static function hash($table, $hash_key = null, $operation, $need_md5 = true){
        if(!self::getHashConfig($table)){
            return false;
        }
        $db_hash_config = self::$hash_config[$table];
        $host_group = $db_hash_config['host_group'];
        //是否开启使用主从配置 
        if (self::$isSlave && isset($db_hash_config['host_group_slave']) && !empty($db_hash_config['host_group_slave']) && $operation === 'select') {
            $host_group = $db_hash_config['host_group_slave'];

        }
        $host_config = self::getConfig('database/host/', $host_group);
        if (!$host_config) {
            self::trigger_error(E::EC_DB_CONFIG_ERR, "Config_file_not_found: database/host/{$host_group}.cfg.php 数据库配置文件未找到 ", ['table' => $table, 'sql' => self::$sql], __LINE__);
            return false;
        }
//      原CODE  
//        $db_hash_config = self::$hash_config[$table];
//        $host_config	= self::getConfig('database/host/', $db_hash_config['host_group']);
//        // if($_GET['ring']) var_dump($host_config,$table, self::$hash_config[$table], $db_hash_config,$db_hash_config['host_group']);
//        if(!$host_config){
//            self::trigger_error(E::EC_DB_CONFIG_ERR,"Config_file_not_found: database/host/{$db_hash_config['host_group']}.cfg.php 数据库配置文件未找到 ", ['table'=>$table,'sql'=>self::$sql], __LINE__);
//            return false;
//        }
        
        // var_dump($db_hash_config);
        
        if($db_hash_config['tb_hash_length']) {//need to hash
            $need_md5 && ($hash_key !==null) && $hash_key = md5($hash_key);
            
            $config['host'] 	= $host_config['host'][floor(hexdec(substr($hash_key, 0, 1))*count($host_config['host'])/16)];//=floor(hash_hex/(16/host_nums));
            $config['table'] 	= $db_hash_config['table']   .'_'.substr($hash_key, 0, $db_hash_config['tb_hash_length']);
            $config['database'] = $db_hash_config['db_hash_length'] ? ($db_hash_config['database'].'_'.substr($hash_key, 0, $db_hash_config['db_hash_length'])) : $db_hash_config['database'];
            
        } else {
            $config['host'] 	= $host_config['host'][0];
            $config['table']	= $db_hash_config['table'];
            $config['database'] = $db_hash_config['database'];
        }
        $config['table_alias']	= $table;
        $config['driver'] 	= $host_config['driver'] ?: self::default_driver;
        $config['port']		= $host_config['port'];
        $config['username']	= $host_config['username'];
        $config['password']	= $host_config['password'];
        
        return self::$config = $config;
    }
    
    /**
     * 通过表名 根据配置得出这个表对应的hash配置，其中包含服务器组和表名的hash规则
     * @param string 通用表名
     * @return hash配置信息  用于存储于self::$hash_config[$table];
     */
    protected static function getHashConfig($table){
        // if($_GET['ring'])var_dump('ttt',$table,self::$hash_config,self::$hash_config[$table]);
        if(empty(self::$hash_config[$table])) {
            $db_hash_config = self::initConfig();//Config::get('database_'.self::$cfgFile);
            if(!$db_hash_config[$table]){
                self::trigger_error(E::EC_DB_CONFIG_ERR,"Config_table_not_found:  数据表{$table}配置在已加载的配置表中未找到 ", ['table'=>$table,'sql'=>self::$sql], __LINE__);
                return false;
            }
            $config = self::getConfig('database/hash/', $db_hash_config[$table]);
            if(!$config){
                self::trigger_error(E::EC_DB_CONFIG_ERR,"Config_file_not_found: database/hash/{$db_hash_config[$table]}.cfg.php 数据表哈希配置文件未找到 ", ['table'=>$table,'sql'=>self::$sql], __LINE__);
                return false;
            }
            
            if(!$config[$table]) {
                self::trigger_error(E::EC_DB_CONFIG_ERR,"Config_not_found: {$table} 配置表 {$table} 信息在database/hash/{$db_hash_config[$table]}.cfg.php中未找到 ", ['table'=>$table,'sql'=>self::$sql], __LINE__);
                return false;
            }
            self::$hash_config[$table] = $config[$table];
            // if($_GET['ring'])var_dump('sss',$table,Config::get('database_hash_'.$db_hash_config[$table] ),self::$hash_config[$table],$db_hash_config);
        }
        
        return self::$hash_config[$table];
    }
    
    public static function cfgFile($config = null){
        return self::initConfig($config);
    }
    public static function initConfig($config = null){
        if(empty(self::$hash_table_config) && KIS_CORE_VERSION < 2.0) {
            self::$hash_table_config = self::getConfig('database/', self::$hash_table_config_base);
            self::$hash_table_config_loaded[ self::$hash_table_config_base ] = true;
        }
        // var_dump($config);
        if($config && empty(self::$hash_table_config_loaded[ $config ])) {
            if (KIS_CORE_VERSION < 2.0) {
                $config_arr = self::getConfig('database/', $config);
            } else {
                $config_arr = null;
            }
            
            if($config_arr && is_array($config_arr)) {
                foreach ($config_arr as $key => $value) {
                    if(isset(self::$hash_table_config[ $key ]) && self::$hash_table_config[ $key ]!=$value){
                        //TODO 表名重复 必须预警
                        self::trigger_error(E::EC_DB_CONFIG_CONFLICT, E::$EC_MSG[E::EC_DB_CONFIG_CONFLICT].'配置重复或冲突：' . $config, array($config,$key));
                        echo E::EC_DB_CONFIG_CONFLICT . ' : ' . E::$EC_MSG[E::EC_DB_CONFIG_CONFLICT] . ':' .$config;
                        exit;
                    } else {
                        self::$hash_table_config[ $key ] = $value;
                    }
                }
            } else {//找不到配置文件则尝试从hash目录读取配置文件
                $config_arr = self::getConfig('database/hash/', $config);
                if($config_arr && is_array($config_arr)) {
                    foreach ($config_arr as $key => $value) {
                        if(isset(self::$hash_table_config[ $key ]) && self::$hash_table_config[ $key ]!=$config){
                            //TODO 表名重复 必须预警
                            self::trigger_error(E::EC_DB_CONFIG_CONFLICT, E::$EC_MSG[E::EC_DB_CONFIG_CONFLICT].'配置重复或冲突：' . $config, array($config,$key));
                            echo E::EC_DB_CONFIG_CONFLICT . ' : ' . E::$EC_MSG[E::EC_DB_CONFIG_CONFLICT] . ':' .$config;
                            exit;
                        } else {
                            self::$hash_table_config[ $key ] = $config;
                        }
                    }
                } else{
                    //TODO trigger error ...
                }
            }
            self::$hash_table_config_loaded[ $config ] = true;
        }
        return self::$hash_table_config;
    }
    
    /**
     * 根据具体的参数创建PDO实例
     * @param 配置信息
     * @return pdo PDO实例的KEY self::$instances[$key]
     */
    protected static function getInstance($config = null){
        !$config && $config = self::$config;

        switch ($config['driver']) {
            case 'mysql':
                # code...oci:dbname=192.168.101.10:1521/
                $dsn = $config['driver'].':dbname='.$config['database'].';host='.$config['host'].';port='.$config['port'];
                break;
            case 'oci':
                $dsn = $config['driver'].':host='.$config['host'].':'.$config['port'].';dbname='.$config['database'];
                //$dsn = $config['driver'].':dbname=//'.$config['host']./*':'.$config['port'].*/'/'.$config['database'];//.';dbname='.$config['database'];
                //dbname=//localhost:1521/mydb
                // var_dump($dsn, $config['username'], $config['password']);//die;
                break;
            default:
                # code...
                break;
        }
        
        $key = $config['driver'].':host='.$config['host'].';username='.$config['username'].';port='.$config['port'];
        if(empty(self::$instances[$key]) || ! (self::$instances[$key] instanceof PDO) ) {
            // if($_GET['ring'])var_dump('expression',$dsn,self::$config,self::$hash_table_config);
            try{
                self::$instances[$key] = new PDO($dsn, $config['username'], $config['password'], array(PDO::ATTR_TIMEOUT=>2));
            } catch (Exception $e) {
                self::trigger_error(E::EC_DB_CONN_ERR,E::$EC_MSG[E::EC_DB_CONN_ERR].':'.$e);
                return false;
            }
            
        }
        // if($config['driver'] == 'oci') {var_dump(self::$instances[$key]);die;}
        return $key;
    }
    
    
    /**
     * 根据sql和哈希规则 替换出真正要执行的SQL 具体就是替换数据库名和表名
     * @param  string 原始SQL
     * @return string 替换后的真实SQL语句 self::$sql
     */
    protected static function getRealQuerySQL($sql = null){
        $sql && self::$sql = $sql;
        if(!empty(self::$config['table_alias'])){
            self::$sql = preg_replace('/(SELECT[\s\S]*FROM|UPDATE|DELETE\s+FROM|INSERT.*?INTO|REPLACE\s+INTO)([\s`\w\.]+?)([`]?)'.self::$config['table_alias'].'([`]?)/i',
                '\1 `'.self::$config['database'].'`.`'.self::$config['table'].'`',
                self::$sql,
                1);
        }
        return self::$sql;
        // return;
        // $ptn = self::$config['table_alias']
        // 	? '/(SELECT[\s\S]*FROM|UPDATE|DELETE\s+FROM|INSERT\s+INTO|REPLACE\s+INTO)\s+'.self::$config['table_alias'].'/i'
        // 	: '/(SELECT[\s\S]*FROM|UPDATE|DELETE\s+FROM|INSERT\s+INTO|REPLACE\s+INTO)\s+([^\s(]*)/i';
        // self::$sql = preg_replace($ptn, '\1 '.self::$config['table'], self::$sql, 1);
    }
    
    /**
     * 根据SQL和绑定的参数解析出表名和hash值,用于self::hash();
     *
     * @param 	string 	SQL语句
     * @param 	array 	绑定的参数 [defaut array[]]
     * @return  array   返回解析出来的表名和hash值 [table, hash_key]
     */
    protected static function getTableAndHashKey($sql, $params = array()){
        $ptn = '/^[\s]*(SELECT[\S\s]*?[\s]+FROM|UPDATE|DELETE[\s]+FROM|INSERT.*?[\s]+INTO|REPLACE[\s]+INTO)\s+([^\s(]*)/i';
        if ($num = preg_match($ptn, $sql, $match)) {
            // if($_GET['ring']) var_dump($sql, $match);
            if(empty($match[2])) {
                self::trigger_error(E::EC_DB_HASH_ERR,'hash_key_not_found SQL表名解析失败 '.__LINE__, ['sql'=>$sql,'params'=>$params]);
                return false;
            }
            
            $operation = strtolower(substr($match[1], 0, 6));
//            if (KIS_CORE_DB_READ_ONLY && $operation != 'select') {
//                self::trigger_error(E::EC_DB_READ_ONLY,'EC_DB_READ_ONLY DB只读'.$operation);
//                return false;
//            }
            
            $table = self::getTableFromSqlTable($match[2]);
            $db_hash_config = self::getHashConfig($table);
            if(!$db_hash_config){
                return false;
            }
            $hash_column	= $db_hash_config['hash_column'];
            if($db_hash_config['tb_hash_length'] == 0) {
                return array('table'=>$table,'hash_key' => null, 'operation' => $operation);
            }
            if(isset($params[$hash_column])){
                $hash_key = $params[$hash_column];
            } else {
                $hash_key = self::getHashKeyFromSql($match[1], $sql, $hash_column, $params);
            }
            if($hash_key === false || $hash_key === null) {
                self::trigger_error(E::EC_DB_HASH_ERR,'hash_key_not_found SQL表名解析失败 '.__LINE__, ['sql'=>$sql,'params'=>$params]);
                return $hash_key;
            } else {
                return array('table'=>$table,'hash_key' => $hash_key, 'operation' => $operation) ;
            }
        } else {
            self::trigger_error(E::EC_DB_HASH_ERR,'hash_key_not_found SQL语法解析失败 '.__LINE__, ['sql'=>$sql,'params'=>$params]);
            return false;
        }
    }
    
    /**
     * 过滤SQL解析出来的表名
     * @param  string $table SQL解析出来的表名，可能带有库名或[`]符号
     * @return string        SQL解析出来的真实表名
     */
    protected static function getTableFromSqlTable($table){
        if(strstr($table,'.')){
            $table = end(explode('.',$table));
        }
        $table = trim($table, '`');
        return $table;
    }
    
    /**
     * 通过SQL解析哈希参数hash_key
     * @param  string $operation   SQL操作类型 如inert update等
     * @param  string $sql         执行的SQL语句
     * @param  string $hash_column 哈希列名 一般是主键
     * @param  array  $params      绑定的参数
     * @return string/false        hash_key
     */
    protected static function getHashKeyFromSql($operation, $sql, $hash_column, $params = array()){
        switch (strtolower(substr($operation, 0, 6))) {
            
            case 'update':
            case 'delete':
            case 'select':
                $hash_key = self::getHashKeyFromSelectSQL($sql, $hash_column, $params);
                break;
                
            case 'insert':
            case 'replac':
                $hash_key = self::getHashKeyFromInsertSQL($sql, $hash_column, $params);
                break;
                
            default:
                self::trigger_error(E::EC_DB_HASH_ERR,'hash_key_not_found 操作解析失败'.$operation);
                return false;
                break;
        }
        return $hash_key ;
    }
    
    /**
     * 从insert replece 的查询语句解析出hash_key
     * @param 	string 	SQL语句
     * @param   string  hash的列名 一般是主键
     * @param 	array 	绑定的参数 [defaut array[]]
     * @return  string/false hash_key
     */
    protected static function getHashKeyFromInsertSQL($sql, $hash_column, $params = array()){
        //insert into user_info(sex,`uid`,addtime) values(1,?,now());
        $ptn1 = '/\((.*?)\)[\s]*values[\s]*\((.+)\)/i';
        $num = preg_match($ptn1, strstr($sql,'('), $match1);
        $column = explode(',', $match1[1]);
        $values = explode(',', $match1[2]);
        foreach ($column as $key => $v) {
            if(trim(trim($v),'`') == $hash_column) {
                $column_num = $key;
                break;
            }
        }
        $params_key = 0;
        foreach ($values as $key => $v) {
            if($column_num == $key) {
                $hash_value = trim($v);
                if($hash_value == '?') {
                    $hash_key = $params[$params_key];
                } else {
                    $hash_key = trim($v, "'");
                }
                break;
            }
            if(trim($v) == '?') {
                $params_key++;
            }
        }
        return $hash_key ;
    }
    
    /**
     * 从select update delect 的查询语句解析出hash_key
     * @param 	string 	SQL语句
     * @param   string  hash的列名 一般是主键
     * @param 	array 	绑定的参数 [defaut array[]]
     * @return  string/false hash_key
     */
    protected static function getHashKeyFromSelectSQL($sql, $hash_column, $params = array()){
        //update user_info set sex=? where isgood=1 and uid=234
        $ptn1 = "/{$hash_column}[\s`=':]+([\?\w]+)['\s]*/i";
        $num = preg_match($ptn1, stristr($sql,'where'), $match1);
        if(empty($match1[1])) {
            //var_dump($hash_column,$match1,stristr($sql,'where'),$ptn1);
            //return '';
            self::trigger_error(E::EC_DB_HASH_ERR,'hash_key_not_found 主键未找到',[$sql, $hash_column, $params]);
            return false;
        } elseif($match1[1] == '?'){
            $param_key = substr_count($sql, '?', 0, stripos($sql, $match1[0])) ;
            $hash_key = $params[$param_key];
        } else {
            $hash_key = $params[$match1[1]];
        }
        // if($_GET['ring'])var_dump('sss',$match1,$hash_key,$params);
        return $hash_key ;
    }
    
    /**
     * 内部错误触发器
     * @param  int $errno 错误码
     * @param  string $error 错误信息
     * @return null
     */
    protected static function trigger_error($errno, $error, $data = null, $line = null, $file = null){
        if(KIS_TRACE_MODE/* && Controller::debug() */) {
            //var_dump($errno, $error);
            $line && $line = 'on Line:'.$line;
            switch ($errno) {
                case E::EC_DB_CONFIG_CONFLICT:
                    echo "数据库配置的表名重复(表名已经在其他配置文件中存在)：配置文件{$data[0]}中的{$data[1]}" . PHP_EOL;
                    break;
                case E::EC_DB_QUERY_TIMEOUT:

                    break;
                case E::EC_DB_HASH_ERR:
                    $error .=' SQL中的哈希信息(主键)不存在，不知道要哈希到哪个表上' ;

                default:
                    echo "Errno:$errno, Error:$error $line ".__FILE__.PHP_EOL;
                    echo "<pre>";
                    var_dump($data);
                    debug_print_backtrace();
                    // var_dump(debug_backtrace());
                    break;
            }
        }
        is_array($error) && $error = json_encode($error);
        is_array($data) && $data = json_encode($data);

        if(isset($_SERVER['REQUEST_URI'])) {
            $uri = (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $_SERVER['REQUEST_URI'];
        } else {
            $uri = KIS_SCRIPT_NAME;
        }

        $msg = "{$errno}\t{$error}\t{$data}\t{$uri}";
        if($errno == E::EC_DB_QUERY_TIMEOUT) {
            E::sendErrorLog('/nds/db/timetolong', $msg);
        } else {
            E::sendErrorLog('/nds/db/error', $msg);
        }
    }
    
    protected static function getConfig($dir_name, $config){
        if (trim(explode("_", $config)[0], DS) == 'database') {
            $method = 'load';
        } else {
            $method = 'get';
        }
        // if($_GET['ring'])var_dump('ddddd',($config[0] == DS ? trim($config, DS) : ($dir_name.$config)),Config::$method($config[0] == DS ? trim($config, DS) : ($dir_name.$config)));
        return Config::$method($config[0] == DS ? trim($config, DS) : ($dir_name.$config));
    }
    
    /**
     * 魔术方法 返回私有静态成员的静态方法
     * @param  string $func      魔术静态方法 即要访问的私有静态成员名
     * @param  array  $arguments 预留参数 可选
     * @return mix            	 若存在该私有静态成员则返回
     */
    public static function __callStatic($func, $arguments = array()){
        if(isset(self::$$func)) {
            return self::$$func;
        }
    }
    
    public static function getDBError(){
        return self::$PDOStatement->errorInfo();
    }
}