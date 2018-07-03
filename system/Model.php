<?php

namespace System;

/**
 * 
 * @author Ding<beyondye@gmail.com>
 */
class Model extends \System\System
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
     * 验证的不合法字段
     * 
     * @var array
     */
    public $illegalFields = [];

    /**
     * 缺少的必要字段
     * 
     * @var array
     */
    public $incompleteFields = [];

    /**
     * 不是数据库的字段
     * 
     * @var array
     */
    public $notMemberFields = [];

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
     * 构造函数
     */
    public function __construct()
    {
        
    }

    public function all()
    {
        return $this->select([]);
    }

    public function where($where)
    {
        return $this->select(['where' => $where]);
    }

    public function lastid()
    {
        return $this->db($this->RDB)->insert_id;
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
     * @return boolean
     */
    public function delete($where = [])
    {

        if (is_int($where)) {
            $where = [$this->primary => $where];
        }

        return $this->db($this->WDB)->delete($this->table, $where);
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
            $where = [$this->primary => $where];
        }

        return $this->db($this->WDB)->update($this->table, $data, $where);
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
        return $this->db($this->RDB)->query($sql);
    }

    /**
     * 查询表数据，没有参数返回全部
     * 
     * @param array $condtion ['where' => [], 'fields' => [], 'orderby' => [], 'limit' => []]
     * 
     * @return array|object
     */
    public function select($condtion = [])
    {
        return $this->db($this->RDB)->select($this->table, $condtion)->result();
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
     * 构造表的预览数据结构，以便于快速编码，必须按照自己需求修改
     * 
     * @return string
     */
    public function schemaPrecode()
    {

        $data_type = [
            'bigint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'char' => ['regx' => '/^\S+$/', 'msg' => ''],
            'decimal' => ['regx' => '/^[\d\.]+$/', 'msg' => ''],
            'double' => ['regx' => '/^[\d\.]+$/', 'msg' => ''],
            'float' => ['regx' => '/^\[d\.]+$/', 'msg' => ''],
            'int' => ['regx' => '/^\d+$/', 'msg' => ''],
            'longtext' => ['regx' => '/^\S+$/', 'msg' => ''],
            'mediumint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'mediumtext' => ['regx' => '/^\S+$/', 'msg' => ''],
            'smallint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'text' => ['regx' => '/^\S+$/', 'msg' => ''],
            'time' => ['regx' => '/^\d+$/', 'msg' => ''],
            'tinyint' => ['regx' => '/^\d+$/', 'msg' => ''],
            'varchar' => ['regx' => '/^\S+$/', 'msg' => '']
        ];

        $data = [];

        if ($this->table) {

            $database = $this->db($this->RDB)->config['database'];
            $sql = "select * from information_schema.columns where table_schema='{$database}' and table_name='{$this->table}' order by ordinal_position asc";
            $data = $this->db($this->RDB)->query($sql)->result();

            $content = '[';
            foreach ($data as $rs) {
                $content .= "\n'{$rs->COLUMN_NAME}'=> [
                'validate'=>['regex' =>'{$data_type[$rs->DATA_TYPE]['regx']}', 'message' =>'{$rs->COLUMN_COMMENT}不能为空'],
                'literal'=>'{$rs->COLUMN_COMMENT}',
                'default'=>'{$rs->COLUMN_DEFAULT}',
                'required'=>true
            ],  ";
            }
        }

        return $content . '];';
    }

    /**
     * 验证数据合法性，非法字段保存于$this->illegalFields
     * 
     * @param array $data 需要和schema key名一致
     * 
     * @return boolean
     */
    public function validate($data)
    {
        if (!$data) {
            return false;
        }

        $pass = true;
        foreach ($data as $key => $value) {

            if (isset($this->schema[$key]['validate']) && $this->schema[$key]['validate']) {
                if (is_array($value)) {
                    foreach ($value as $rs) {
                        if (preg_match("{$this->schema[$key]['validate']['regex']}", $rs) == 0) {
                            $this->illegalFields[] = $key;
                            $pass = false;
                        }
                    }
                } else if (preg_match("{$this->schema[$key]['validate']['regex']}", $value) == 0) {
                    $this->illegalFields[] = $key;
                    $pass = false;
                }
            }
        }

        return $pass;
    }

    /**
     * 验证是否缺少必要字段，缺少的必要字段保存于$this->incompleteFields
     * 
     * @param array $data 比较数据
     * 
     * @return boolean
     */
    public function complete($data)
    {
        if (!is_array($data)) {
            return false;
        }

        $required = [];
        foreach ($this->schema as $key => $value) {
            if (isset($value['required']) && $value['required'] == true) {
                $required[] = $key;
            }
        }

        $pass = true;
        foreach ($required as $rs) {
            if (!isset($data[$rs])) {
                $this->incompleteFields[] = $rs;
                $pass = false;
            }
        }

        return $pass;
    }

    /**
     * 与schema默认数据合并，并且清理不存在于schema里面的字段，不是成员的字段保存于$this->notMemberFields
     * 
     * @param array $data 并入schema的数据
     * @param array $without 不需要的字段
     * 
     * @return array 与schema默认合并后的数据
     */
    public function merge($data, $without = [])
    {

        if (!is_array($data)) {
            return false;
        }

        $fields = [];
        foreach ($this->schema as $key => $value) {
            if (isset($value['default'])) {
                $fields[$key] = $value['default'];
            }
        }

        $given = [];
        foreach ($data as $key => $value) {
            if (isset($this->schema[$key])) {
                $given[$key] = $value;
            } else {
                $this->notMemberFields[] = $key;
            }
        }

        $merge = array_merge($fields, $given);

        foreach ($without as $rs) {
            if (isset($merge[$rs])) {
                unset($merge[$rs]);
            }
        }

        return $merge;
    }

}
