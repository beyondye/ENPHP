<?php

namespace system;

/**
 * model数据验证过滤
 *
 * @author Ding<beyondye@gmail.com>
 */
class Safe
{

    public $schema = [];

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

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
    public function clear($data)
    {

        if (!is_array($data)) {
            return false;
        }

        global $sys;

        $given = [];
        foreach ($data as $key => $value) {
            if (isset($this->schema[$key])) {

                if (isset($this->schema[$key]['filter']) && $this->schema[$key]['filter']) {

                    $funcs = explode('|', $this->schema[$key]['filter']);
                    foreach ($funcs as $fun) {
                        $value = $sys->security->$fun($value);
                    }
                }

                $given[$key] = $value;
            } else {
                $this->notMemberFields[] = $key;
            }
        }

        return $given;
    }

}
