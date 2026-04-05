<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonDeleteTest extends TestCase
{
    /**
     * 测试 delete 方法 - 基本删除功能
     */
    public function testDeleteBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);

        // 执行删除操作
        $affected = $sqlite->delete('test_table', ['id', '=', 2]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 3', $rows[1]['name']);
    }

    /**
     * 测试 delete 方法 - 空 where 条件（应该抛出异常）
     */
    public function testDeleteEmptyWhere()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 尝试执行空 where 条件的删除操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Where Condition Is Empty.');
        $sqlite->delete('test_table');
    }

    /**
     * 测试 delete 方法 - 空表名（应该抛出异常）
     */
    public function testDeleteEmptyTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空表名的删除操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Table Is Empty.');
        $sqlite->delete('', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 只包含空白字符的表名（应该抛出异常）
     */
    public function testDeleteWhitespaceTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行只包含空白字符的表名的删除操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Table Is Empty.');
        $sqlite->delete('   ', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 执行错误（应该抛出异常）
     */
    public function testDeleteExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试删除不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Delete Execute Error :/');
        $sqlite->delete('non_existent_table', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 边界测试（特殊字符）
     */
    public function testDeleteWithSpecialCharacters()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入包含特殊字符的数据
        $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => $specialName, 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行删除操作（使用包含特殊字符的条件）
        $affected = $sqlite->delete('test_table', ['name', '=', $specialName]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 安全测试（SQL注入尝试）
     */
    public function testDeleteSqlInjectionAttempt()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 尝试SQL注入
        $sqlInjectionAttempt = "1 OR 1=1";
        $affected = $sqlite->delete('test_table', ['id', '=', $sqlInjectionAttempt]);

        // 验证只删除了符合条件的行，而不是所有行
        $this->assertEquals(0, $affected); // 因为没有 id 等于 "1 OR 1=1" 的行

        // 验证表仍然存在且数据完整
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
    }

    /**
     * 测试 delete 方法 - 验证 affected rows
     */
    public function testDeleteAffectedRows()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行删除操作
        $affected = $sqlite->delete('test_table', ['value', '>', 150]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $sqlite->effected());

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 复杂 where 条件
     */
    public function testDeleteWithComplexWhere()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 4', 'value' => 400]);

        // 执行带有复杂 where 条件的删除操作
        $affected = $sqlite->delete('test_table', [['value', '>', 150, 'and'], ['value', '<', 350]]);

        // 验证返回受影响的行数
        $this->assertEquals(2, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);
    }

    /**
     * 测试 delete 方法 - 空字符串参数
     */
    public function testDeleteWithEmptyStringParams()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入包含空字符串的数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => '', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行删除操作（使用空字符串作为条件）
        $affected = $sqlite->delete('test_table', ['name', '=', '']);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 多个 where 条件
     */
    public function testDeleteWithMultipleWhereConditions()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            status INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value, status) VALUES (:name, :value, :status)', ['name' => 'Test 1', 'value' => 100, 'status' => 1]);
        $sqlite->execute('INSERT INTO test_table (name, value, status) VALUES (:name, :value, :status)', ['name' => 'Test 2', 'value' => 200, 'status' => 1]);
        $sqlite->execute('INSERT INTO test_table (name, value, status) VALUES (:name, :value, :status)', ['name' => 'Test 3', 'value' => 300, 'status' => 0]);
        $sqlite->execute('INSERT INTO test_table (name, value, status) VALUES (:name, :value, :status)', ['name' => 'Test 4', 'value' => 400, 'status' => 0]);

        // 执行带有多个 where 条件的删除操作
        $affected = $sqlite->delete('test_table', ['status', '=', 1, 'and'], ['value', '>', 150]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 3', $rows[1]['name']);
        $this->assertEquals('Test 4', $rows[2]['name']);
    }

    /**
     * 测试 delete 方法 - 使用不同操作符
     */
    public function testDeleteWithDifferentOperators()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 4', 'value' => 400]);

        // 测试大于操作符
        $affected = $sqlite->delete('test_table', ['value', '>', 300]);
        $this->assertEquals(1, $affected);

        // 测试小于操作符
        $affected = $sqlite->delete('test_table', ['value', '<', 150]);
        $this->assertEquals(1, $affected);

        // 测试不等于操作符
        $affected = $sqlite->delete('test_table', ['value', '!=', 200]);
        $this->assertEquals(1, $affected);

        // 验证最终结果
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 删除不存在的记录
     */
    public function testDeleteNonExistentRecord()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 尝试删除不存在的记录
        $affected = $sqlite->delete('test_table', ['id', '=', 999]);

        // 验证返回受影响的行数为 0
        $this->assertEquals(0, $affected);

        // 验证数据未被删除
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 删除所有符合条件的记录
     */
    public function testDeleteAllMatchingRecords()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 4', 'value' => 100]);

        // 删除所有 value 为 100 的记录
        $affected = $sqlite->delete('test_table', ['value', '=', 100]);

        // 验证返回受影响的行数
        $this->assertEquals(3, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
    }

    /**
     * 测试 delete 方法 - 不同形式的参数传递
     */
    public function testDeleteWithDifferentParameterForms()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        for ($i = 1; $i <= 5; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 测试形式 1: delete('table', 1) - 默认字段名为 id，操作符为 =
        $affected = $sqlite->delete('test_table', 1);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $this->assertNull($result->first('array'));

        // 测试形式 2: delete('table', 'id', 2) - 字段名为 id，操作符为 =
        $affected = $sqlite->delete('test_table', 'id', 2);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 2]);
        $this->assertNull($result->first('array'));

        // 测试形式 3: delete('table', 'id', [3, 4]) - 字段名为 id，操作符为 in
        $affected = $sqlite->delete('test_table', 'id', [3, 4]);
        $this->assertEquals(2, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id IN (3, 4)');
        $this->assertCount(0, $result->all('array'));

        // 测试形式 4: delete('table', 'id', '=', '5') - 字段名为 id，操作符为 =
        $affected = $sqlite->delete('test_table', 'id', '=', '5');
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 5]);
        $this->assertNull($result->first('array'));

        // 验证所有记录都已删除
        $result = $sqlite->execute('SELECT * FROM test_table');
        $this->assertCount(0, $result->all('array'));
    }

    /**
     * 测试 delete 方法 - 使用 in 操作符
     */
    public function testDeleteWithInOperator()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        for ($i = 1; $i <= 5; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 测试使用 in 操作符
        $affected = $sqlite->delete('test_table', 'id', 'in', [1, 3, 5]);
        $this->assertEquals(3, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);
    }
}