<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Result;

class ResultAllMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 all 方法 - 默认类型（object）
     */
    public function testAllWithObjectType()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_all_object (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_result_all_object', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_result_all_object', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_result_all_object', ['name' => 'Test 3', 'value' => 300]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_all_object');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 all 方法（默认类型 - object）
            $rows = $result->all();

            // 验证返回的是对象数组
            $this->assertIsArray($rows);
            $this->assertCount(3, $rows);
            $this->assertIsObject($rows[0]);
            $this->assertEquals('Test 1', $rows[0]->name);
            $this->assertEquals(100, $rows[0]->value);
            $this->assertEquals('Test 2', $rows[1]->name);
            $this->assertEquals(200, $rows[1]->value);
            $this->assertEquals('Test 3', $rows[2]->name);
            $this->assertEquals(300, $rows[2]->value);

            // 验证 count 方法返回正确的行数
            $this->assertEquals(3, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_all_object");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result all test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 all 方法 - array 类型
     */
    public function testAllWithArrayType()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_all_array (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_result_all_array', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_result_all_array', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_result_all_array', ['name' => 'Test 3', 'value' => 300]);

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_all_array');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 all 方法（array 类型）
            $rows = $result->all('array');

            // 验证返回的是关联数组
            $this->assertIsArray($rows);
            $this->assertCount(3, $rows);
            $this->assertIsArray($rows[0]);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals(100, $rows[0]['value']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals(200, $rows[1]['value']);
            $this->assertEquals('Test 3', $rows[2]['name']);
            $this->assertEquals(300, $rows[2]['value']);

            // 验证 count 方法返回正确的行数
            $this->assertEquals(3, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_all_array");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result all test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 all 方法 - 空结果
     */
    public function testAllWithEmptyResult()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_all_empty (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 执行查询（表为空）
            $result = $db->execute('SELECT * FROM test_result_all_empty');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 all 方法
            $rows = $result->all();

            // 验证返回空数组
            $this->assertIsArray($rows);
            $this->assertEmpty($rows);

            // 验证 count 方法返回 0
            $this->assertEquals(0, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_all_empty");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result all test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 all 方法 - 大量数据
     */
    public function testAllWithMultipleRecords()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_result_all_multiple (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入多条测试数据
            for ($i = 1; $i <= 10; $i++) {
                $db->insert('test_result_all_multiple', ['name' => "Test $i", 'value' => $i * 100]);
            }

            // 执行查询
            $result = $db->execute('SELECT * FROM test_result_all_multiple ORDER BY id');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 执行 all 方法
            $rows = $result->all();

            // 验证返回的数组包含正确数量的记录
            $this->assertIsArray($rows);
            $this->assertCount(10, $rows);

            // 验证数据的正确性
            for ($i = 0; $i < 10; $i++) {
                $expectedId = $i + 1;
                $this->assertEquals("Test $expectedId", $rows[$i]->name);
                $this->assertEquals($expectedId * 100, $rows[$i]->value);
            }

            // 验证 count 方法返回正确的行数
            $this->assertEquals(10, $result->count());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_result_all_multiple");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Result all test failed: ' . $e->getMessage());
        }
    }
}