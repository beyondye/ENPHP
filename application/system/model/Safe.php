<?php

declare(strict_types=1);

namespace system\model;

use system\model\ModelException;
use system\database\Util;

class Safe
{

    public static function where(array $wheres, array $fields): array
    {
        $wheres = Util::where($wheres);
        foreach ($wheres as $where) {

            if (in_array(mb_strtolower($where[1]), ['in', 'between'])) {
                if (!is_array($where[2])) {
                    throw new ModelException('Value Must Be Array:' . $where[2]);
                }
                foreach ($where[2] as $value) {
                    if (!self::validate($where[0], $value, $fields)) {
                        throw new ModelException('Field '.$where[0].' Value Not Matched:' . $value);
                    }
                }
            }
            elseif (!self::validate($where[0], $where[2], $fields)) {
                throw new ModelException('Field '.$where[0].' Value Not Matched:' . $where[2]);
            }   
        }

        return $wheres;
    }


    public static function fillable(array $data, array $fillable): void
    {
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $fillable)) {
                throw new ModelException('Invalid Field ' . $key . ',Only Allowed Fields:' . join(',', array_keys($fillable)));
            }
        }
    }


    public static function data(array $data, array $fields): void
    {
        foreach ($data as $key => $value) {
            if (!self::validate($key, $value, $fields)) {
                throw new ModelException('Field '.$key.' Value Not Matched:' . $value);
            }
        }
    }


    public static function integer(int $value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX, bool $unsigned = false): bool
    {
        if ($unsigned && $value < 0) {
            return false;
        }

        if ($min !== null && $value < $min) {
            return false;
        }
        if ($max !== null && $value > $max) {
            return false;
        }

        return true;
    }

    public static function varchar(string $value, int $length = 255): bool
    {
        $len = mb_strlen($value);
        if ($len > $length) {
            return false;
        }
        return true;
    }

    public static function datetime(string $value): bool
    {
        $timestamp = strtotime($value);//时间超出范围，只要格式正确即可通过
        if ($timestamp === false) {
            return false;
        }
        return true;
    }

    public static function enum(mixed $value, array $options): bool
    {
        return in_array($value, $options, true);
    }


    public static function decimal(string|float|int $value, int $precision = 10, int $scale = 0): bool
    {
        // 验证参数有效性
        if ($precision <= 0 || $scale < 0 || $scale > $precision) {
            return false;
        }
        
        // 处理不同类型的输入
        if (is_int($value)) {
            // 整数转换为字符串
            $value = (string)$value;
        } elseif (is_float($value)) {
            // 浮点数转换为字符串，确保小数位数正确
            $value = number_format($value, $scale, '.', '');
        }
        
        // 匹配小数格式：可选负号，整数部分，可选小数部分（最多 $scale 位）
        $pattern = '/^-?\d+(' . ($scale > 0 ? '\.\d{1,' . $scale . '}' : '') . ')?$/';
        if (!preg_match($pattern, $value)) {
            return false;
        }
        
        // 分离整数部分和小数部分
        $parts = explode('.', $value);
        $integerPart = ltrim($parts[0], '-'); // 移除负号以计算长度
        
        // 验证整数部分长度不超过最大允许长度
        $maxIntegerLength = $precision - $scale;
        if (strlen($integerPart) > $maxIntegerLength) {
            return false;
        }
        
        return true;
    }

    public static function validate(string $key, mixed $value, array $fields): bool
    {
        // 检查字段名是否在允许的字段列表中
        if (!array_key_exists($key, $fields)) {
            throw new ModelException('Invalid Field Name:' . $key);
        }

        // 获取字段类型和验证规则
        $fieldConfig = $fields[$key];

        // 处理不同类型的验证
        if (is_string($fieldConfig)) {
            // 简单类型，如 'integer', 'varchar', 'datetime', 'enum', 'decimal'
            $type = $fieldConfig;
            $rules = [];
        } else if (is_array($fieldConfig)) {
            // 复杂类型，包含类型和规则，如 ['varchar', 'length' => 100]
            $type = $fieldConfig[0] ?? 'varchar';
            $rules = array_slice($fieldConfig, 1);
        } else {
            throw new ModelException('Invalid Validation Value for Field ' . $key . ', Must Be String or Array Containing Type and Rules.');
        }

        if (!in_array(strtolower($type), ['integer', 'varchar', 'datetime', 'enum', 'decimal', 'text'])) {
            throw new ModelException('Invalid Validation Type for Field ' . $key . ', Must Be One of [integer,varchar,datetime,enum,decimal,text].');
        }

        // 根据类型进行验证
        switch (strtolower($type)) {
            case 'integer':
                $min = $rules['min'] ?? PHP_INT_MIN;
                $max = $rules['max'] ?? PHP_INT_MAX;
                $unsigned = $rules['unsigned'] ?? false;
                
                // 对于整数类型，允许字符串或浮点数输入，只要它们可以被转换为有效的整数
                if (is_string($value) && ctype_digit($value)) {
                    $value = (int)$value;
                } 
                
                return is_int($value) && self::integer($value, $min, $max, $unsigned);

            case 'varchar':
            case 'text':
                $length = $rules['length'] ?? 255;
                return is_string($value) && self::varchar($value, $length);

            case 'datetime':
                return is_string($value) && self::datetime($value);

            case 'enum':
                $options = $rules['options'] ?? [];
                return self::enum($value, $options);

            case 'decimal':
                $precision = $rules['precision'] ?? 10;
                $scale = $rules['scale'] ?? 0;
                return is_numeric($value) && self::decimal($value, $precision, $scale);
        }

        return false;
    }
}