<?php

declare(strict_types=1);

namespace system\model;

use system\database\DatabaseAbstract;
use system\model\Safe;

class Select
{

    public DatabaseAbstract $db;

    public string $table;
    public string $primary;
    public array $schema;
    public array $fields = [];
    public array $wheres = [];
    public array $groups = [];
    public array $orders = [];


    /**
     * 查询方法
     * @param float|int|string|array ...$wheres 查询条件
     * @return object 查询对象
     * @example
     * 示例：
     * where(1); // [['primary','=',1]]  默认字段名为$this->primary
     * where('1');// [['primary','=','1']] 默认字段名为$this->primary   
     * where('id',1);// [['id','=',1]]
     * where('id','1');// [['id','=','1']]
     * where('id',[1,2,3]);// [['id','in',[1,2,3]]] 
     * where('id','between',[1,10]);// [['id','between',[1,10]]] 
     * where('id','not in',[1,2,3],'and');// [['id','not','in',[1,2,3]]]
     * where('id','not between',[1,10],'or');// [['id','not','between',[1,10]]]
     * where(['id','=',1],['name','=','张三']);// [['id','=',1],['name','=','张三']]
     * where(['id','in',[1,2,3],'or'], ['name','=','李四']);// [['id','in',[1,2,3],'or'], ['name','=','李四']]
     */
    public function where(float|int|string ...$wheres): object
    {
        if (is_numeric($wheres[0]) || is_string($wheres[0])) {
            if (count($wheres) == 1) {
                $wheres = [$this->primary, '=', $wheres[0]];
            }
        }

        $this->wheres = array_merge($this->wheres, Safe::where($wheres, $this->schema)); //验证where条件是否合法并合并

        return $this;
    }


    public function groupBy(array $groups): object
    {
        $this->groups = array_merge($this->groups, $groups);
        return $this;
    }


    public function having() {}


    //$orders=array|'string'
    //orderBy('id')|orderBy('id','desc')|orderBy(['id'=>'desc','name'=>'asc'])
    //select()->orderBy('id')->orderBy('name','asc')->orderBy(['user_id'=>'desc'])
    public function orderBy(array|string $orders, string $sort = 'desc'): object
    {
        if (is_string($orders)) {
            $orders = [$orders => $sort];
        }

        $this->orders = array_merge($this->orders, $orders);

        return $this;
    }


    public function one(): object|null
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'groupby' => $this->groups,
            'limit' => 1
        ];

        return $this->db->select($this->table, $condition)->row();
    }

    public function last(): object|null
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => [$this->primary => 'DESC'],
            'groupby' => $this->groups,
            'limit' => 1
        ];

        return $this->db->select($this->table, $condition)->row();
    }


    public function first(): object|null
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => [$this->primary => 'ASC'],
            'groupby' => $this->groups,
            'limit' => 1
        ];

        return $this->db->select($this->table, $condition)->row();
    }


    public function rows(int ...$offset): array
    {
        if (empty($offset)) {
            $limit = [];
        } else {
            $limit = count($offset) == 1 ? $offset[0] : $offset;
        }

        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => $this->orders,
            'groupby' => $this->groups,
            'limit' => $limit
        ];

        return $this->db->select($this->table, $condition)->result();
    }

    public function all(int $limit = 1000): array
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => $this->orders,
            'groupby' => $this->groups,
            'limit' => $limit
        ];
        return $this->db->select($this->table, $condition)->result();
    }
}
