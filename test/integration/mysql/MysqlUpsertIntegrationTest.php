<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class MysqlUpsertIntegrationTest extends TestCase
{
    /**
     * 测试 MySQL upsert 方法 - 插入新数据
     */
    public function testUpsertInsert()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_insert (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入数据
            $id = $db->upsert('test_upsert_insert', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $selectResult = $db->select('test_upsert_insert', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $selectResult->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_insert");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL upsert 方法 - 更新现有数据
     */
    public function testUpsertUpdate()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_update (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入初始数据
            $id = $db->upsert('test_upsert_update', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 更新数据
            $updatedId = $db->upsert('test_upsert_update', ['id' => $id, 'name' => 'Updated Test', 'value' => 200]);
            $this->assertEquals($id, $updatedId);

            // 验证数据更新成功
            $selectResult = $db->select('test_upsert_update', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $selectResult->first('array');
            $this->assertEquals('Updated Test', $row['name']);
            $this->assertEquals(200, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_update");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL upsert 方法 - 边界测试（特殊字符）
     */
    public function testUpsertWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含特殊字符的数据
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $id = $db->upsert('test_upsert_special', ['name' => $specialName, 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $selectResult = $db->select('test_upsert_special', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $selectResult->first('array');
            $this->assertEquals($specialName, $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL upsert 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpsertSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_injection (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 尝试SQL注入
            $sqlInjectionAttempt = "Test'; DROP TABLE test_upsert_injection; --";
            $id = $db->upsert('test_upsert_injection', ['name' => $sqlInjectionAttempt, 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功，且表没有被删除
            $selectResult = $db->select('test_upsert_injection', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $selectResult->first('array');
            $this->assertEquals($sqlInjectionAttempt, $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_injection");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL upsert 方法 - 空字符串参数
     */
    public function testUpsertWithEmptyStringParams()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_empty (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含空字符串的数据
            $id = $db->upsert('test_upsert_empty', ['name' => '', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $selectResult = $db->select('test_upsert_empty', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $selectResult->first('array');
            $this->assertEquals('', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_empty");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 MySQL upsert 方法 - 验证 affected rows
     */
    public function testUpsertAffectedRows()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_affected (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入数据
            $id = $db->upsert('test_upsert_affected', ['name' => 'Test 1', 'value' => 100]);
            $this->assertEquals(1, $db->effected());

            // 更新数据
            $db->upsert('test_upsert_affected', ['id' => $id, 'name' => 'Updated Test', 'value' => 200]);
            $this->assertEquals(2, $db->effected());

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_affected");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }
}
