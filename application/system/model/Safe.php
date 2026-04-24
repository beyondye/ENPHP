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
                        throw new ModelException('Field ' . $where[0] . ' Value Not Matched:' . $value);
                    }
                }
            } elseif (!self::validate($where[0], $where[2], $fields)) {
                throw new ModelException('Field ' . $where[0] . ' Value Not Matched:' . $where[2]);
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
                throw new ModelException('Field ' . $key . ' Value Not Matched:' . $value);
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
        $timestamp = strtotime($value); //时间超出范围，只要格式正确即可通过
        if ($timestamp === false) {
            return false;
        }
        return true;
    }

    public static function enum(mixed $value, array $options): bool
    {
        return in_array($value, $options, true);
    }


    public static function decimal(string|float $value, int $precision = 10, int $scale = 2): bool
    {
        // 验证参数有效性
        if ($precision <= 0 || $scale < 0 || $scale > $precision) {
            return false;
        }

        if (!is_numeric($value)) {
            return false;
        }

        // 转换为字符串
        if (is_float($value)) {
            $value = (string)$value;
        }

        $value = ltrim($value, '-'); // 移除负号以计算长度

        // 分离整数部分和小数部分
        $parts = explode('.', $value);

        if (count($parts) !== 2) {
            return false;
        }

        $integer = strlen($parts[0]);
        $decimal = strlen($parts[1]);

        if ($decimal === 0) {
            return false;
        }

        if ($integer > $precision) {
            return false;
        }

        if ($decimal > $scale) {
            return false;
        }

        $total = $integer + $decimal;
        if ($total > $precision) {
            return false;
        }

        return true;
    }

    public static function boolean(bool $value): bool
    {
        return $value === true || $value === false;
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

        if (!in_array(strtolower($type), ['boolean', 'integer', 'varchar', 'datetime', 'enum', 'decimal', 'text'])) {
            throw new ModelException('Invalid Validation Type for Field ' . $key . ', Must Be One of [boolean,integer,varchar,datetime,enum,decimal,text].');
        }

        // 根据类型进行验证
        switch (strtolower($type)) {
            case 'boolean':
                return self::boolean($value);
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
                $scale = $rules['scale'] ?? 2;
                return is_numeric($value) && self::decimal($value, $precision, $scale);
            default:

                return false;
        }
    }
}
