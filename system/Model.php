<?php

namespace system;

use system\System;

/**
 * model基类
 *
 * @author Ding<beyondye@gmail.com>
 */
class Model extends System
{
    /**
     * 操作的表名
     *
     * @var string
     */
    public $table = '';

    /**
     * 表主键字段名
     *
     * @var string
     */
    public $primary = '';

    /**
     * 表结构
     *
     * @var array
     */
    public $schema = [];

    /**
     * 读数据库名
     *
     * @var string
     */
    public $RDB = 'default';

    /**
     * 写数据库名
     *
     * @var string
     */
    public $WDB = 'default';

    /**
     * 加载safe 字段验证
     *
     * @param string $name
     * @return object
     */
    public function __get($name)
    {
        if ($name == 'safe') {
            return $this->load(ucfirst($name), 'system', 'safe_' . $this->table, $this->schema);
        }

        return parent::__get($name);
    }

    /**
     * 获取表全部记录
     *
     * @param array $fields 返回字段
     * @return object array
     */
    public function all($fields = [])
    {
        return $this->select(['fields' => $fields]);
    }

    /**
     * 通过sql where条件获取数据
     *
     * @param array $where 条件过滤
     * @param array $fields 返回字段
     * @return object array
     */
    public function where($where, $fields = [])
    {
        return $this->select(['where' => $where, 'fields' => $fields]);
    }

    /**
     * 获取最后插入的主键id
     *
     * @return int
     */
    public function lastid()
    {
        return $this->db($this->WDB)->insert_id;
    }

    /**
     * 按条件获取表数据条数
     *
     * @param array|string $where 必须与表字段对应 $where['field_name'=>'field_value']
     *
     * @return int
     */
    public function count($where = [])
    {
        return $this->db($this->RDB)->select($this->table, ['where' => $where, 'fields' => ' COUNT(*) AS ct '])->row()->ct;
    }

    /**
     * 插入数据到表
     *
     * @param array|string $data 必须与表字段对应 $data['field_name'=>'field_value']
     *
     * @return boolean
     */
    public function insert($data = [])
    {
        return $this->db($this->WDB)->insert($this->table, $data);
    }

