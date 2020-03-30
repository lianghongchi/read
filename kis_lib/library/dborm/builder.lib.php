<?php
/**
 * mysql sql组装类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_dborm_builder
{


    /**
     * 操作类型：插入
     * @var integer
     */
    const TYPE_INSERT = 4;

    /**
     * 操作类型：删除
     * @var integer
     */
    const TYPE_DELETE = 3;

    /**
     * 操作类型：更新
     * @var integer
     */
    const TYPE_UPDATE = 2;

    /**
     * 操作类型：查询
     * @var integer
     */
    const TYPE_SELECT = 1;

    /**
     * 操作类型属性映射
     * @var array
     */
    protected $attributes = [
        self::TYPE_INSERT => ['insert', 'table', 'add'],
        self::TYPE_DELETE => ['table', 'where'],
        self::TYPE_UPDATE => ['table', 'update', 'where'],
        self::TYPE_SELECT => ['distinct', 'field', 'table', 'force', 'join', 'where', 'orwhere','group', 'having', 'order', 'limit', 'union', 'lock'],
    ];


    /**
     * 查询操作关系列表
     * @var array
     */
    protected $relationMap = [
        '=', '<>', '!=', '<', '<=', '>', '>=', 'NOT BETWEEN', 'BETWEEN', 'NOT IN', 'IN', 'MORE', 'NOT LIKE', 'LIKE', 'REGEXP', 'NOT REGEXP'
    ];

    /**
     * 参数绑定值
     * @var array
     */
    protected $parameters = [];

    /**
     * 当前操作类型
     * @var int
     */
    protected $type = 1;

    /**
     * 操作条件数组
     * @var array
     */
    protected $data = [];

    /**
     * where 条件类型，分为 where 与 orwhere
     * @var array
     */
    protected $whereType = '';



    /**
     * 构建插入语句
     * @param  array $data 插入的数据
     * @return array 插入语句与绑定参数
     */
    public function insert($data)
    {
        $this->data = $data;
        $this->type = self::TYPE_INSERT;
        list ($sql, $binds) = $this->build();

        return [$sql, $binds];
    }

    /**
     * 构建删除语句
     * @param  array $data 删除条件
     * @return array 删除语句与绑定参数
     */
    public function delete($data)
    {
        $this->data = $data;
        $this->type = self::TYPE_DELETE;
        list ($sql, $binds) = $this->build(self::TYPE_DELETE);

        return ['DELETE FROM ' . $sql, $binds];
    }

    /**
     * 构建更新语句
     * @param  array $data 更新条件
     * @return array 更新语句与绑定参数
     */
    public function update($data)
    {
        $this->data = $data;
        $this->type = self::TYPE_UPDATE;
        list ($sql, $binds) = $this->build();

        return ['UPDATE ' . $sql, $binds ];
    }

    /**
     * 构建查询语句
     * @param  array $data 查询条件
     * @return array 查询语句与绑定参数
     */
    public function select($data)
    {
        $this->data = $data;
        $this->type = self::TYPE_SELECT;
        list ($sql, $binds) = $this->build();

        return ['SELECT ' . $sql, $binds];
    }

    /**
     * 关键字段添加引号
     * @param  string $key 字段
     * @return string
     */
    public function keyWrap($key)
    {
        if (strpos($key, '.') && !is_numeric($key)) {
            list ($name, $key) = explode('.', $key);
            return "{$name}.`{$key}`";
        }

        return is_numeric($key) ? $key : "`{$key}`";
    }

    /**
     * 添加绑定条件
     * @param string $type 绑定的参数类型
     * @param mix $value 绑定的参数值
     */
    protected function bindingParameter($type, $value)
    {
        if (is_array($value) || is_object($value)) {
            $this->throwException('array/object is not allowed to bind');
        }

        $this->parameters[$type][] = $value;
    }

    /**
     * 抛出错误
     * @param  string $info 错误信息
     * @return object Exception
     */
    protected function throwException($info)
    {
        throw new \Exception($info);
    }

    /**
     * where条件处理
     * @param  string $key where key
     * @param  string $relation where 关系
     * @param  string $value where 值
     * @return array
     */
    protected function whereWrap($key, $relation, $value)
    {
        return ['key' => $key, 'relation' => $relation, 'value' => $value];
    }

    /**
     * 构建语句
     * @return array 语句
     */
    public function build()
    {
        $this->parameters = [];
        $data = $this->data;

        $attribute = $this->attributes[$this->type];
        $section = [];

        foreach ($attribute as $key => $value) {
            $method = 'make'.ucfirst($value);

            if (isset($data[$value])) {
                $section[$value] = $this->$method($data[$value]);
            }
        }

        $sql   = '';
        $binds = [];

        foreach ($section as $key => $value) {
            $sql .= $value.' ';
            isset($this->parameters[$key]) && array_push($binds, ...$this->parameters[$key]);
        }

        return [$sql, $binds];
    }

    /**
     * 构建插入部分
     * @param  string $value 是否替换插入
     * @return string
     */
    protected function makeInsert($isReplace)
    {
        return $isReplace ? 'REPLACE INTO' : 'INSERT INTO';
    }

    /**
     * 构建表格部分
     * @param  array $from 表格参数
     * @return string
     */
    protected function makeTable($from)
    {
        if (!is_array($from)) {
            return '';
        }
        list ($fr, $as) = $from;
        return $this->keyWrap($fr) . ($as ? ' as '.$as : '');
    }

    /**
     * 构建插入 value部分
     * @param  array $datas 插入的值
     * @return [type] [description]
     */
    protected function makeAdd($datas)
    {
        $datas  = is_array(reset($datas)) ? $datas : [$datas];
        $column = implode(',', array_map([$this, 'keyWrap'], array_keys(reset($datas))));

        $values = array_map(function($value) {
            $valueString = '';
            foreach ($value as $key => $val) {
                $valueString .= '?,';
                $this->bindingParameter('add', $val);
            }
            return '(' . trim($valueString, ',') . ')';

        }, $datas);

        $values = trim(implode(',', $values), ',');

        return "({$column}) VALUES {$values}";
    }

    /**
     * 构建where部分
     * @param  string $wheres where条件
     * @return string
     */
    protected function makeWhere($wheres)
    {
        if (!$wheres) {
            return '';
        }

        $this->whereType = 'where';
        $whereString = '';

        if (is_string($wheres)) {
            $whereString = $this->makeWhereOfString($wheres);
        } else if (is_array($wheres)) {
            $whereString = $this->makeWhereOfArray($wheres, 'AND');
        } else {
            return '';
        }

        return 'WHERE '.$whereString;
    }

    /**
     * 构建orwhere部分
     * @param  array $wheres orwhere条件
     * @return string
     */
    protected function makeOrwhere($wheres)
    {
        if (!$wheres) {
            return '';
        }

        $this->whereType = 'orwhere';
        $whereString = '';

        if (is_string($wheres)) {
            $whereString = $this->makeWhereOfString($wheres);
        } else if (is_array($wheres)) {
            $whereString = $this->makeWhereOfArray($wheres, 'OR');
        } else {
            return '';
        }

        if (isset($this->data['where']) && $this->data['where']) {
            return "AND ( {$whereString} )";
        }

        return "WHERE {$whereString}";
    }

    /**
     * 构建string 类型 where
     * @param  [type] $where [description]
     * @return [type] [description]
     */
    protected function makeWhereOfString($where)
    {
        return $where;
    }

    /**
     * 构建数组类型where
     * @param  array $where where条件
     * @param  string $logic 关系类型
     * @return string
     */
    protected function makeWhereOfArray($where, $logic)
    {
        $whereString = '';

        $package = [];

        $this->makeWhereGetPackage($where, $package);

        foreach ($package as $key => $value) {
            $whereString .= $this->keyWrap($value['key'])." {$value['relation']} {$value['value']} {$logic} ";
        }

        return substr($whereString, 0, -4);
    }

    /**
     * 构建 数组类型的where条件的封装包
     * @param  array $where where条件
     * @param  array $package 封装包
     * @return string
     */
    protected function makeWhereGetPackage(array $where, &$package)
    {
    	foreach ($where as $key => $value) {
            if (is_array($value)) {

                $relation = strtoupper(reset($value));
                $tval = next($value);

                if (!in_array($relation, $this->relationMap)) {
                    $this->throwException('undefined relationship type:'.$relation);
                }

                $vals = '';

                switch ($relation) {
                    case 'BETWEEN':
                    case 'NOT BETWEEN':
                        $vals = $this->makeWhereByBT($tval);
                        break ;
                    case 'IN':
                    case 'NOT IN':
                        $vals = $this->makeWhereByIN($key, $tval);
                        break ;
                    case 'MORE':
						foreach ($tval as $k => $v) {
							$this->makeWhereGetPackage([$key => $v], $package);
						}
                        continue 2;
                        break ;
                    default :
                        $vals = $this->makeWhereByBasic($tval);
                        break ;
                }

            } else {
                $relation = '=';
                $vals  = $this->makeWhereByBasic($value);
            }

            $package[] = $this->whereWrap($key, $relation, $vals);
        }
    }

    /**
     * 从 BETWEEN 构建where部分
     * @param  array $tvalue BETWEEN值
     * @return string
     */
    protected function makeWhereByBT(array $tvalue)
    {
    	if (!$tvalue) {
    		$this->throwException("undefined 'BETWEEN' value");
    	}

        list ($beginValue, $endingValue) = $tvalue;

        $this->bindingParameter($this->whereType, $beginValue);
        $this->bindingParameter($this->whereType, $endingValue);

        $valString = '? and ?';

        return $valString;
    }

    /**
     * 从 IN 构建where部分
     * @param  [type] $tvalue IN值
     * @return string
     */
    protected function makeWhereByIN($tkey, $tvalue)
    {
        $valString = '';

        if (is_array($tvalue) && $tvalue) {

            foreach ($tvalue as $key => $value) {
                if (is_array($value)) {
                    $value = $value[$tkey] ?? '';
                }

                if (!$value || !(is_string($value) || is_numeric($value))) {
                    $this->throwException('the where IN value is undefined');
                }

                $this->bindingParameter($this->whereType, $value);
                $valString .= '?,';
            }

            $valString = '(' . rtrim($valString, ',') . ')';

        } else if (is_string($tvalue)) {
            $valString = $tvalue;
        }

        return $valString ? : "('')";
    }

    /**
     * 构建普通条件类型where
     * @param  string $tvalue where值
     * @return string [description]
     */
    protected function makeWhereByBasic($tvalue)
    {
        $this->bindingParameter($this->whereType, $tvalue);
        return '?';
    }

    /**
     * 构建更新条件部分
     * @param  array $data 更新的值
     * @return string
     */
    protected function makeUpdate(array $data)
    {
        $setString = '';

        foreach ($data as $key => $value) {
            $updateString = '';

            if (is_array($value)) {
                $updateString = implode('+', array_map(function($v){
                    return is_string($v) ? $this->keyWrap($v) : $v;

                }, $value));

            } else {
                $this->bindingParameter('update', $value);
                $updateString = '?';
            }

            $setString .= $this->keyWrap($key).' = '.$updateString . ',';
        }
        $setString = rtrim($setString, ', ');

        return 'SET ' . $setString;
    }

    /**
     * 构建field字段部分
     * @param  mix $field field字段
     * @return string
     */
    protected function makeField($field)
    {
        if (!$field) {
            return '* FROM';

        } else if (is_string($field)) {
            return $field . ' FROM';

        } else if (is_array($field)) {
            return implode(',', $field).' FROM';

        }
    }

    /**
     * 构建join部分
     * @param  mix $join join条件
     * @return string
     */
    protected function makeJoin($join)
    {
        if (is_string($join)) {
            return $join;
        }

        return implode(' ', $join);
    }

    /**
     * 构建group部分
     * @param  string $group group值
     * @return string
     */
    protected function makeGroup($group)
    {
        return $group ? 'GROUP BY '.$this->keyWrap($group) : '';
    }

    /**
     * 构建havaing部分
     * @param  string $having having值
     * @return string
     */
    protected function makeHaving($having)
    {
        return $having ? 'HAVING '.$having : '';
    }

    /**
     * 构建order by 部分
     * @param  string $order order by 值
     * @return string
     */
    protected function makeOrder($order)
    {
        if(is_string($order)) {
            return 'ORDER BY '.$order;
        }

        list ($name, $type) = $order;
        return "ORDER BY {$name} {$order}";
    }

    /**
     * 构建union部分
     * @param  string $union union值
     * @return string
     */
    protected function makeUnion($union)
    {
        return "UNION ($union)";
    }

    /**
     * 构建force部分
     * @param  string $value force值
     * @return string
     */
    protected function makeForce($value)
    {
        if (is_string($value)) {
            return 'FORCE INDEX ( '.$value.' ) ';
        }

        return 'FORCE INDEX ( '.implode(',', $value).' ) ';
    }

    /**
     * 构建distinct部分
     * @param  string $value distinct值
     * @return string
     */
    protected function makeDistinct($value)
    {
        return $value ? 'DISTINCT' : '';
    }

    /**
     * 构建limit部分
     * @param  mix $value limit 值
     * @return string
     */
    protected function makeLimit($value)
    {
        if (is_array($value)) {
            list ($start, $count) = $value;

            $this->bindingParameter('limit', $start);
            $this->bindingParameter('limit', $count);

            $limit = '?, ?';

        } else {
            $limit = $value;
        }

        return 'LIMIT '.$limit;
    }

    /**
     * 构建lock部分
     * @param  bool $lock 是否锁定
     * @return string
     */
    protected function makeLock($isLock)
    {
        return $isLock ? 'FOR UPDATE' : '';
    }


}
