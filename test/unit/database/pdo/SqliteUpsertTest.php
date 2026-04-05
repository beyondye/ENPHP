<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class SqliteUpsertTest extends TestCase
{
    /**
     * 测试 Sqlite upsert 方法 - 空数据（应该抛出异常）
     */
    public function testUpsertEmptyData()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Data Is Empty.');
        $sqlite->upsert('test_table', []);
    }

    /**
     * 测试 Sqlite upsert 方法 - 插入新数据
     */
    public function testUpsertInsert()
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
        $id = $sqlite->upsert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $this->assertIsScalar($id);
        $this->assertEquals('1', $id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 更新现有数据
     */
    public function testUpsertUpdate()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入初始数据
        $id = $sqlite->upsert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 更新数据
        $updatedId = $sqlite->upsert('test_table', ['id' => $id, 'name' => 'Updated Test', 'value' => 200]);
        $this->assertEquals($id, $updatedId);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 执行错误（应该抛出异常）
     */
    public function testUpsertExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试向不存在的表插入数据，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Upsert Execute Error :/');
        $sqlite->upsert('non_existent_table', ['name' => 'Test', 'value' => 100]);
    }

    /**
     * 测试 Sqlite upsert 方法 - 边界测试（特殊字符）
     */
    public function testUpsertWithSpecialCharacters()
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
        $id = $sqlite->upsert('test_table', ['name' => $specialName, 'value' => 100]);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals($specialName, $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpsertSqlInjectionAttempt()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 尝试SQL注入
        $sqlInjectionAttempt = "Test'; DROP TABLE test_table; --";
        $id = $sqlite->upsert('test_table', ['name' => $sqlInjectionAttempt, 'value' => 100]);

        // 验证数据插入成功，且表未被删除
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals($sqlInjectionAttempt, $row['name']);
        $this->assertEquals(100, $row['value']);

        // 再次插入数据，验证表仍然存在
        $id2 = $sqlite->upsert('test_table', ['name' => 'Another test', 'value' => 200]);
        $this->assertNotEquals($id, $id2);
    }

    /**
     * 测试 Sqlite upsert 方法 - 不同类型数据
     */
    public function testUpsertWithDifferentDataTypes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建包含不同类型字段的测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            price REAL,
            active INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入不同类型的数据
        $data = [
            'name' => 'Test Product',
            'value' => 100,
            'price' => 99.99,
            'active' => 1
        ];
        $id = $sqlite->upsert('test_table', $data);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Test Product', $row['name']);
        $this->assertEquals(100, $row['value']);
        $this->assertEquals(99.99, $row['price']);
        $this->assertEquals(1, $row['active']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 验证 affected rows
     */
    public function testUpsertAffectedRows()
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
        $id = $sqlite->upsert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $this->assertEquals(1, $sqlite->effected());

        // 更新数据
        $sqlite->upsert('test_table', ['id' => $id, 'name' => 'Updated Test', 'value' => 200]);
        $this->assertEquals(1, $sqlite->effected());
    }

    /**
     * 测试 Sqlite upsert 方法 - 空字符串参数
     */
    public function testUpsertWithEmptyStringParams()
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
        $id = $sqlite->upsert('test_table', ['name' => '', 'value' => 100]);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);
        $this->assertEquals(100, $row['value']);

        // 更新为空字符串
        $updatedId = $sqlite->upsert('test_table', ['id' => $id, 'name' => '', 'value' => 200]);
        $this->assertEquals($id, $updatedId);

        // 验证更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);
        $this->assertEquals(200, $row['value']);
    }
}