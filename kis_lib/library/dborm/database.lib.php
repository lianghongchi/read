<?php
/**
 * mysql sql 条件构建类
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.com>
 * @since       2018-11-23
 *
 */

class lib_dborm_database
{



    protected $builder = null;

    protected $tableName  = null;

    protected $condition  = [];

    protected $hooks = [];



    public function __construct($tableName)
    {
        $this->tableName = $tableName;

        $this->builder = new lib_dborm_builder();
    }

    public function distinct($isDistinct = true)
    {
        if ($isDistinct) {
            $this->condition['distinct'] = $isDistinct;
        } else {
            unset($this->condition['distinct']);
        }
        return $this;
    }

    public function setDefTable($table = '', $alias = '')
    {
        if ($table) {
            $this->condition['table'] = [$table, $alias];
        }

        if (!isset($this->condition['table'])) {
            $this->condition['table'] = [$this->tableName, $alias];
        }

        return $this;
    }

    public function setDefField()
    {
        if (!isset($this->condition['field'])) {
            $this->condition['field'] = '*';
        }

        return $this;
    }

    public function field($field = '')
    {
        $field && $this->condition['field'] = $field;
        return $this;
    }

    public function from($from = '', $alias = '')
    {
        if (is_string($from)) {
            $this->condition['from'] = [$from, $alias];
        } else if (is_array($from)) {
            $this->condition['from'] = $from;
        }

        return $this;
    }

    public function force($force = '')
    {
        $force && $this->condition['force'] = $force;
		return $this;
    }

    public function alias($name = null)
    {
        $name && $this->setDefTable($this->tableName, $name);
        return $this;
    }

    public function join($join = '')
    {
        $join && $this->condition['join'] = $join;
        return $this;
    }

    public function where($where = '', $value = null)
    {
        $thisWhere = $this->condition['where'] ?? [];

        if (is_string($where) && $where) {
            $twhere = func_num_args() > 1 ? [$where => $value] : $where;
            $this->condition['where'] = array_merge($thisWhere, $twhere);
        } else if (is_array($where) && $where) {
            $this->condition['where'] = array_merge($thisWhere, $where);
        }

        return $this;
    }

    public function orWhere($where = '', $value = null)
    {
        isset($this->condition['orwhere']) || $this->condition['orwhere'] = [];

        if (is_string($where) && $where) {
            $twhere = func_num_args() > 1 ? [$where => $value] : $where;
            $this->condition['orwhere'] = array_merge($this->condition['orwhere'], $twhere);
        } else if (is_array($where) && $where) {
            $this->condition['orwhere'] = array_merge($this->condition['orwhere'], $where);
        }

        return $this;
    }

    public function clearWhere()
    {
        $this->condition['where'] = [];
        return $this;
    }

    public function group($field = '')
    {
        $field && $this->condition['group'] = $field;
        return $this;
    }

    // 用于配合group方法完成从分组的结果中筛选（通常是聚合条件）数据
    public function having($having = '')
    {
        $having && $this->condition['having'] = $having;
        return $this;
    }

    public function order($order = '', $type = '')
    {
        $order && $this->condition['order'] = $type ? [$order, $type] : $order;
        return $this;
    }

    public function limit($index = 0, $length = 0)
    {
        if ($index || $length) {
            $this->condition['limit'] = $length ? [intval($index), intval($length)] : [0, intval($index)];
        }
        return $this;
    }

    // 用于合并两个或多个 SELECT 语句的结果集
    public function union($union = '')
    {
        $union && $this->condition['union'] = $union;
        return $this;
    }

    public function lock($lock = true)
    {
        $this->condition['lock'] = $lock;
        return $this;
    }

    public function carry($field, $number = 1)
    {
        if (is_string($field)) {
            $this->condition['update'] = [$field => [$field, $number]];
        } else {
            foreach ($field as $key => $value) {
                $this->condition['update'][$key] = [$key, $value];
            }
        }
        return $this;
    }

    protected function throwException($info)
    {
        throw new Exception($info);
    }

    public function clearCondition()
    {
        $this->condition = [];
    }

    public function getCondition()
    {
        return $this->condition;
    }

    public function insert(array $data, $replace = false)
    {
        if (!$data) {
            $this->throwException('no data to insert');
        }

        $this->condition['insert'] = $replace;
        $this->condition['add'] = $data;

        $this->setDefTable();

        list($sql, $binds) = $this->builder->insert($this->getCondition());
        $this->clearCondition();

        if (false === $this->hook('insert_before')) {
            return false;
        }

        return db::insert($sql, $binds);
    }

