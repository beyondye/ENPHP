<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;
use system\database\pdo\Result;

class CommonExecuteTest extends TestCase
{
    /**
     * 测试 execute 方法 - SELECT 查询
     */
    public function testExecuteSelectQuery()
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

        // 执行 SELECT 查询
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);

        // 验证返回 Result 对象
        $this->assertInstanceOf(Result::class, $result);

        // 验证查询结果
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 execute 方法 - INSERT 查询
     */
    public function testExecuteInsertQuery()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行 INSERT 查询
        $affected = $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals(100, $rows[0]['value']);
    }

    /**
     * 测试 execute 方法 - UPDATE 查询
     */
    public function testExecuteUpdateQuery()
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

        // 执行 UPDATE 查询
        $affected = $sqlite->execute('UPDATE test_table SET name = :name, value = :value WHERE id = :id', ['name' => 'Updated Test', 'value' => 200, 'id' => 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
    }

    /**
     * 测试 execute 方法 - DELETE 查询
     */
    public function testExecuteDeleteQuery()
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

        // 执行 DELETE 查询
        $affected = $sqlite->execute('DELETE FROM test_table WHERE id = :id', ['id' => 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
    }

    /**
     * 测试 execute 方法 - 无参数查询
     */
    public function testExecuteWithoutParams()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行无参数的 INSERT 查询
        $affected = $sqlite->execute("INSERT INTO test_table (name, value) VALUES ('Test 1', 100)");

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 执行无参数的 SELECT 查询
        $result = $sqlite->execute('SELECT * FROM test_table');

        // 验证返回 Result 对象
        $this->assertInstanceOf(Result::class, $result);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals(100, $rows[0]['value']);
    }

    /**
     * 测试 execute 方法 - 空 SQL 语句（应该抛出异常）
     */
    public function testExecuteEmptySql()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空 SQL 语句，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Execute Sql Is Empty.');
        $sqlite->execute('');
    }

    /**
     * 测试 execute 方法 - 只包含空白字符的 SQL 语句（应该抛出异常）
     */
    public function testExecuteWhitespaceSql()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行只包含空白字符的 SQL 语句，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Execute Sql Is Empty.');
        $sqlite->execute('   ');
    }

    /**
     * 测试 execute 方法 - 执行错误（应该抛出异常）
     */
    public function testExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行错误的 SQL，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Statement Execute Error :/');
        $sqlite->execute('SELECT * FROM non_existent_table');
    }

    /**
     * 测试 execute 方法 - 边界测试（特殊字符）
     */
    public function testExecuteWithSpecialCharacters()
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
        $affected = $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => $specialName, 'value' => 100]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $row = $result->first('array');
        $this->assertEquals($specialName, $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 execute 方法 - 安全测试（SQL注入尝试）
     */
    public function testExecuteSqlInjectionAttempt()
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

        // 尝试SQL注入
        $sqlInjectionAttempt = "Test'; DROP TABLE test_table; --";
        $affected = $sqlite->execute('UPDATE test_table SET name = :name WHERE id = :id', ['name' => $sqlInjectionAttempt, 'id' => 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功，且表未被删除
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals($sqlInjectionAttempt, $row['name']);

        // 再次查询，验证表仍然存在
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
    }

    /**
     * 测试 execute 方法 - 验证 affected rows
     */
    public function testExecuteAffectedRows()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入数据
        $affected = $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $sqlite->effected());

        // 更新数据
        $affected = $sqlite->execute('UPDATE test_table SET value = :value WHERE name = :name', ['value' => 200, 'name' => 'Test 1']);
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $sqlite->effected());

        // 删除数据
        $affected = $sqlite->execute('DELETE FROM test_table WHERE id = :id', ['id' => 1]);
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $sqlite->effected());
    }

    /**
     * 测试 execute 方法 - 大小写不敏感的 SELECT 检查
     */
    public function testExecuteSelectCaseInsensitive()
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

        // 执行大小写混合的 SELECT 查询
        $result = $sqlite->execute('select * FROM test_table');

        // 验证返回 Result 对象
        $this->assertInstanceOf(Result::class, $result);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
    }

    /**
     * 测试 execute 方法 - 空字符串参数
     */
    public function testExecuteWithEmptyStringParams()
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
        $affected = $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => '', 'value' => 100]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE name = :name', ['name' => '']);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);
        $this->assertEquals(100, $row['value']);

        // 更新为空字符串
        $affected = $sqlite->execute('UPDATE test_table SET name = :name WHERE id = :id', ['name' => '', 'id' => 1]);
        $this->assertEquals(1, $affected);

        // 验证更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);
    }
}