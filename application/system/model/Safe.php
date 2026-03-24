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

            if (in_array(mb_strtolower($where[1]), ['in', 'not in', 'between', 'not between'])) {
                if (!is_array($where[2])) {
                    throw new ModelException('值必须是数组:' . $where[2]);
                }
                foreach ($where[2] as $value) {
                    if (!self::validate($where[0], $value, $fields)) {
                        throw new ModelException('字段'.$where[0].'值不符合要求:' . $value);
                    }
                }
            }
            elseif (!self::validate($where[0], $where[2], $fields)) {
                throw new ModelException('字段'.$where[0].'值不符合要求:' . $where[2]);
            }   

        }

        return $wheres;
    }


    public static function fillable(array $data, array $fillable): void
    {
        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $fillable)) {
                throw new ModelException('非法字段' . $key . '  ，仅允许的字段:' . join(',', array_keys($fillable)));
            }
        }
    }


    public static function data(array $data, array $fields): void
    {
        foreach ($data as $key => $value) {
            if (!self::validate($key, $value, $fields)) {
                throw new ModelException('字段'.$key.'值不符合要求:' . $value);
            }
        }
    }


    public static function integer($value, int $min = PHP_INT_MIN, int $max = PHP_INT_MAX, bool $unsigned = false): bool
    {
        if (!is_int($value)) {
            return false;
        }

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



    public static function varchar($value, int $length = 255): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $len = mb_strlen($value);
        if ($len > $length) {
            return false;
        }
        return true;
    }


    public static function datetime($value): bool
    {
        if (!is_string($value)) {
            return false;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return false;
        }
        return true;
    }

    public static function enum($value, array $options): bool
    {
        return in_array($value, $options);
    }


    public static function decimal($value, int $precision = 10, int $scale = 0): bool
    {
        if (!is_string($value)) {
            return false;
        }
        if (!preg_match('/^-?\d+(\.\d{1,' . $scale . '})?$/', $value)) {
            return false;
        }
        $parts = explode('.', $value);
        if (strlen($parts[0]) > ($precision - $scale)) {
            return false;
        }
        return true;
    }



    public static function validate(string $key, mixed $value, array $fields): bool
    {
        // 检查字段名是否在允许的字段列表中
        if (!array_key_exists($key, $fields)) {
            throw new ModelException('非法字段名:' . $key);
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
            throw new ModelException($key.'无效验证值,必须是字符串或数组，包含类型和规则.');
        }

        if (!in_array(strtolower($type), ['integer', 'varchar', 'datetime', 'enum', 'decimal', 'text'])) {
            throw new ModelException($key.'无效的验证类型,必须是[integer,varchar,datetime,enum,decimal,text]之一.');
        }

        // 根据类型进行验证
        switch (strtolower($type)) {
            case 'integer':
                $min = $rules['min'] ?? PHP_INT_MIN;
                $max = $rules['max'] ?? PHP_INT_MAX;
                $unsigned = $rules['unsigned'] ?? false;
                return self::integer($value, $min, $max, $unsigned);

            case 'varchar':
            case 'text':
                $length = $rules['length'] ?? 255;
                return self::varchar($value, $length);

            case 'datetime':
                return self::datetime($value);

            case 'enum':
                $options = $rules['options'] ?? [];
                return self::enum($value, $options);

            case 'decimal':
                $precision = $rules['precision'] ?? 10;
                $scale = $rules['scale'] ?? 0;
                return self::decimal($value, $precision, $scale);
        }

        return false;
    }
}
