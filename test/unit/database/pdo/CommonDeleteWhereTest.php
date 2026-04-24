<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonDeleteWhereTest extends TestCase
{
    /**
     * 测试 delete 方法 - 使用布尔值作为 where 条件
     */
    public function testDeleteWithBooleanWhereCondition()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_boolean (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            active INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_boolean (name, value, active) VALUES (:name, :value, :active)', ['name' => 'Test 1', 'value' => 100, 'active' => 1]);
        $sqlite->execute('INSERT INTO test_delete_boolean (name, value, active) VALUES (:name, :value, :active)', ['name' => 'Test 2', 'value' => 200, 'active' => 0]);

        // 测试使用 true 作为条件
        $affected = $sqlite->delete('test_delete_boolean', ['active', '=', true]);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_boolean WHERE active = 1');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证 active = 0 的记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_boolean WHERE active = 0');
        $row = $result->first('array');
        $this->assertEquals('Test 2', $row['name']);

        // 测试使用 false 作为条件
        $affected = $sqlite->delete('test_delete_boolean', ['active', '=', false]);
        $this->assertEquals(1, $affected);

        // 验证所有数据都被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_boolean');
        $rows = $result->all('array');
        $this->assertCount(0, $rows);
    }

    /**
     * 测试 delete 方法 - 使用 null 值作为 where 条件
     */
    public function testDeleteWithNullWhereCondition()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_null (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            email TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 1', 'value' => 100, 'email' => 'test1@example.com']);
        $sqlite->execute('INSERT INTO test_delete_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 2', 'value' => 200, 'email' => null]);

        // 测试使用 null 作为条件
        $affected = $sqlite->delete('test_delete_null', ['email', '=', null]);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_null WHERE email IS NULL');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证 email 不为 null 的记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_null WHERE email IS NOT NULL');
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);

        // 清理
        $sqlite->execute('DROP TABLE IF EXISTS test_delete_null');
    }

    /**
     * 测试 delete 方法 - 使用 IS NULL 操作符
     */
    public function testDeleteWithIsNullOperator()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_is_null (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            email TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_is_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 1', 'value' => 100, 'email' => 'test1@example.com']);
        $sqlite->execute('INSERT INTO test_delete_is_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 2', 'value' => 200, 'email' => null]);

        // 测试使用 IS NULL 操作符
        $affected = $sqlite->delete('test_delete_is_null', ['email', '=', null]);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_is_null WHERE email IS NULL');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证 email 不为 null 的记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_is_null WHERE email IS NOT NULL');
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);

        // 清理
        $sqlite->execute('DROP TABLE IF EXISTS test_delete_is_null');
    }

    /**
     * 测试 delete 方法 - 使用 IS NOT NULL 操作符
     */
    public function testDeleteWithIsNotNullOperator()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_is_not_null (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            email TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_is_not_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 1', 'value' => 100, 'email' => 'test1@example.com']);
        $sqlite->execute('INSERT INTO test_delete_is_not_null (name, value, email) VALUES (:name, :value, :email)', ['name' => 'Test 2', 'value' => 200, 'email' => null]);

        // 测试使用 IS NOT NULL 操作符
        $affected = $sqlite->delete('test_delete_is_not_null', ['email', '<>','null']);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_is_not_null WHERE email IS NOT NULL');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证 email 为 null 的记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_is_not_null WHERE email IS NULL');
        $row = $result->first('array');
        $this->assertEquals('Test 2', $row['name']);

        // 清理
        $sqlite->execute('DROP TABLE IF EXISTS test_delete_is_not_null');
    }

    /**
     * 测试 delete 方法 - 组合布尔值和其他条件
     */
    public function testDeleteWithCombinedBooleanCondition()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_combined_boolean (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            active INTEGER,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_combined_boolean (name, value, active, category) VALUES (:name, :value, :active, :category)', ['name' => 'Test 1', 'value' => 100, 'active' => 1, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_delete_combined_boolean (name, value, active, category) VALUES (:name, :value, :active, :category)', ['name' => 'Test 2', 'value' => 200, 'active' => 1, 'category' => 'B']);
        $sqlite->execute('INSERT INTO test_delete_combined_boolean (name, value, active, category) VALUES (:name, :value, :active, :category)', ['name' => 'Test 3', 'value' => 300, 'active' => 0, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_delete_combined_boolean (name, value, active, category) VALUES (:name, :value, :active, :category)', ['name' => 'Test 4', 'value' => 400, 'active' => 0, 'category' => 'B']);

        // 测试组合布尔值和其他条件：active = true 且 category = 'A'
        $affected = $sqlite->delete('test_delete_combined_boolean', ['active', '=', true], ['category', '=', 'A']);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_combined_boolean WHERE active = 1 AND category = "A"');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证其他记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_combined_boolean');
        $rows = $result->all('array');
        $this->assertCount(3, $rows);

        // 清理
        $sqlite->execute('DROP TABLE IF EXISTS test_delete_combined_boolean');
    }

    /**
     * 测试 delete 方法 - 组合 null 值和其他条件
     */
    public function testDeleteWithCombinedNullCondition()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_combined_null (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            email TEXT,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_delete_combined_null (name, value, email, category) VALUES (:name, :value, :email, :category)', ['name' => 'Test 1', 'value' => 100, 'email' => 'test1@example.com', 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_delete_combined_null (name, value, email, category) VALUES (:name, :value, :email, :category)', ['name' => 'Test 2', 'value' => 200, 'email' => null, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_delete_combined_null (name, value, email, category) VALUES (:name, :value, :email, :category)', ['name' => 'Test 3', 'value' => 300, 'email' => 'test3@example.com', 'category' => 'B']);
        $sqlite->execute('INSERT INTO test_delete_combined_null (name, value, email, category) VALUES (:name, :value, :email, :category)', ['name' => 'Test 4', 'value' => 400, 'email' => null, 'category' => 'B']);

        // 测试组合 null 值和其他条件：email IS NULL 且 category = 'A'
        $affected = $sqlite->delete('test_delete_combined_null', ['email','=', null], ['category', '=', 'A']);
        $this->assertEquals(1, $affected);

        // 验证数据删除成功
        $result = $sqlite->execute('SELECT * FROM test_delete_combined_null WHERE email IS NULL AND category = "A"');
        $row = $result->first('array');
        $this->assertNull($row);

        // 验证其他记录未被删除
        $result = $sqlite->execute('SELECT * FROM test_delete_combined_null');
        $rows = $result->all('array');
        $this->assertCount(3, $rows);

        // 清理
        $sqlite->execute('DROP TABLE IF EXISTS test_delete_combined_null');
    }
}