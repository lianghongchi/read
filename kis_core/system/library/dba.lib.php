<?php
/**
 * database agent
 * 快速使用db的二次封装
 *
 * @package     KIS
 * @author      peven <qiufeng@dianjiemian.cn>
 * @since       2018-11-21
 * @example
 *
 */

class DBA {

	/**
	 * 通过表名和 数据 执行 insert sql
	 * @param  string  $table         表名
	 * @param  [type]  $data          要插入的数据 key value组成的数组
	 * @param  boolean $hash_need_md5 同insert方法
	 * @return [type]                 同insert方法
	 */
	public static function insert($table, $data , $hash_need_md5 = true){
		$sql = self::insertSql($table, $data);
		return db::insert($sql, $data, $hash_need_md5);
	}

	/**
	 * 通过表名和 数据 执行update sql
	 * @param  string  $table         表名
	 * @param  array   $data          需要更新的SET数据   key value组成的数组
	 * @param  array   $where_data    需要更新的WHERE条件 key value组成的数组
	 * @param  boolean $hash_need_md5 同update方法
	 * @return [type]                 同update方法
	 */
	public static function update($table, $data , $where_data , $hash_need_md5 = true){
		$sql = self::updateSql($table, $data, $where_data);
		return db::update($sql, $data , $hash_need_md5 );
	}

	public static function getOne($table, $where_data, $columns = ' * ', $hash_need_md5 = true){
		$sql = "SELECT {$columns} FROM {$table} ". self::whereString($where_data);
		return db::getOne($sql, $where_data, $hash_need_md5);
	}

	public static function getAll($table, $where_data, $columns = ' * ', $with_column_key = false , $hash_need_md5 = true){
		$sql = "SELECT {$columns} FROM {$table} ". self::whereString($where_data);
		return db::getAll($sql, $where_data, $with_column_key , $hash_need_md5);
	}

	public static function delete($table, $where_data, $hash_need_md5 = true){
		$sql = "DELETE FROM {$table} ". self::whereString($where_data);
		return db::delete($sql, $where_data, $hash_need_md5);
	}


	/**
	 * 创建插入的SQL
	 * @param  [type] $table [description]
	 * @param  [type] $data  [description]
	 * @return [type]        [description]
	 */
	public static function insertSql($table, $data){
		$columns = [];
		$values  = [];
		foreach ($data as $key => $v) {
			$columns[] = "`{$key}`";
			$values[]  = ":{$key}";
		}
		$columns_str = implode(',', $columns);
		$values_str  = implode(',', $values);
		$sql = "INSERT INTO `{$table}` ({$columns_str})
				VALUES ({$values_str})";
		return $sql;
	}

	/**
	 * 创建更新的SQL
	 * @param  [type] $table      [description]
	 * @param  [type] &$data      [description]
	 * @param  [type] $where_data [description]
	 * @return [type]             [description]
	 */
	public static function updateSql($table, &$data, $where_data){
		$sets = [];
		$values  = [];
		foreach ($data as $key => $v) {
			$sets[] = "`{$key}`=:{$key}";
		}
		foreach ($where_data as $key => $v) {

			if(isset($data[$key])){
				//可能有BUG
				$where[] = "`{$key}`=:{$key}_D905R";
				$data[$key.'_D905R'] = $v;
			} else {
				$where[] = "`{$key}`=:{$key}";
				$data[$key] = $v;
			}

		}
		$sets_str = implode(',', $sets);
		$where_str  = implode(' AND ', $where);
		$sql = "UPDATE `{$table}`
				SET {$sets_str}
				WHERE {$where_str}";
		return $sql;
	}

	/**
	 * 创建where语句 delete使用
	 * @param  array  $where_data [description]
	 * @return [type]             [description]
	 */
	public static function whereString($where_data = []){
		$where = [];
		foreach ($where_data as $key => $v) {
			$where[] = "`{$key}`=:{$key}";
		}
		if($where) {
			$where_str  = ' WHERE '. implode(' AND ', $where);
		} else {
			$where_str  = '';
		}
		return $where_str;
	}
}
