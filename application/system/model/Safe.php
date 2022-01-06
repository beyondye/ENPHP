<?php

namespace system\model;

use system\Validator;

class Safe
{
    /**
     * 表结构
     *
     * @var array
     */
    public $schema = [];

    /**
     * 验证规则
     * @var array
     */
    public $rules = [];

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
     * 非数据库的字段
     *
     * @var array
     */
    public $notMemberFields = [];


    /**
     * 验证之后的数据
     * @var array
     */
    public $data = [];


    /**
     * 构造函数
     *
     * @param $schema
     */
    public function __construct($schema)
    {
        $this->schema = $schema;
        $this->rules = $this->makeRules($schema);
    }


    /**
     * 生成结构化rules
     *
     * @param array $schema
     * @return array
     */
    private function makeRules(array $schema)
    {
        $rules = [];
        foreach ($schema as $key => $val) {
            if (!is_array($schema[$key]) || !key_exists('rules', $schema[$key])) {
                continue;
            }

            $rules[$key] = $schema[$key]['rules'];
            if (is_string($rules[$key])) {
                $rules[$key] = $rules[$key] . '|label:' . $schema[$key]['label'];
            } else {
                $rules[$key]['label'] = $schema[$key]['label'];
            }
        }

        return $rules;
    }

    /**
     * 验证数据合法性，非法字段保存于$this->illegalFields
     *
     * @param array $data 需要和schema key名一致
     *
     * @return boolean
     */
    public function validate(array $data)
    {

        $vali = new Validator();
        if ($vali->setRules($this->rules)->validate($data)) {
            $this->data = $vali->data;
            return true;
        }

        $this->illegalFields = $vali->error;

        return false;
    }

    /**
     * 验证是否缺少必要字段，缺少的必要字段保存于$this->incompleteFields
     *
     * @param array $data 比较数据
     *
     * @return boolean
     */
    public function complete(array $data)
    {
        if (!is_array($data)) {
            return false;
        }

        $required = [];
        foreach ($this->schema as $key => $value) {
            if (key_exists('required', $value) && $value['required'] == true) {
                $required[] = $key;
            }
        }

        $pass = true;
        foreach ($required as $rs) {
            if (!key_exists($rs, $data)) {
                $this->incompleteFields[$rs] = '缺少字段 [' . $this->schema[$rs]['label'] . ']';
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
     * @return array|boolean 与schema默认合并后的数据
     */
    public function merge(array $data, array $without = [])
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

        $given = $this->clear($data);
        $merge = array_merge($fields, $given);
        foreach ($without as $rs) {
            if (isset($merge[$rs])) {
                unset($merge[$rs]);
            }
        }

        return $merge;
    }

    /**
     * 清理不存在于schema里面的字段，不是成员的字段保存于$this->notMemberFields
     *
     * @param array $data 需要清理的数据
     *
     * @return array|bool 清理后后的数据
     */
    public function clear(array $data)
    {
        if (!is_array($data)) {
            return false;
        }

        $given = [];
        foreach ($data as $key => $value) {
            if (isset($this->schema[$key])) {
                $given[$key] = $value;
            } else {
                $this->notMemberFields[] = $key;
            }
        }

        return $given;
    }
}
