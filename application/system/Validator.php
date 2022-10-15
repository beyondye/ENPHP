<?php

namespace system;

class Validator
{
    /**
     * 验证规则
     * @var array
     */
    public $rules = [];

    /**
     * 处理后的数据
     * @var array
     */
    public $data = [];

    /**
     * 错误规则
     * @var array
     */
    public $error = [];

    /**
     * 错误模板
     * @var array
     */
    private $template = [
        'required' => '<%label%>不能为空',
        'len' => '<%label%>长度必须为<%limit%>个字符',
        'minLen' => '<%label%>最小长度为<%limit%>个字符',
        'maxLen' => '<%label%>最大长度为<%limit%>个字符',
        'gt' => '<%label%>必须大于<%limit%>',
        'lt' => '<%label%>必须小于<%limit%>',
        'gte' => '<%label%>必须大于等于<%limit%>',
        'lte' => '<%label%>必须小于等于<%limit%>',
        'eq' => '<%label%>必须是<%limit%>',
        'neq' => '<%label%>不能是<%limit%>',
        'in' => '<%label%>只能是<%limit%>其中之一',
        'nin' => '<%label%>不能是<%limit%>其中之一',
        'same' => '<%label%>和<%limit%>必须一致',
        'mobile' => '<%label%>格式错误',
        'email' => '<%label%>格式错误',
        'id' => '<%label%>格式错误',
        'ip4' => '<%label%>格式错误',
        'ip6' => '<%label%>格式错误',
        'url' => '<%label%>格式错误',
        'array' => '<%label%>必须是数组',
        'float' => '<%label%>必须是浮点数',
        'num' => '<%label%>必须是数字',
        'string' => '<%label%>必须是字符',
        'chinese' => '<%label%>必须是中文',
        'alpha' => '<%label%>必须是字母',
        'alphaNum' => '<%label%>必须是字母、数字',
        'alphaNumChinese' => '<%label%>必须是字母、数字、汉字',
        'alphaNumDash' => '<%label%>必须是字母、数字、下划线',
    ];

    /**
     * 设置错误信息
     * @param string $key
     * @param string $ruleKey
     */
    private function setError(string $key, string $ruleKey)
    {
        $label = $key;
        if (isset($this->rules[$key]['label'])) {
            $label = is_array($this->rules[$key]['label']) ? $this->rules[$key]['label'][0] : $this->rules[$key]['label'];
        }

        $message = $this->template[$ruleKey] ?? '<%label%><%value%>验证错误';
        if (isset($this->rules[$key]['message'])) {
            $message = is_array($this->rules[$key]['message']) ? $this->rules[$key]['message'][0] : $this->rules[$key]['message'];
        }

        $limit = '';
        if (isset($this->rules[$key][$ruleKey])) {

            if (is_array($this->rules[$key][$ruleKey])) {
                $limit = join(',', $this->rules[$key][$ruleKey]);
            } else {
                $limit = $this->rules[$key][$ruleKey];
            }
        }

        $value = is_string($this->data[$key]) ? $this->data[$key] : '类型';
        $this->error[$key] = str_replace(['<%label%>', '<%value%>', '<%limit%>'], [$label, $value, $limit], $message);
    }

    /**
     * 设置验证规则
     * @param array $rules
     * @return $this
     */
    public function setRules(array $rules = [])
    {
        if (empty($rules) || !is_array($rules)) {
            return $this;
        }

        foreach ($rules as $key => $val) {
            if (is_string($val)) {
                $this->rules[$key] = $this->toArray($val);
                continue;
            }
            $this->rules[$key] = $val;
        }

        return $this;
    }

    /**
     * 一条字符串验证规则转换为数组
     * @param string $string
     * @return array
     */
    private function toArray(string $string)
    {
        $parts = explode('|', $string);

        $funs = [];
        foreach ($parts as $rs) {
            $fun = explode(':', $rs);
            if (isset($fun[1])) {
                $funs[$fun[0]] = explode(',', $fun[1]);
                continue;
            }
            $funs[$fun[0]] = true;
        }

        return $funs;
    }


    /**
     * 验证数据
     * @param array $data
     * @return bool
     */
    public function validate(array $data)
    {
        if (empty($data)) {
            return true;
        }

        if (empty($this->rules)) {
            return true;
        }

        $rules = $this->rules;
        foreach ($data as $key => $val) {
            if (!isset($rules[$key])) {
                continue;
            }

            if (isset($rules[$key]['label'])) {
                unset($rules[$key]['label']);
            }

            if (isset($rules[$key]['massage'])) {
                unset($rules[$key]['massage']);
            }

            //过滤清理验证数据
            if (isset($rules[$key]['filter'])) {
                $data[$key] = self::filter($val, ...$rules[$key]['filter']);
                unset($rules[$key]['filter']);
            }
        }
        $this->data = $data;


        $pass = true;
        foreach ($data as $key => $val) {
            if (!isset($rules[$key])) {
                continue;
            }

            //提前验证不为空
            if (isset($rules[$key]['required'])) {
                if (!self::required($val)) {
                    $pass = false;
                    $this->setError($key, 'required');
                    continue;
                }
                unset($rules[$key]['required']);
            }

            //没有设置required规则且值为空就跳过
            if (empty($val) && !is_numeric($val)) {
                continue;
            }

            //提前regex验证
            if (isset($rules[$key]['regex'])) {

                if (!is_string($val)) {
                    $pass = false;
                    $this->setError($key, 'regex');
                    continue;
                }

                //var_dump($rules[$key]['regex']);
                if (!self::regex($val, $rules[$key]['regex'][0])) {
                    $pass = false;
                    $this->setError($key, 'regex');
                    continue;
                }
                unset($rules[$key]['regex']);
            }

            foreach ($rules[$key] as $subkey => $subval) {

                if (!method_exists($this, $subkey)) {
                    continue;
                }

                if ($subkey == 'same') {
                    $val = $data[$rules[$key]['same']] ?? $rules[$key]['same'][0];
                }

                $param = [];
                if (is_array($subval)) {
                    array_unshift($subval, $val);
                    $param = $subval;
                } else {
                    $param[] = $val;
                }

                if (!call_user_func_array(__NAMESPACE__ . '\Validator::' . $subkey, $param)) {
                    $this->setError($key, $subkey);
                    $pass = false;
                }
            }

        }

        return $pass;
    }