    /**
     * 删除表数据
     *
     * @param array|int|string $where
     *
     * array必须与表字段对应 $where['field_name'=>'field_value'],
     * int类型 必须是主键值
     *
     * @return boolean|int 删除成功返回影响行数不然返回false
     */
    public function delete($where = [])
    {

        if (is_numeric($where)) {
            $where = [$this->primary => $where];
        }

        if ($this->db($this->WDB)->delete($this->table, $where)) {

            return $this->db($this->WDB)->affected_rows;
        }

        return false;
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
     * @return boolean|int 更新成功返回影响行数不然返回false
     */
    public function update($data, $where = [])
    {

        if (is_numeric($where)) {
            $where = [$this->primary => $where];
        }

        if ($this->db($this->WDB)->update($this->table, $data, $where)) {

            return $this->db($this->WDB)->affected_rows;
        }

        return false;
    }

    /**
     * 原生sql查询表数据，没有参数返回全部
     *
     * @param string $sql
     *
     * @return mix
     */
    public function query($sql)
    {
        if (!$sql) {
            return false;
        }

        $pieces = explode(' ', trim($sql));
        $db = strtolower($pieces[0]) == 'select' ? $this->RDB : $this->WDB;

        return $this->db($db)->query($sql);
    }

    /**
     * 查询表数据，没有参数返回全部
     *
     * @param array $condtion ['where' => [], 'fields' => [], 'orderby' => [], 'limit' => []]
     *
     * @return array|object
     */
    public function select($condition = [])
    {
        return $this->db($this->RDB)->select($this->table, $condition)->result();
    }

    /**
     * 通过主键返回一条数据
     *
     * @param int|array  表主键或唯一索引数组
     *
     * @return array
     */
    public function one($primary)
    {
        if (is_array($primary)) {
            $data = $this->select(['where' => $primary, 'limit' => 1]);
        } else {
            $data = $this->select(['where' => [$this->primary => $primary], 'limit' => 1]);
        }

        return isset($data[0]) ? $data[0] : null;
    }


    /**
     * 一对一
     *
     * @param string $model 关联的model
     * @param string|int $primary_value 主键唯一值
     *
     * @return array|object
     */
    public function hasOne($model, $primary_value)
    {
        $local = $this->one($primary_value);
        $foreign = $this->model($model)->one($primary_value);

        if ($local) {

            if ($foreign) {

                foreach ($foreign as $key => $value) {
                    $local->$key = $value;
                }
            }

            return $local;
        }

        return null;
    }

    /**
     * 一对多
     *
     * @param string $model 需要关联的model
     *
     * @param array $where ['foreign_name' => 'local_primary_value']
     * @subparam string $foreign 外表字段名
     * @subparam string|int $local_primary_value 本表主键值
     *
     * @param array $condition 参见$this->select()参数
     *
     * @return array|object
     */
    public function hasMany($model, $where = [], $condition = [])
    {
        if (!$where) {
            return null;
        }

        $defaut = ['where' => $where, 'fields' => [], 'orderby' => [], 'limit' => []];

        return $this->model($model)->select(array_merge($defaut, $condition));

    }

    /**
     * 多对多
     *
     * @param string $model 需要关联的model名称
     * @param string $relation_model 关系表model名称
     * @param string $relation_foreign_name 关联表主键名在关系表中的字段名
     *
     * @param array $where ['local_relation_filed_name' => 'local_primary_value']
     * @subparam string $local_relation_filed_name 本表在关系表字段名
     * @subparam string|int $local_primary_value 本表主键值
     *
     * @param array $condition 参见$this->select()参数
     *
     * @return array|object
     */
    public function belongsTo($model, $relation_model, $relation_foreign_name, $where = [], $condition = [])
    {
        if (!$where) {
            return null;
        }

        $relation = $this->model($relation_model)->where($where, [$relation_foreign_name]);

        if ($relation) {
            $primaries = [];

            foreach ($relation as $rs) {
                $primaries[] = $rs->$relation_foreign_name;
            }

            $foreign = $this->model($model);
            $defaut = ['where' => ["{$foreign->primary} in" => join($primaries)], 'fields' => [], 'orderby' => [], 'limit' => []];

            return $foreign->select(array_merge($defaut, $condition));

        }

        return null;

    }

    /**
     * 构造表的预览数据结构，以便于快速编码，必须按照自己需求修改
     *
     * @return string
     */
    public function printSchema()
    {

        $data_type = [
            'bigint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'char' => ['regx' => '/^\S+$/', 'msg' => ''],
            'decimal' => ['regx' => '/^[\d\.]+$/', 'msg' => ''],
            'double' => ['regx' => '/^[\d\.]+$/', 'msg' => ''],
            'float' => ['regx' => '/^\[\d\.]+$/', 'msg' => ''],
            'int' => ['regx' => '/^\d+$/', 'msg' => ''],
            'longtext' => ['regx' => '/^\S+[\s\S]+\S+$/', 'msg' => ''],
            'mediumint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'mediumtext' => ['regx' => '/^\S+[\s\S]+\S+$/', 'msg' => ''],
            'smallint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'text' => ['regx' => '/^\S+[\s\S]+\S+$/', 'msg' => ''],
            'time' => ['regx' => '/^\d+$/', 'msg' => ''],
            'tinyint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'varchar' => ['regx' => '/^\S+[\s\S]+\S+$/', 'msg' => '']
        ];

        $data = [];

        if ($this->table) {

            $database = $this->db($this->RDB)->config['database'];
            $sql = "select * from information_schema.columns where table_schema='{$database}' and table_name='{$this->table}' order by ordinal_position asc";
            $data = $this->db($this->RDB)->query($sql)->result();

            $content = '[';
            foreach ($data as $rs) {

                $filter = '';
                if (in_array($rs->DATA_TYPE, ['longtext', 'mediumtext', 'text', 'varchar'])) {
                    $filter = 'tag|blank';
                }

                $content .= "\n'{$rs->COLUMN_NAME}'=> [
                'validate'=>['regex' =>'{$data_type[$rs->DATA_TYPE]['regx']}', 'message' =>'{$rs->COLUMN_COMMENT}不能为空'],
                'filter'=>'{$filter}',
                'literal'=>'{$rs->COLUMN_COMMENT}',
                'default'=>'{$rs->COLUMN_DEFAULT}',
                'required'=>true
            ],  ";
            }
        }

        return $content . '];';
    }

}
