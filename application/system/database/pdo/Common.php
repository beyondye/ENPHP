<?php

declare(strict_types=1);

namespace system\database\pdo;

use system\database\DatabaseException;
use system\database\Util;
use system\database\pdo\Build;
use system\database\pdo\Result;

trait Common
{
    protected function transaction(): bool
    {
        return $this->db->beginTransaction();
    }


    protected function commit(): bool
    {
        return $this->db->commit();
    }

    protected function rollback(): bool
    {
        return $this->db->rollBack();
    }

    protected function close(): bool
    {
        $this->db = null;
        return true;
    }

    public function insert(string $table, array ...$data): int|false
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

        return $this->lastid();
    }


    public function upsert(string $table, array $data): int
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

        return $this->lastid();
    }

    public function update(string $table, array $data, array ...$wheres): int
    {
        if (empty($data)) {
            throw new DatabaseException('Update Data Is Empty.');
        }

        $wheres = Util::where($wheres);
        if (empty($wheres)) {
            throw new DatabaseException('Update Where Condition Is Empty.');
        }

        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "{$key} = :{$key}";
        }
        $setStr = implode(', ', $set);

        // 构建 WHERE 子句
        $whereClause = Build::whereParams(...$wheres);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        // 绑定数据参数
        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        // 绑定 WHERE 条件参数
        Build::whereValues($stmt, ...$wheres);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Update Execute Error :' . $e->getMessage());
        }

        return $stmt->rowCount();
    }

    public function delete(string $table, array ...$wheres): int
    {
        $wheres = Util::where($wheres);
        if (empty($wheres)) {
            throw new DatabaseException('Delete Where Condition Is Empty.');
        }

        // 构建 WHERE 子句
        $whereClause = Build::whereParams(...$wheres);
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";
        $stmt = $this->db->prepare($sql);

        // 绑定 WHERE 条件参数
        Build::whereValues($stmt, ...$wheres);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Delete Execute Error :' . $e->getMessage());
        }
   
        return $stmt->rowCount();
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
            return $stmt->rowCount();
        }
    }


    public function select(string $table, array ...$params): Result
    {
        if (trim($table) == '') {
            throw new DatabaseException('Select Table Name Is Empty');
        }

        $fields = $params['fields'] ?? [];
        $where = $params['where'] ?? [];
        $groupby = $params['groupby'] ?? [];
        $having = $params['having'] ?? [];
        $orderby = $params['orderby'] ?? [];
        $limit = $params['limit'] ?? [];

        // 构建 SELECT 子句
        $fields = Build::fields($this->db, $fields);

        // 构建 FROM 子句
        $sql = "SELECT {$fields} FROM {$table}";

        // 构建 WHERE 子句
        $sql .= Build::where($where);

        // 构建 GROUP BY 子句
        $sql .= Build::groupBy($groupby, $having);

        // 构建 ORDER BY 子句
        $sql .= Build::orderBy($orderby);

        // 构建 LIMIT 子句
        $sql .= Build::limit($limit);

        // 准备 SQL 语句
        $stmt = $this->db->prepare($sql);

        // 绑定 WHERE 条件参数
        Build::whereValues($stmt, $where);

        // 绑定 HAVING 条件参数
        Build::whereValues($stmt, $having);

        try {
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Select Execute Error :' . $e->getMessage());
        }

        return new Result($stmt);
    }

    public function effected(): int
    {
        return $this->db->rowCount();
    }
}
