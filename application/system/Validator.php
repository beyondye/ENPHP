<?php

declare(strict_types=1);

namespace system;

class Validator
{

    public function __construct(array $rules = [])
    {
        $this->raw = $rules;
        $this->setRules($rules);
    }


    public static function make(array $rules = []): self
    {
        return new self($rules);
    }


    /* 原始规则数据
     * @var array
     */
    private array $raw = [];

    /**
     * 处理后的验证规则
     * @var array
     */
    public private(set) array $rules = [];

    /**
     * 处理后的数据
     * @var array
     */
    public private(set) array $data = [];

    /**
     * 验证提示信息
     * @var array
     */
    public private(set) array $errors = [];

    /**
     * 是否验证通过
     * @var bool
     */
    public private(set) bool $pass = true;


    /**
     * 验证方法列表
     * @var array
     */
    private array $methods = [
        'required',
        'len',
        'minLen',
        'maxLen',
        'gt',
        'lt',
        'gte',
        'lte',
        'eq',
        'neq',
        'in',
        'nin',
        'same',
        'mobile',
        'email',
        'id',
        'ip4',
        'ip6',
        'url',
        'array',
        'float',
        'num',
        'string',
        'chinese',
        'alpha',
        'alphaNum',
        'alphaNumChinese',
        'alphaNumDash',
        'regex',
        'filter'
    ];


    /**
     * 设置验证规则
     * @param array $rules 验证规则数组
     * @return $this
     */
    public function setRules(array $rules = []): self
    {
        if (empty($rules)) {
            return $this;
        }

        foreach ($rules as $key => $val) {
            if (!isset($val['rules'])) {
                continue;
            }

            if (is_array($val['rules'])) {
                // 如果rules是数组且是索引数组就转换成关联数组
                foreach ($val['rules'] as $method => $limit) {
                    if (is_int($method)) {
                        $method = $limit;
                    }
                    $this->rules[$key][$method] = $limit;
                }
                continue;
            }

            if (is_string($val['rules'])) {
                $this->rules[$key]['regex'] = $val['rules'];
                continue;
            }

            $this->rules[$key] = $val['rules'];
        }

        return $this;
    }


    /**
     * 设置验证错误信息
     * @param string $key 验证字段名  
     * @param string $name 验证方法名
     * @param array $replace 替换参数数组
     * @return void
     */
    private function setError(string $key, string $name = 'default', array $replace = []): void
    {
        $replace = array_merge([
            // 字段标签
            'label' => $this->raw[$key]['label'] ?? $key,
            // 验证规则限制值
            'limit' => is_array($this->rules[$key]) ? $this->rules[$key][$name] ?? '' : '',
        ], $replace);


        // 如果设置了错误信息就使用设置的错误信息
        if (isset($this->raw[$key]['errors'])) {

            if (is_string($this->raw[$key]['errors'])) {
                $this->errors[$key] = str_replace(['{label}', '{limit}'], array_values($replace), $this->raw[$key]['errors']);
                return;
            }

            if (is_array($this->raw[$key]['errors'])) {
                $this->errors[$key] = $this->raw[$key]['errors'][$name] ?? ($this->raw[$key]['errors']['default'] ?? lang('system.validator.' . $name, $replace));
                return;
            }
        }

        // 如果没有设置错误信息就使用默认错误信息
        $this->errors[$key] = lang('system.validator.' . $name, $replace);
    }