    /**
     * 自定义政策表达式
     * @param string $var
     * @param string $pattern
     * @return bool
     */
    public static function regex(string $var, string $pattern)
    {
        return preg_match("{$pattern}", $var) > 0;
    }


    /**
     * 是否数组
     * @param mixed $var
     * @return bool
     */
    public static function array($var)
    {
        return is_array($var);
    }


    /**
     * 是否字符串
     * @param mixed $var
     * @return bool
     */
    public static function string($var)
    {
        return is_string($var);
    }

    /**
     * 是否整型
     * @param mixed $var
     * @return bool
     */
    public static function num($var)
    {
        return preg_match('/^\d+$/', $var) > 0;
    }

    /**
     * 是否浮点数
     * @param mixed $var
     * @return bool
     */
    public static function float($var)
    {
        return is_float($var);
    }


    /**
     * 不为空
     * @param mixed $var
     * @return bool
     */
    public static function required($var)
    {
        if (is_numeric($var)) {
            return true;
        }

        return !empty($var);
    }

    /**
     * 和另外一个字段值相同
     * @param $var
     * @param $compare_var
     * @return bool
     */
    public static function same($var, $compare_var)
    {
        return $var === $compare_var;
    }

    /**
     * 字符长度必须等于
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function len(string $var, int $len)
    {
        return !((mb_strlen($var) != $len));
    }

    /**
     * 字符最小长度 一个中文算1个字符
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function minLen(string $var, int $len)
    {
        return !((mb_strlen($var) < $len));
    }

    /**
     * 字符最大长度 一个中文算1个字符
     * @param string $var
     * @param int $len
     * @return bool
     */
    public static function maxLen(string $var, int $len)
    {
        return !((mb_strlen($var) > $len));
    }


    /**
     * 是否手机号
     * @param string $var
     * @return bool
     */
    public static function mobile(string $var)
    {
        return preg_match("/^1[3-9][0-9]{9}$/", $var) > 0;
    }

    /**
     * 是否邮箱
     * @param string $var
     * @return bool
     */
    public static function email(string $var)
    {
        return (bool)filter_var($var, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 是否IP地址
     * @param string $var
     * @return bool
     */
    public static function ip6(string $var)
    {
        return (bool)filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    }

    /**
     * 是否IP地址
     * @param string $var
     * @return bool
     */
    public static function ip4(string $var)
    {
        return (bool)filter_var($var, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    }

    /**
     * 是否有效的URL地址
     * @param string $var
     * @return bool
     */
    public static function url(string $var)
    {
        return (bool)filter_var($var, FILTER_VALIDATE_URL);
    }

    /**
     * 是否身份证
     * @param string $var
     * @return bool
     */
    public static function id(string $var)
    {
        return preg_match('/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/', $var) > 0;
    }

    /**
     * 是否中文字母数字
     * @param string $var
     * @return bool
     */
    public static function alphaNumChinese(string $var)
    {
        return preg_match('/^[a-z0-9\x{4e00}-\x{9fa5}]+$/u', $var) > 0;
    }

    /**
     * 判断是否中文
     * @param string $var
     * @return bool
     */
    public static function chinese(string $var)
    {
        return preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', $var) > 0;
    }


    /**
     * 字母
     * @param string $var
     * @return bool
     */
    public static function alpha(string $var)
    {
        return preg_match("/^([a-z])+$/i", $var) > 0;
    }

    /**
     * 字母数字
     * @param string $var
     * @return bool
     */
    public static function alphaNum(string $var)
    {
        return preg_match("/^([a-z0-9])+$/i", $var) > 0;
    }

    /**
     * 字母、数字、下划线、破折号
     * @param string $var
     * @return bool
     */
    public static function alphaNumDash(string $var)
    {
        return preg_match("/^([a-z0-9_\-])+$/i", $var) > 0;
    }

    /**
     * 大于
     * @param $var
     * @param $min
     * @return bool
     */
    public static function gt($var, $min)
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
    public static function lt($var, $max)
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
    public static function gte($var, $min)
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
    public static function lte($var, $max)
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
    public static function eq($var, $obj)
    {
        if (is_numeric($var)) {
            return $var == $obj;
        }

        return false;
    }

    /**
     * 不等于
     * @param string|int $var
     * @param string|int $obj
     * @return bool
     */
    public static function neq($var, $obj)
    {
        if (is_numeric($var)) {
            return $var != $obj;
        }

        return false;
    }

    /**
     * 必须在集合中
     * @param $var
     * @param array ...$set
     * @return bool
     */
    public static function in($var, ...$set)
    {
        return in_array($var, $set);
    }

    /**
     * 不在集合中
     * @param $var
     * @param array ...$set
     * @return bool
     */
    public static function nin($var, ...$set)
    {
        return !in_array($var, $set);
    }

    /**
     * 过滤字符串
     * @param string $var
     * @param mixed ...$set ['trim','blank','tag','html']
     * @return string
     */
    public static function filter(string $var, ...$set)
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
            $var = htmlspecialchars($var, ENT_QUOTES | ENT_HTML401, CHARSET);;
        }

        return $var;
    }
}