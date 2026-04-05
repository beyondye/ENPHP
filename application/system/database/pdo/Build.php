<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseException;

class Build
{
    public static function wherePlaceholder(array $wheres, string $prefix = 'where'): string
    {
        if (empty($wheres)) {
            return '';
        }

        $conditions = [];
        foreach ($wheres as $key => $value) {

            if (count($value) < 3 || count($value) > 4) {
                throw new DatabaseException('buildWhere Condition Format Is Wrong.');
            }

            if (!is_string($value[0]) || empty($value[0]) || is_numeric($value[0])) {
                throw new DatabaseException('buildWhere Key Must Be String. ' . $key);
            }

            $field = $value[0];

            if (!is_string($value[1]) || empty($value[1]) || is_numeric($value[1])) {
                throw new DatabaseException('buildWhere Operator Must Be String. ' . $field);
            }

            if (!is_scalar($value[2]) && !is_array($value[2])) {
                throw new DatabaseException('buildWhere Value Condition Format Is Wrong. ' . $field);
            }

            if (count($value) == 4 && !in_array(strtolower($value[3]), ['or', 'and', 'not'])) {
                throw new DatabaseException('buildWhere Logic Condition Format Is Wrong. ' . $field);
            }

            $operator = strtoupper($value[1]);
            $allowedOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'IN', 'BETWEEN', 'ILIKE'];
            if (!in_array($operator, $allowedOperators)) {
                throw new DatabaseException('buildWhere Operator Is Wrong. ' . $field);
            }

            $logic = $value[3] ?? '';
            $logic = $logic ? ' ' . strtoupper($logic) : '';

            if ($operator == 'IN') {
                if (!is_array($value[2]) || count($value[2]) == 0) {
                    throw new DatabaseException('buildWhere In Operator Value Must Be Array With At Least One Element. ' . $field);
                }
                $placeholders = [];
                foreach ($value[2] as $subkey => $subvalue) {
                    if (is_string($subvalue) || is_numeric($subvalue)) {
                        $placeholders[] = ":{$prefix}_{$key}_{$subkey}";
                    } else {
                        throw new DatabaseException('buildWhere In Operator Value Must Be String Or Numeric. ' . $field);
                    }
                }

                $conditions[] = "{$field} {$operator} (" . implode(',', $placeholders) . "){$logic}";
                continue;
            }

            if ($operator == 'BETWEEN') {
                if (!is_array($value[2]) || count($value[2]) != 2) {
                    throw new DatabaseException('buildWhere Between Operator Value Must Be Array With Two Elements. ' . $field);
                }

                if (is_numeric($value[2][0]) && is_numeric($value[2][1])) {
                    $conditions[] = "{$field} {$operator} :{$prefix}_{$key}_0 AND :{$prefix}_{$key}_1{$logic}";
                    continue;
                }

                throw new DatabaseException('buildWhere Between Operator Value Must Be Numeric. ' . $field);
            }

            $conditions[] = "{$field} {$operator} :{$prefix}_{$key}{$logic}";
        }

        return implode(' ', $conditions);
    }

    public static function wherePlaceholderValues(\PDOStatement $stmt, array $wheres, string $prefix = 'where'): array
    {
        if (empty($wheres)) {
            return [];
        }

        $values = [];
        foreach ($wheres as $key => $value) {

            if (in_array(strtolower($value[1]), ['in', 'between']) && is_array($value[2])) {
                foreach ($value[2] as $subkey => $subvalue) {
                    $stmt->bindValue(":{$prefix}_{$key}_{$subkey}", $subvalue, is_int($subvalue) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
                    $values["{$prefix}_{$key}_{$subkey}"] = $subvalue;
                }
                continue;
            }

            $stmt->bindValue(":{$prefix}_{$key}", $value[2], is_int($value[2]) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
            $values["{$prefix}_{$key}"] = $value[2];
        }

        return $values;
    }

    public static function where(array $wheres = []): string
    {
        if (empty($wheres)) {
            return '';
        }

        $whereClause = self::wherePlaceholder($wheres, 'where');
        return " WHERE {$whereClause}";
    }

    public static function fields(string ...$fields): string
    {
        if (empty($fields)) {
            return '*';
        }
        foreach ($fields as $key => $value) {
            $fields[$key] = $value;
        }
        return implode(',', $fields);
    }

    public static function having(array $having = []): string
    {
        if (empty($having)) {
            return '';
        }

        $havingClause = self::wherePlaceholder($having, 'having');
        return " HAVING {$havingClause}";
    }

    public static function groupBy(array $groupby, array $having): string
    {
        if (empty($groupby)) {
            return '';
        }

        if (is_array($groupby)) {
            $groupby = implode(',', $groupby);
        }

        if (!empty($having)) {
            $havingClause = self::having($having);
        } else {
            $havingClause = '';
        }

        return " GROUP BY {$groupby}{$havingClause}";
    }

    public static function orderBy(array $orderby): string
    {
        if (empty($orderby)) {
            return '';
        }

        $orders = [];
        foreach ($orderby as $key => $value) {
            if (is_string($key) && trim($key) != '' && $value != '' && is_string($value) && in_array(strtolower($value), ['asc', 'desc'])) {
                $orders[$key] = $key . ' ' . $value;
                continue;
            }
            throw new DatabaseException('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. ' . $key);
        }

        return ' ORDER BY ' . implode(',', $orders);
    }


    public static function limit(array|int $limit): string
    {
        if (empty($limit)) {
            return '';
        }

        if (is_int($limit)) {
            return " LIMIT {$limit}";
        }

        if (count($limit) == 1 && is_int($limit[0])) {
            return " LIMIT {$limit[0]}";
        } elseif (count($limit) == 2 && is_int($limit[0]) && is_int($limit[1])) {
            return " LIMIT {$limit[0]} OFFSET {$limit[1]}";
        }

        throw new DatabaseException('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');
    }
}
