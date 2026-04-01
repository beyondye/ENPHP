<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseException;
use system\database\Util;
use system\database\pdo\Build;
use system\database\ResultAbstract;

trait Common
{
    protected int $effected = 0;

    public function transaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollBack();
    }

    public function close(): bool
    {
        $this->db = null;
        return true;
    }

    public function insert(string $table, array ...$data): string|int
    {
        if (empty($data) || empty($data[0])) {
            throw new DatabaseException('Insert Data Is Empty.');
        }

        // 获取字段名
        $fields = array_keys($data[0]);
        $fieldList = implode(',', $fields);

        // 构建占位符
        $placeholders = [];
        $params = [];

        foreach ($data as $index => $row) {
            $rowPlaceholders = [];
            foreach ($fields as $field) {
                $paramName = "{$field}_{$index}";
                $rowPlaceholders[] = ":{$paramName}";
                $params[$paramName] = $row[$field] ?? null;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        // 构建 SQL 语句
        $placeholderList = implode(', ', $placeholders);
        $sql = "INSERT INTO {$table} ({$fieldList}) VALUES {$placeholderList}";

        // 执行预处理语句
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Insert Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->lastid();
    }


    public function upsert(string $table, array $data): string|int
    {
        if (empty($data)) {
            throw new DatabaseException('Upsert Data Is Empty.');
        }

        $fields = array_keys($data);
        $placeholders = ':' . implode(', :', $fields);
        $updateFields = [];
        foreach ($fields as $field) {
            $updateFields[] = "{$field} = VALUES({$field})";
        }

        $sql = "INSERT INTO {$table} (" . implode(',', $fields) . ") VALUES ({$placeholders})";
        if (!empty($updateFields)) {
            $sql .= " ON DUPLICATE KEY UPDATE " . implode(', ', $updateFields);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Upsert Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->lastid();
    }

    public function update(string $table, array $data, array ...$wheres): int
    {
        if (empty($data)) {
            throw new DatabaseException('Update Data Is Empty.');
        }

        $wheres = Util::where(...$wheres);
        if (empty($wheres)) {
            throw new DatabaseException('Update Where Condition Is Empty.');
        }

        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);

        // 构建 WHERE 子句
        $whereClause = Build::wherePlaceholder(...$wheres);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        // 绑定数据参数
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        // 绑定 WHERE 条件参数
        Build::wherePlaceholderValues($stmt, ...$wheres);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Update Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->effected;
    }

    public function delete(string $table, array ...$wheres): int
    {
        $wheres = Util::where(...$wheres);
        if (empty($wheres)) {
            throw new DatabaseException('Delete Where Condition Is Empty.');
        }

        // 构建 WHERE 子句
        $whereClause = Build::wherePlaceholder(...$wheres);
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        // 绑定 WHERE 条件参数
        Build::wherePlaceholderValues($stmt, ...$wheres);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Delete Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->effected;
    }

    public function lastid(): string|int
    {
        return $this->db->lastInsertId();
    }

    public function execute(string $sql, array $params = []): int|Result
    {
        $stmt = $this->db->prepare($sql);

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Statement Execute Error :' . $e->getMessage());
        }

        if (stripos(trim($sql), 'SELECT') === 0) {
            return new Result($stmt);
        } else {
            $this->effected = $stmt->rowCount();

            return $this->effected;
        }
    }


    /**
     * 执行 SELECT 查询
     * @param string $table 表名
     * @param array $field 字段列表，默认所有字段
     * @param array $where WHERE 条件，默认空数组
     * @param array $groupby GROUP BY 字段，默认空数组
     * @param array $having HAVING 条件，默认空数组
     * @param array $orderby ORDER BY 字段，默认空数组
     * @param array|int $limit LIMIT 条件，默认空数组或整数 ，数组时为 [offset, limit]
     * @example 
     * select('test_table', ['id', 'name'], ['id', '=', 1], ['status'], ['count(*)',' >', 4], ['name'=>'asc'], [10, 20]);
     * select('test_table', field:['id', 'name'], where:['id', '=', 1], groupby:['status'], having:['count(*)',' >', 4], orderby:['name'=>'asc'], limit:[10, 20]);
     * select('test_table', field:['id', 'name'], where:['id', '=', 1], groupby:['status', 'name'], having:['count(*)',' >', 4], orderby:['name'=>'asc'], limit:10);
     * select('test_table');
     * select('test_table', field:['id', 'name']);
     * select('test_table', where:[['id', '=', 1,'or'], ['name', 'like', '%test%','not'], ['age', '>', 5]]);
     * 
     * @return ResultAbstract 查询结果
     * @throws DatabaseException 如果查询执行失败
     */
    public function select(string $table, array $field = [], array $where = [], array $groupby = [], array $having = [], array $orderby = [], array|int $limit = []): ResultAbstract
    {
        if (trim($table) == '') {
            throw new DatabaseException('Select Table Name Is Empty');
        }

        // 构建 SELECT 子句
        $fields = Build::fields($this->db, ...$field);

        // 构建 FROM 子句
        $sql = "SELECT {$fields} FROM {$table}";

        // 构建 WHERE 子句
        $where = Util::where(...$where);
        $sql .= Build::where($where);

        // 构建 GROUP BY 子句
        $having = Util::where(...$having);
        $sql .= Build::groupBy($groupby, $having);

        // 构建 ORDER BY 子句
        $sql .= Build::orderBy($orderby);

        // 构建 LIMIT 子句
        $sql .= Build::limit($limit);

        // 准备 SQL 语句
        $stmt = $this->db->prepare($sql);

        // 绑定 WHERE 条件参数
        Build::wherePlaceholderValues($stmt, ...$where);

        // 绑定 HAVING 条件参数
        Build::wherePlaceholderValues($stmt, ...$having);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Select Execute Error :' . $e->getMessage());
        }

        return new Result($stmt);
    }

    public function effected(): int
    {
        return $this->effected;
    }
}
