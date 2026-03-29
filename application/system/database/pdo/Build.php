<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseException;
use system\database\Util;

class Build
{
    public static function whereParams(array ...$wheres): string
    {
        $conditions = [];

        foreach ($wheres as $key => $value) {

            if (!is_array($value)) {
                throw new DatabaseException('buildWhere Condition Must Be Array. ' . $key);
            }

            if (count($value) < 3 || count($value) > 4) {
                throw new DatabaseException('buildWhere Condition Format Is Wrong.');
            }

            if (!is_string($value[0])) {
                throw new DatabaseException('buildWhere Key Must Be String. ' . $key);
            }

            $field = $value[0];

            if (!is_string($value[1])) {
                throw new DatabaseException('buildWhere Operator Must Be String. ' . $field);
            }

            if (!is_scalar($value[2]) && !is_array($value[2])) {
                throw new DatabaseException('buildWhere Value Condition Format Is Wrong. ' . $field);
            }

            if (count($value) == 4 && !in_array(strtolower($value[3]), ['or', 'and', 'not'])) {
                throw new DatabaseException('buildWhere Logic Condition Format Is Wrong. ' . $field);
            }

            $operator = $value[1];
            $allowedOperators = ['=', '!=', '<>', '<', '>', '<=', '>=', 'like', 'in', 'between', 'ilike'];
            if (!in_array(strtolower($operator), $allowedOperators)) {
                throw new DatabaseException('buildWhere Operator Is Wrong. ' . $field);
            }

            $logic = $value[3] ?? '';


            if (in_array($operator, ['in', 'between'])) {
                if (!is_array($value[2])) {
                    throw new DatabaseException('buildWhere In Or Between Value Must Be Array. ' . $field);
                }
                $placeholders = [];
                foreach ($value[2] as $subkey => $subvalue) {
                    if (is_scalar($subvalue)) {
                        $placeholders[] = ":{$field}_{$subkey}";
                    }
                }
                if (empty($placeholders)) {
                    continue;
                }
                $conditions[] = "{$field} {$operator} ({ implode(',', $placeholders) }) {$logic}";
            } else {
                $conditions[] = "{$field} {$operator} :where_{$field} {$logic}";
            }
        }

        return implode(' ', $conditions);
    }

    public static function whereValues(\PDOStatement $stmt, array ...$wheres): void
    {
        if (empty($wheres)) {
            return;
        }   

        foreach ($wheres as $key => $value) {
            if (in_array($value[1], ['in', 'between']) && is_array($value[2])) {
                foreach ($value[2] as $subkey => $subvalue) {
                    if (is_scalar($subvalue)) {
                        $stmt->bindValue(":{$value[0]}_{$subkey}", $subvalue);
                    }
                }
            } else {
                $stmt->bindValue(":where_{$value[0]}", $value[2]);
            }
        }
    }

    public static function where(array ...$wheres): string
    {
        $wheres = Util::where($wheres);
        if (empty($wheres)) {
            return '';
        }

        $whereClause = self::whereParams($wheres);
        return " WHERE {$whereClause}";
    }

    public static function fields(\PDO $db, string ...$fields): string
    {
        if (empty($fields)) {
            return '*';
        }
        foreach ($fields as $key => $value) {
            $fields[$key] = $db->quote($value);
        }
        return implode(',', $fields);
    }

    public static function having(array ...$having): string
    {
        $wheres = Util::where($having);
        if (empty($wheres)) {
            return '';
        }

        $whereClause = self::whereParams($wheres);
        return " HAVING {$whereClause}";
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
            } else {
                throw new DatabaseException('buildOrderBy Order By Key Must Be String,Value Must Be Asc Or Desc. ' . $key);
            }
        }

        if (empty($orders)) {
            return '';
        }

        return ' ORDER BY ' . implode(',', $orders);    
    }


    public static function limit(array|int $limit): string
    {
        if (empty($limit)) {
            return '';
        }

        if(is_int($limit)) {
            return " LIMIT {$limit}";
        }

        if(count($limit) == 1 && is_int($limit[0])) {
            return " LIMIT {$limit[0]}";
        }    
        elseif(count($limit) == 2 && is_int($limit[0]) && is_int($limit[1])) {
            return " LIMIT {$limit[0]} OFFSET {$limit[1]}";
        } 

        throw new DatabaseException('buildLimit Limit Must Be Integer Or Array Be Integer,Not More Than 2');

    }

}
