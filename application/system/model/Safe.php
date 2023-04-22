<?php

namespace system\model;

use Exception;
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
    private function makeRules(array $schema): array
    {
        $rules = [];
        foreach ($schema as $key => $val) {
            if (!is_array($val) || !key_exists('rules', $val)) {
                continue;
            }

            $rules[$key] = $val['rules'];
            if (is_string($rules[$key])) {
                $rules[$key] = $rules[$key] . '|label:' . $val['label'];
            } else {
                $rules[$key]['label'] = $val['label'];
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
    public function validate(array $data): bool
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
    public function complete(array $data): bool
    {

        $required = [];
        foreach ($this->schema as $key => $value) {
            if (key_exists('required', $value) && $value['required']) {
                $required[] = $key;
            }
        }

        $pass = true;
        foreach ($required as $rs) {
            if (!key_exists($rs, $data)) {
                $this->incompleteFields[$rs] = $this->schema[$rs]['label'] . '[' . $rs . ']';
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

        $fields = [];
        foreach ($this->schema as $key => $value) {
            if (isset($value['default'])) {
                $fields[$key] = $value['default'];
            }
        }

        $merge = array_merge($fields, $data);
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


    public function validateWhere(array $where): bool
    {
        foreach ($where as $rs) {

            if (!is_array($rs) || count($rs) != 3) {
                throw new Exception('WHERE条件参数不完整');
            }

            if (!in_array($rs[1], ['=', '>', '<', '>=', '<=', '<>', '!=', 'in', 'like', 'between'])) {
                throw new Exception('非法操作符');
            }

            if (!array_key_exists($rs[0], $this->schema)) {
                throw new Exception('包含非法字段:' . $rs[0]);
            }

            if ($rs[1] === 'in' || $rs[1] === 'between') {
                $ins = explode(',', $rs[2]);
                foreach ($ins as $subrs) {
                    $this->validateWhere([[$rs[0], '=', $subrs]]); //递归
                }
                continue;
            }

            if (!$this->validate([$rs[0] => $rs[2]])) {
                if ($this->illegalFields) {
                    throw new Exception('非法字段数据:' . join(',', $this->illegalFields));
                }
                throw new Exception('没有提交Where数据');
            }
        }

        return true;
    }
}
