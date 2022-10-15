<?php

namespace system\model;

use system\Database as DB;

class Select
{

    public string $db;

    public string $table;
    public string $primary;
    public array $schema;

    public array $fields = [];
    public array $wheres = [];
    private array $groups = [];
    private array $orders = [];


    //['id','=','1']
    //id,'>','2'
    //id,'2'
    //[[['id','=','1'],['name','!=','bob']]]
    public function where(array|string ...$wheres): object
    {
        if (is_string($wheres[0])) {
            $count = count($wheres);
            if ($count == 2) {
                $this->wheres[] = [$wheres[0], '=', $wheres[1]];
            } elseif ($count == 3) {
                $this->wheres[] = [$wheres[0], $wheres[1], $wheres[2]];
            }
        } else if($wheres[0]) {

            $key = array_key_first($wheres[0]);
            if (is_string($wheres[0][$key])) {
                foreach ($wheres as $rs) {
                    $this->wheres[] = $rs;
                }
            } else {
                foreach ($wheres[0] as $rs) {
                    //var_dump($rs);
                    if ($rs) {
                        $this->wheres[] = $rs;
                    }
                }
            }
        }

        $safe = new Safe($this->schema);

        //var_dump($this->wheres);
        $safe->validateWhere($this->wheres);

        return $this;
    }


    public function group(array $groups)
    {
        $this->groups = array_merge($this->groups, $groups);
        return $this;
    }


    public function having()
    {


    }


    //$orders=array|'string'
    //order('id')|order('id','desc')|order(['id'=>'desc','name'=>'asc'])
    //select()->order('id')->order('name','asc')->order(['user_id'=>'desc'])
    public function order(array|string $orders, string $sort = 'desc'): object
    {
        if (is_string($orders)) {
            $orders = [$orders => $sort];
        }

        $this->orders = array_merge($this->orders, $orders);

        return $this;
    }

    public function count(): int
    {
        $params = ['where' => $this->wheres, 'fields' => " COUNT({$this->primary}) AS ct "];
        return DB::instance($this->db)->select($this->table, $params)->row()->ct;
    }


    public function last()
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => [$this->primary => 'desc'],
            'limit' => 1
        ];

        return DB::instance($this->db)->select($this->table, $condition)->row();
    }

    public function first(): object|null
    {
        $condition = [
            'where' => $this->wheres,
            'fields' => $this->fields,
            'orderby' => [$this->primary => 'asc'],
            'limit' => 1
        ];

        return DB::instance($this->db)->select($this->table, $condition)->row();
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
            'limit' => $limit
        ];

        return DB::instance($this->db)->select($this->table, $condition)->result();
    }

    public function all(int $limit = 1000): array
    {
        $condition = [
            'fields' => $this->fields,
            'limit' => $limit
        ];
        return DB::instance($this->db)->select($this->table, $condition)->result();
    }


}