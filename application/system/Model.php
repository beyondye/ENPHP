<?php

declare(strict_types=1);

namespace system;

use system\database\DatabaseAbstract;
use system\model\ModelException;
use system\model\Select;
use system\model\Safe;

class Model
{

    protected string $table;
    protected string $primary;

    protected array $fields = [];
    protected array $fillable = [];
    protected array $timestamps = ['creating' => false, 'updating' => false, 'deleting' => false];

    protected array $objects = [
        'select' => null,
    ];

    protected DatabaseAbstract $db;

    public function __construct(DatabaseAbstract $db)
    {
        $this->db = $db;
    }


    protected function creating(): void
    {
        if ($this->timestamps['creating']) {
            $this->fields[$this->timestamps['creating']] = time();
        }
    }

    protected function updating(): void
    {
        if ($this->timestamps['updating']) {
            $this->fields[$this->timestamps['updating']] = time();
        }
    }

    protected function deleting(): void
    {
        if ($this->timestamps['deleting']) {
            $this->fields[$this->timestamps['deleting']] = time();
        }
    }


    public function select(array $fields = []): object
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
        $select->db = $this->db;
        $select->wheres = [];
        $select->orders = [];
        $select->groups = [];

        return $select;
    }


    public function delete(array|string|int ...$wheres): bool
    {
        $wheres = $this->where($wheres);
        return $this->db->delete($this->table, $wheres);
    }


    public function update(array $data, array|int|string ...$wheres): bool
    {
        $safe = new Safe($this->schema);
        $data = $safe->clear($data);

        if (empty($data)) {
            throw new ModelException('更新数据不能为空');
        }

        if (!$safe->validate($data)) {
            if ($safe->illegalFields) {
                throw new ModelException('非法字段数据:' . join(',', $safe->illegalFields));
            }
            throw new ModelException('没有提交数据');
        }
        $data = $safe->data;
        $_wheres = $this->where($wheres);

        return $this->db->update($this->table, $data, $_wheres);
    }

    public function insert(array $data = []): bool
    {

        $_data = [];
        if (empty($data[0])) {
            $_data[] = $data;
        } else {
            $_data = $data;
        }

        $safe = new Safe($this->fillable);
        $data = [];
        foreach ($_data as $rs) {

            $rs = $safe->clear($rs);
            if (empty($rs)) {
                throw new ModelException('插入数据不能为空');
            }

            if (!$safe->complete($rs)) {
                throw new ModelException('缺少必要字段:' . join(',', $safe->incompleteFields));
            }

            if (!$safe->validate($rs)) {
                if ($safe->illegalFields) {
                    throw new ModelException('非法字段数据:' . join(',', $safe->illegalFields));
                }
                throw new ModelException('没有提交数据');
            }

            $rs = $safe->data;
            $data[] = $safe->merge($rs);
        }

        return $this->db->insert($this->table, $data);
    }

    /**
     * 获取最后插入的主键id
     * @return int
     */
    public function lastid()
    {
        return $this->db->lastid();
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
        } else if ($wheres[0]) {
            if (is_string($wheres[0][array_key_first($wheres[0])])) {
                $_wheres = $wheres;
            } else {
                $_wheres = $wheres[0];
            }
        }


        $safe = new Safe($this->schema);
        $safe->validateWhere($_wheres);

        return $_wheres;
    }

    public function count(array|string|int ...$wheres): int
    {
        $wheres = $this->where($wheres);
        $params = ['where' => $wheres, 'fields' => " COUNT({$this->primary}) AS ct "];
        return $this->db->select($this->table, $params)->row()->ct;
    }
}