    /**
     * 执行验证
     * @param array $data 验证数据数组
     * @return bool 是否验证通过
     */
    public function execute(array $data): bool
    {
        //如果没有设置验证规则就直接返回true
        if (empty($this->rules)) {
            return $this->pass;
        }

        //验证数据的键必须在规则中存在，否则返回错误
        $diff = array_diff_key($this->rules, $data);
        if (!empty($diff)) {
            $this->pass = false;
            foreach ($diff as $key => $val) {
                $this->setError($key, 'key_no_exist');
            }
            return $this->pass;
        } 

        //过滤数据
        $this->data = $data;

        //遍历验证数据
        foreach ($data as $key => $val) {

            //没有设置验证规则就跳过
            if (!isset($this->rules[$key])) {
                continue;
            }

            //如果是自定义验证方法就调用
            if (is_callable($this->rules[$key])) {
                if ($this->rules[$key]($val)) {
                    continue;
                }
                $this->pass = false;
                $this->setError($key);
                continue;
            }

            //检查验证方法是否存在，不存在就返回错误
            $diff = array_diff_key($this->rules[$key], array_flip($this->methods));
            if (!empty($diff)) {
                $this->pass = false;
                $this->setError($key, 'method_no_exist', ['limit' => implode(',', array_keys($diff))]);
                continue;
            }

            //获取当前字段设置的验证方法
            $methods = array_intersect($this->methods, array_keys($this->rules[$key]));

            //如果没有设置验证方法就跳过
            if (empty($methods)) {
                continue;
            }

            //如果设置了filter方法就先进行过滤
            if (in_array('filter', $methods)) {
                $val = self::filter($val, $this->rules[$key]['filter']);
                $this->data[$key] = $val;
            }

            //提前验证不能为空
            if (in_array('required', $methods) && !self::required($val)) {
                $this->pass = false;
                $this->setError($key, 'required');
                continue;
            } else {
                //没有设置required规则且值为空就跳过
                if (empty($val) && !is_numeric($val)) {
                    continue;
                }
            }

            //提前验证正则表达式
            if (in_array('regex', $methods) && !self::regex($val, $this->rules[$key]['regex'])) {
                $this->pass = false;
                $this->setError($key);
                continue;
            }

            //遍历验证方法
            foreach ($methods as $method) {

                //跳过required,regex,filter方法
                if (in_array($method, ['required', 'regex', 'filter'])) {
                    continue;
                }

                //调用验证方法
                if (!self::$method($val, $this->rules[$key][$method])) {
                    $this->setError($key, $method);
                    $this->pass = false;
                    break; //跳出当前字段的验证方法循环
                }
            }
        }

        return $this->pass;
    }


    /**
     * 自定义正则表达式验证
     * @param string $var
     * @param string $pattern
     * @return bool
     */
    public static function regex(string $var, string $pattern): bool
    {
        if (empty($pattern)) {
            return false;
        }

        $result = @preg_match("{$pattern}", $var);

        // 检查是否有错误发生
        if ($result === false) {
            return false;
        }

        return $result > 0;
    }


    /**
     * 是否数组
     * @param mixed $var
     * @return bool
     */
    public static function array($var): bool
    {
        return is_array($var);
    }


    /**
     * 是否字符串
     * @param mixed $var
     * @return bool
     */
    public static function string($var): bool
    {
        return is_string($var);
    }

    /**
     * 是否数字
     * @param mixed $var
     * @return bool
     */
    public static function num($var): bool
    {
        return is_numeric($var);
    }

    /**
     * 是否浮点数
     * @param mixed $var
     * @return bool
     */
    public static function float($var): bool
    {
        return filter_var($var, FILTER_VALIDATE_FLOAT) !== false;
    }


    /**
     * 不为空
     * @param mixed $val
     * @return bool
     */
    public static function required($val): bool
    {
        if (is_numeric($val)) {
            return true;
        }
        if (is_bool($val)) {
            return true;
        }

        return !empty($val);
    }

    /**
     * 和另外一个值相同
     * @param $var
     * @param $compare_var
     * @return bool
     */
    public static function same($var, $compare_var): bool
    {
        return $var === $compare_var;
    }

    /**
     * 字符长度必须等于
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function len(string $var, int $len): bool
    {
        return mb_strlen($var) == $len;
    }

    /**
     * 字符最小长度 一个中文算1个字符
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function minLen(string $var, int $len): bool
    {
        return mb_strlen($var) >= $len;
    }

    /**
     * 字符最大长度 一个中文算1个字符
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function maxLen(string $var, int $len): bool
    {
        return mb_strlen($var) <= $len;
    }


    /**
     * 是否手机号
     * @param string $var
     * @return bool
     */
    public static function mobile(string $var): bool
    {
        return preg_match("/^1[3-9][0-9]{9}$/", $var) > 0;
    }

