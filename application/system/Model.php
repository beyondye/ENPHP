<?php

namespace system;

use Exception;
use system\Database as DB;
use system\model\Select;
use system\model\Safe;

class Model
{
    protected string $RDB = 'default';
    protected string $WDB = 'default';

    public string $table;
    public array $schema;
    public string $primary;

    protected array $objects = [
        'select' => null,
    ];

    const SER_DEFAULT = 0;
    const  SER_WRITE = 1;
    const  SER_READ = 2;

    public function setServer($name, $type = self::SER_DEFAULT): void
    {
        if ($type == self::SER_READ) {
            $this->RDB = $name;
        } else if ($type == self::SER_WRITE) {
            $this->WDB = $name;
        } else {
            $this->WDB = $name;
            $this->RDB = $name;
        }
    }

    public function selectDb($db): bool
    {
        if (DB::instance($this->RDB)->selectDb($db) &&
            DB::instance($this->WDB)->selectDb($db)) {
            return true;
        }

        return false;
    }


    public function select(string|array $fields = []): object
    {

        if ($this->objects['select']) {
            $select = $this->objects['select'];
        } else {
            $select = new Select();
            $select->table = $this->table;
            $select->schema = $this->schema;
            $select->primary = $this->primary;
            $this->objects['select'] = $select;
        }

        $select->fields = $fields;
        $select->db = $this->RDB;
        $select->wheres = [];

        return $select;
    }


    public function delete(array|string|int ...$wheres): bool
    {
        $wheres = $this->where($wheres);
        return DB::instance($this->WDB)->delete($this->table, $wheres);
    }


    public function update(array $data, array|int|string ...$wheres): bool
    {
        $safe = new Safe($this->schema);
        $data = $safe->clear($data);

        if (empty($data)) {
            throw new Exception('更新数据不能为空');
        }

        if (!$safe->validate($data)) {
            if ($safe->illegalFields) {
                throw new Exception('非法字段数据:' . join(',', $safe->illegalFields));
            }
            throw new Exception('没有提交数据');
        }
        $data = $safe->data;
        $_wheres = $this->where($wheres);

        return DB::instance($this->WDB)->update($this->table, $data, $_wheres);
    }

    public function insert(array $data = []): bool
    {

        $_data = [];
        if (empty($data[0])) {
            $_data[] = $data;
        } else {
            $_data = $data;
        }

        $safe = new Safe($this->schema);
        $data = [];
        foreach ($_data as $rs) {

            $rs = $safe->clear($rs);
            if (empty($rs)) {
                throw new Exception('新增数据不能为空');
            }

            if (!$safe->complete($rs)) {
                throw new Exception('缺少必要字段:' . join(',', $safe->incompleteFields));
            }

            if (!$safe->validate($rs)) {
                if ($safe->illegalFields) {
                    throw new Exception('非法字段数据:' . join(',', $safe->illegalFields));
                }
                throw new Exception('没有提交数据');
            }

            $rs = $safe->data;
            $data[] = $safe->merge($rs);
        }

        return DB::instance($this->WDB)->insert($this->table, $data);
    }

    /**
     * 获取最后插入的主键id
     * @return int
     */
    public function lastid()
    {
        return DB::instance($this->WDB)->insert_id;
    }


    protected function where(array $wheres): array
    {
        //var_dump($wheres);
        $_wheres = [];
        if (is_numeric($wheres[0])) {
            $_wheres[] = [$this->primary, '=', $wheres[0]];
        } else if (is_string($wheres[0])) {

            if (stripos($wheres[0], ',') === false) {
                $_wheres[] = [$this->primary, '=', $wheres[0]];
            } else {
                $_wheres[] = [$this->primary, 'in', $wheres[0]];
            }

        } else if($wheres[0]){
            if (is_string($wheres[0][array_key_first($wheres[0])])) {
                $_wheres= $wheres;
            } else {
                $_wheres = $wheres[0];
            }

        }


        $safe = new Safe($this->schema);
        $safe->validateWhere($_wheres);

        return $_wheres;
    }


}