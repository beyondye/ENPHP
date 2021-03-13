<?php

namespace system\model;


class Mapper
{

    private $model = null;
    public $keys = [];

    public function __construct(object $model)
    {
        $this->model = $model;
    }


    public function hasOne(object $model, string $foreign, string $local = '')
    {

        if ($local == '') {
            $local = $this->model->primary;
        }

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

        $default = ['where' => $where, 'fields' => [], 'orderby' => [], 'limit' => []];

        return $this->model($model)->select(array_merge($default, $condition));

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
    public function manyToMany($model, $relation_model, $relation_foreign_name, $where = [], $condition = [])
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

            $default = array_merge(['where' => [], 'fields' => [], 'orderby' => [], 'limit' => []], $condition);
            $default['where'][$foreign->primary . ' in'] = join(',', $primaries);

            return $foreign->select($default);

        }

        return null;

    }

}