    /**
     * 是否邮箱
     * @param string $var
     * @return bool
     */
    public static function email(string $var): bool
    {
        return (bool)filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 是否IP地址
     * @param string $var
     * @return bool
     */
    public static function ip6(string $var): bool
    {
        return (bool)filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * 是否IP地址
     * @param string $var
     * @return bool
     */
    public static function ip4(string $var): bool
    {
        return (bool)filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * 是否有效的URL地址
     * @param string $var
     * @return bool
     */
    public static function url(string $var): bool
    {
        return (bool)filter_var($var, FILTER_VALIDATE_URL);
    }

    /**
     * 是否中国身份证号
     * @param string $var
     * @return bool
     */
    public static function id(string $var): bool
    {
        return preg_match('/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/', $var) > 0;
    }

    /**
     * 是否中文字母数字
     * @param string $var
     * @return bool
     */
    public static function alphaNumChinese(string $var): bool
    {
        return preg_match('/^[a-z0-9\x{4e00}-\x{9fa5}]+$/u', $var) > 0;
    }

    /**
     * 判断是否中文
     * @param string $var
     * @return bool
     */
    public static function chinese(string $var): bool
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $var) > 0;
    }


    /**
     * 字母
     * @param string $var
     * @return bool
     */
    public static function alpha(string $var): bool
    {
        return preg_match("/^([a-z])+$/i", $var) > 0;
    }

    /**
     * 字母数字
     * @param string $var
     * @return bool
     */
    public static function alphaNum(string $var): bool
    {
        return preg_match("/^([a-z0-9])+$/i", $var) > 0;
    }

    /**
     * 字母、数字、下划线、破折号
     * @param string $var
     * @return bool
     */
    public static function alphaNumDash(string $var): bool
    {
        return preg_match("/^([a-z0-9_\-])+$/i", $var) > 0;
    }

    /**
     * 大于
     * @param $var
     * @param $min
     * @return bool
     */
    public static function gt($var, $min): bool
    {
        if (!is_numeric($var)) {
            return false;
        }
        return $var > $min;
    }

    /**
     * 小于
     * @param $var
     * @param $max
     * @return bool
     */
    public static function lt($var, $max): bool
    {
        if (!is_numeric($var)) {
            return false;
        }
        return $var < $max;
    }

    /**
     * 大于等于
     * @param $var
     * @param $min
     * @return bool
     */
    public static function gte($var, $min): bool
    {
        if (!is_numeric($var)) {
            return false;
        }
        return $var >= $min;
    }

    /**
     * 小于等于
     * @param $var
     * @param $max
     * @return bool
     */
    public static function lte($var, $max): bool
    {
        if (!is_numeric($var)) {
            return false;
        }
        return $var <= $max;
    }


    /**
     * 等于
     * @param string|int $var
     * @param string|int $obj
     * @return bool
     */
    public static function eq($var, $obj): bool
    {
        if (is_numeric($var)) {
            return $var == $obj;
        }

        return $var === $obj;
    }

    /**
     * 不等于
     * @param string|int $var
     * @param string|int $obj
     * @return bool
     */
    public static function neq($var, $obj): bool
    {
        if (is_numeric($var)) {
            return $var != $obj;
        }

        return $var !== $obj;
    }

    /**
     * 必须在集合中
     * @param $var
     * @param array $set
     * @return bool
     */
    public static function in($var, $set): bool
    {
        return in_array($var, $set);
    }

    /**
     * 不在集合中
     * @param $var
     * @param array $set
     * @return bool
     */
    public static function nin($var, $set): bool
    {
        return !in_array($var, $set);
    }

    /**
     * 过滤字符串
     * @param string $var
     * @param mixed $set ['trim','blank','tag','html']
     * @return string
     */
    public static function filter(string $var, $set): string
    {
        if (in_array('trim', $set)) {
            $var = trim($var);
        }

        if (in_array('blank', $set)) {
            $var = preg_replace('/[\s]+/is', ' ', $var);
        }

        if (in_array('tag', $set)) {
            $var = strip_tags($var);
        }

        if (in_array('html', $set)) {
            $var = htmlspecialchars($var, ENT_QUOTES | ENT_HTML401, 'UTF-8');
        }


        return $var;
    }
}