    public function delete()
    {
        if (!isset($this->condition['where']) || !$this->condition['where']) {
            $this->throwException('no where data to delete');
        }

        $this->setDefTable();

        list($sql, $binds) = $sql = $this->builder->delete($this->getCondition());
        $this->clearCondition();

        if (false === $this->hook('delete_before')) {
            return false;
        }

        return db::delete($sql, $binds);
    }

    public function update($data = null, $value = '')
    {
        if (is_string($data) && $data) {
            $data = [$data => $value];
        }

        if (is_array($data) && $data) {
            if (isset($this->condition['update']) && is_array($this->condition['update'])) {
                $this->condition['update'] = array_merge($this->condition['update'], $data);

            } else {
                $this->condition['update'] = $data;
            }
        }

        if (!isset($this->condition['where']) || !$this->condition['where']) {
            $this->throwException('no where data for udpate');
        }

        if (!isset($this->condition['update']) || !$this->condition['update']) {
            $this->throwException('no data to udpate');
        }

        $this->setDefTable();

        list($sql, $binds) = $this->builder->update($this->getCondition());
        $this->clearCondition();

        if (false === $this->hook('update_before')) {
            return false;
        }

        return db::update($sql, $binds);
    }

    public function getSql()
    {
        $this->setDefTable();
        $this->setDefField();

        $this->hook('select_before');

        list($sql, $binds) = $this->builder->select($this->getCondition());
        $this->clearCondition();

        return [$sql, $binds];
    }

    protected function hook($event, $params = [])
    {
        $result = true;
        if (isset($this->hooks[$event])) {
            $callback = $this->hooks[$event];
            $result = call_user_func_array($callback, [$this, $params]);
        }
        return $result;
    }

    public function registHook($name, $callback)
    {
        if (!is_callable($callback)) {
            $this->throwException('the callback is invalid');
        }

        $this->hooks[$name] = $callback;
    }

    public function select()
    {
        list($sql, $binds) = $this->getSql();

        return db::select($sql, $binds);
    }

    public function selectWithUnique($columnKey = '')
    {
        list($sql, $binds) = $this->getSql();

        return db::select($sql, $binds, $columnKey);
    }

    public function selectChunk($count, $callback, $maxCount = 0)
    {
        $this->setDefTable();
        $this->setDefField();

        $start = 0;

        ($maxCount > 0 && $count > $maxCount) && ($count = $maxCount);

        do {
            $this->limit($start, $count);
            list($sql, $binds) = $this->getSql();

            $results  = db::select($sql, $binds);
            $resCount = count($results);

            if (!$results || $resCount == 0) {
                break ;
            }

            $state = call_user_func($callback, $results, $start, $count);

            if ($state === false) {
                break ;
            }

            $start += $count;

            if ($maxCount > 0 && $start > $maxCount) {
                if ($start > $maxCount) {
                    break ;
                }
                $count = $count - ($start + $count - $maxCount);
            }
        }
        while ($resCount == $count);

        $this->clearCondition();

        return true;
    }

    public function find()
    {
        $this->limit(1);
        list($sql, $binds) = $this->getSql();

        return db::getOne($sql, $binds);
    }

    public function sum($field)
    {
        if (!$field) {
            $this->throwException('no field to sum');
        }

        $this->field("IFNULL(sum({$field}),0) AS get_sum");

        $data = $this->find();

        if ($data === false) {
            return false;
        }

        return $data['get_sum'] ?? 0;
    }

    public function count($field = '')
    {
        $field || $field = '*';

        $this->field("count({$field}) AS get_count");

        $data = $this->find();

        if ($data === false) {
            return false;
        }

        return $data['get_count'] ?? 0;
    }

    public function max($field)
    {
        if (!$field) {
            $this->throwException('no field to max');
        }

        $this->field("max({$field}) AS get_max");

        $data = $this->find();

        if ($data === false) {
            return false;
        }

        return $data['get_max'] ?? 0;
    }

    public function min($field)
    {
        if (!$field) {
            $this->throwException('no field to min');
        }

        $this->field("min({$field}) AS get_min");

        $data = $this->find();

        if ($data === false) {
            return false;
        }

        return $data['get_min'] ?? 0;
    }

    public function avg($field)
    {
        if (!$field) {
            $this->throwException('no field to avg');
        }

        $this->field("avg({$field}) AS get_avg");

        $data = $this->find();

        if ($data === false) {
            return false;
        }

        return $data['get_avg'] ?? 0;
    }



}
