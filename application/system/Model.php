<?php

namespace system;

use \system\Database as DB;

class Model
{
    /**
     * 操作的表名
     * @var string
     */
    public string $table = '';

    /**
     * 表主键字段名
     * @var string
     */
    public string $primary = '';

    /**
     * 表结构
     * @var array
     */
    public array $schema = [];

    /**
     * 读数据库名
     * @var string
     */
    public string $RDB = 'default';

    /**
     * 写数据库名
     * @var string
     */
    public string $WDB = 'default';


    /**
     * 获取表全部记录
     * @param array $fields 返回字段
     * @return object array
     */
    public function all(array $fields = [])
    {
        return $this->select(['fields' => $fields]);
    }

    /**
     * 通过sql where条件获取数据
     * @param array $where 条件过滤
     * @param array $fields 返回字段
     * @return object|array
     */
    public function where(array $where, array $fields = [])
    {
        return $this->select(['where' => $where, 'fields' => $fields]);
    }

    /**
     * 获取最后插入的主键id
     * @return int
     */
    public function lastid()
    {
        return DB::instance($this->WDB)->insert_id;
    }

    /**
     * 按条件获取表数据条数
     * @param array $where 必须与表字段对应 $where['field_name'=>'field_value']
     * @return int
     */
    public function count(array $where = [])
    {
        return DB::instance($this->RDB)->select($this->table, ['where' => $where, 'fields' => " COUNT({$this->primary}) AS ct "])->row()->ct;
    }

    /**
     * 插入数据到表
     * @param array $data 必须与表字段对应 $data['field_name'=>'field_value']
     * @return boolean
     */
    public function insert(array $data = [])
    {
        return DB::instance($this->WDB)->insert($this->table, $data);
    }


    /**
     * 新建数据行或已存在即替换
     * @param array $data 必须与表字段对应 $data['field_name'=>'field_value']
     * @return boolean
     */
    public function replace(array $data = [])
    {
        return DB::instance($this->WDB)->replace($this->table, $data);
    }

    /**
     * 删除表数据
     *
     * @param array|int $where
     * array必须与表字段对应 $where['field_name'=>'field_value'],
     * int类型 必须是主键值
     *
     * @return boolean
     */
    public function delete($where = [])
    {
        if (is_numeric($where)) {
            $where = [$this->primary, '=', $where];
        }

        return DB::instance($this->WDB)->delete($this->table, $where);
    }

    /**
     * 更新数据到表
     *
     * @param array|string $data 必须与表字段对应 $data['field_name'=>'field_value']
     *
     * @param array|int|string $where
     * array必须与表字段对应 $where['field_name'=>'field_value'],
     * int类型 必须是主键值
     *
     * @return boolean
     */
    public function update($data, $where = [])
    {
        if (is_numeric($where)) {
            $where = [[$this->primary, '=', $where]];
        }

        return DB::instance($this->WDB)->update($this->table, $data, $where);
    }

    /**
     * 原生sql查询带返回数据
     * @param string $sql sql语句
     * @return bool|database\mysqli\Result
     */
    public function query(string $sql)
    {
        if (!$sql) {
            return false;
        }

        return DB::instance($this->RDB)->query($sql);
    }


    /**
     * 原生sql查询，不带返回数据
     * @param string $sql
     * @return bool
     */
    public function execute(string $sql)
    {
        if (!$sql) {
            return false;
        }

        return DB::instance($this->WDB)->execute($sql);
    }

    /**
     * 查询表数据，没有参数返回全部
     * @param array $condition
     * @return array|object
     */
    public function select(array $condition = ['where' => [], 'fields' => [], 'orderby' => [], 'limit' => []])
    {
        return DB::instance($this->RDB)->select($this->table, $condition)->result();
    }

    /**
     * 通过主键返回一条数据
     * @param int|array $primary 表主键或唯一索引数组
     * @param array $fields 选择字段
     * @return object|null
     */
    public function one($primary, array $fields = [])
    {
        if (is_array($primary)) {
            $data = $this->select(['where' => $primary, 'fields' => $fields, 'limit' => 1]);
        } else {
            $data = $this->select(['where' => [[$this->primary, '=', $primary]], 'fields' => $fields, 'limit' => 1]);
        }

        return $data[0] ?? null;
    }

}
