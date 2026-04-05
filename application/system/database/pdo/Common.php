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
        if ($this->db->inTransaction()) {
            throw new DatabaseException('Transaction Is Already In Progress.');
        }
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        if (!$this->db->inTransaction()) {
            throw new DatabaseException('No active transaction to commit.');
        }
        return $this->db->commit();
    }

    public function rollback(): bool
    {
       if (!$this->db->inTransaction()) {
           throw new DatabaseException('No active transaction to roll back.');
       }
       return $this->db->rollBack();    
     
    }

    public function close(): bool
    {
        $this->db = null;
        return true;
    }

    public function insert(string $table, array ...$data): string|int
    {
        if (trim($table) == '') {
            throw new DatabaseException('Insert Table Name Is Empty.');
        }

        if (empty($data) || empty($data[0]) || !is_array($data[0])) {
            throw new DatabaseException('Insert Data Is Empty.');
        }

        foreach ($data as $index => $row) {
            if (!is_array($row) || empty($row)) {
                throw new DatabaseException("Insert Data Row {$index} Is Invalid.");
            }

            if (count($data[0]) != count($row)) {
                throw new DatabaseException('Insert Data Row ' . $index . ' Field Count Does Not Match.');
            }
        }

        foreach ($data[0] as $key => $value) {
            if (is_numeric($key)) {
                throw new DatabaseException('Insert Data Field Key Must Be String.');
            }
        }

        $fields = array_keys($data[0]);

        // 构建占位符
        $placeholders = [];
        $bindValues = [];
        foreach ($data as $index => $row) {
            $rowPlaceholders = [];
            foreach ($fields as $field) {
                $placeholder = "{$field}_{$index}";
                $rowPlaceholders[] = ":{$placeholder}";
                $bindValues[$placeholder] = $row[$field] ?? null;
            }
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        // 构建 SQL 语句
        $placeholderList = implode(', ', $placeholders);
        $fieldList = implode(',', $fields);
        $sql = "INSERT INTO {$table} ({$fieldList}) VALUES {$placeholderList}";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($bindValues as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Insert Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->lastid();
    }


    /**
     * 更新数据
     * update 方法接受一个表名、一个数据数组和一个或多个 WHERE 条件数组，构建 UPDATE SQL 语句并执行。
     * @param string $table 表名
     * @param array $data 要更新的数据数组，键为字段名，值为新值
     * @param array ...$wheres WHERE 条件数组，支持多个条件，每个条件数组格式为 [字段, 操作符, 值, 逻辑连接符]，其中逻辑连接符可选，默认为 AND
     * @return int 更新的行数
     * @example
     * update('test_table', ['name' => 'test'], 'id', '=', 1); // 更新 id 为 1 的记录 name 字段为 test
     * update('test_table', ['name' => 'test'], ['id', '=', 1]); // 等同于 update('test_table', ['name' => 'test'], 'id', '=', 1);
     * update('test_table', ['name' => 'test'], ['id', '=', 1], ['name', '=', 'test2']); // 更新 id 为 1 或 name 为 test2 的记录 name 字段为 test
     */
    public function update(string $table, array $data, array|int|string|float ...$wheres): int
    {
        if (trim($table) == '') {
            throw new DatabaseException('Update Table Is Empty.');
        }

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
        $whereClause = Build::wherePlaceholder($wheres);
        $sql = "UPDATE {$table} SET {$setStr} WHERE {$whereClause}";

        try {
            $stmt = $this->db->prepare($sql);

            // 绑定数据参数
            foreach ($data as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }

            // 绑定 WHERE 条件参数
            Build::wherePlaceholderValues($stmt, $wheres);

            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Update Execute Error :' . $e->getMessage());
        }

        $this->effected = $stmt->rowCount();

        return $this->effected;
    }

    /**
     * 删除数据
     * delete 方法接受一个表名和一个或多个 WHERE 条件数组，构建 DELETE SQL 语句并执行。
     * @param string $table 表名
     * @param array ...$wheres WHERE 条件数组，支持多个条件，每个条件数组格式为 [字段, 操作符, 值, 逻辑连接符]，其中逻辑连接符可选，默认为 AND
     * @return int 删除的行数
     * examples:
     * delete('test_table' 1); // 等同于 delete('test_table', ['id', '=', 1]);
     * delete('test_table', 'id' , 1); // 等同于 delete('test_table', ['id', '=', 1]);
     * delete('test_table', 'id',[1,2,3]); // 等同于 delete('test_table', ['id', 'in', [1,2,3]]);
     * delete('test_table', 'id', '>', 1); // 等同于 delete('test_table', ['id', '>', 1]); 
     * delete('test_table', 'id', 'between', [1, 10], 'and'); // 等同于 delete('test_table', ['id', 'between', [1, 10]]);
     * delete('test_table', ['id', '=', 1], ['name', '=', 'test']);// 等同于 delete('test_table', ['id', '=', 1, 'and'], ['name', '=', 'test']);
     * delete('test_table', ['id', 'in', [1,2,3], 'or'], ['name', '=', 'test']);// 等同于 delete('test_table', ['id', 'in', [1,2,3], 'or'], ['name', '=', 'test']);
     */
    public function delete(string $table, array|int|string|float ...$wheres): int
    {
        if (trim($table) == '') {
            throw new DatabaseException('Delete Table Is Empty.');
        }

        $wheres = Util::where(...$wheres);
        if (empty($wheres)) {
            throw new DatabaseException('Delete Where Condition Is Empty.');
        }

        $whereClause = Build::wherePlaceholder($wheres);
        $sql = "DELETE FROM {$table} WHERE {$whereClause}";

        try {
            $stmt = $this->db->prepare($sql);
            Build::wherePlaceholderValues($stmt, $wheres);
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

    /**
     * 执行自定义 SQL 语句
     * @param string $sql SQL 语句，必须是完整的 SQL 语句，包含参数占位符（:）
     * @param array $params 参数数组，默认空数组，键为占位符名称（不包含冒号），值为参数值  
     * @return int|Result 执行结果 ，如果是 SELECT 查询则返回 Result 对象，否则返回受影响的行数
     * @throws DatabaseException 如果执行失败
     * @example
     * execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);// 查询 id 为 1 的记录 
     * execute('UPDATE test_table SET name = :name WHERE id = :id', ['name' => 'test', 'id' => 1]);// 更新 id 为 1 的记录 name 字段为 test
     */
    public function execute(string $sql, array $params = []): int|Result
    {
        if (trim($sql) == '') {
            throw new DatabaseException('Execute Sql Is Empty.');
        }

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->execute();
        } catch (\PDOException $e) {
            throw new DatabaseException('Statement Execute Error :' . $e->getMessage());
        }

        if (stripos(trim($sql), 'SELECT') === 0) {
            return new Result($stmt);
        }

        $this->effected = $stmt->rowCount();
        return $this->effected;
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

        $fields = Build::fields(...$field);
        $sql = "SELECT {$fields} FROM {$table}";
        $where = Util::where(...$where);
        $sql .= Build::where($where);
        $having = Util::where(...$having);
        $sql .= Build::groupBy($groupby, $having);
        $sql .= Build::orderBy($orderby);
        $sql .= Build::limit($limit);

        try {
            $stmt = $this->db->prepare($sql);
            Build::wherePlaceholderValues($stmt, $where, 'where');
            Build::wherePlaceholderValues($stmt, $having, 'having');
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
