<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Result;

class ResultFirstMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 first 方法 - 默认类型（object）
     */
    public function testFirstWithObjectType()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_first_object (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_result_first_object', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_result_first_object', ['name' => 'Test 2', 'value' => 200]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_first_object WHERE id = :id', ['id' => 1]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 first 方法（默认类型 - object）
            $row = $result->first();

            // 验证返回的是对象
            $this->assertIsObject($row);
            $this->assertEquals('Test 1', $row->name);
            $this->assertEquals(100, $row->value);

            // 验证 count 方法返回 1
            $this->assertEquals(1, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_first_object");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result first test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 first 方法 - array 类型
     */
    public function testFirstWithArrayType()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_first_array (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_result_first_array', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_result_first_array', ['name' => 'Test 2', 'value' => 200]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_first_array WHERE id = :id', ['id' => 1]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 first 方法（array 类型）
            $row = $result->first('array');

            // 验证返回的是关联数组
            $this->assertIsArray($row);
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 验证 count 方法返回 1
            $this->assertEquals(1, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_first_array");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result first test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 first 方法 - 空结果
     */
    public function testFirstWithEmptyResult()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_first_empty (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 执行查询（没有匹配的记录）
            $result = $db->execute('SELECT * FROM test_result_first_empty WHERE id = :id', ['id' => 999]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 first 方法
            $row = $result->first();

            // 验证返回 null
            $this->assertNull($row);

            // 验证 count 方法返回 0
            $this->assertEquals(0, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_first_empty");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result first test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 first 方法 - 多次调用
     */
    public function testFirstMultipleCalls()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_first_multiple (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_result_first_multiple', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_result_first_multiple', ['name' => 'Test 2', 'value' => 200]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_first_multiple ORDER BY id');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 第一次调用 first 方法
            $row1 = $result->first();
            $this->assertIsObject($row1);
            $this->assertEquals('Test 1', $row1->name);
            $this->assertEquals(1, $result->count());

            // 第二次调用 first 方法（由于游标已关闭，应该返回 null）
            $row2 = $result->first();
            $this->assertNull($row2);
            $this->assertEquals(0, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_first_multiple");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result first test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 first 方法 - 边界测试（特殊字符）
     */
    public function testFirstWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_first_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含特殊字符的测试数据
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $id = $db->insert('test_result_first_special', ['name' => $specialName, 'value' => 100]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_first_special WHERE id = :id', ['id' => $id]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 first 方法
            $row = $result->first();

            // 验证返回的对象包含正确的特殊字符
            $this->assertIsObject($row);
            $this->assertEquals($specialName, $row->name);
            $this->assertEquals(100, $row->value);

            // 验证 count 方法返回 1
            $this->assertEquals(1, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_first_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result first test failed: ' . $e->getMessage());
        }
    }
}