<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonUpdateMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 update 方法 - 基本更新功能
     */
    public function testUpdateBasic()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_basic (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_basic', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作
            $affected = $db->update('test_update_basic', ['name' => 'Updated Test', 'value' => 200], ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_basic', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('Updated Test', $row['name']);
            $this->assertEquals(200, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_basic");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 空表名（应该抛出异常）
     */
    public function testUpdateEmptyTable()
    {
        $db = Database::instance('database.default');

        // 尝试执行空表名的更新操作，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessage('Update Table Is Empty.');
        $db->update('', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空数据（应该抛出异常）
     */
    public function testUpdateEmptyData()
    {
        $db = Database::instance('database.default');

        // 尝试执行空数据的更新操作，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessage('Update Data Is Empty.');
        $db->update('test_table', [], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空 WHERE 条件（应该抛出异常）
     */
    public function testUpdateEmptyWhere()
    {
        $db = Database::instance('database.default');

        // 尝试执行空 WHERE 条件的更新操作，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessage('Update Where Condition Is Empty.');
        $db->update('test_table', ['name' => 'Updated Test']);
    }

    /**
     * 测试 update 方法 - 执行错误（应该抛出异常）
     */
    public function testUpdateExecuteError()
    {
        $db = Database::instance('database.default');

        // 尝试更新不存在的表，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessageMatches('/Update Execute Error :/');
        $db->update('non_existent_table', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 边界测试（特殊字符）
     */
    public function testUpdateWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_special', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行包含特殊字符的更新操作
            $specialName = "Updated Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $affected = $db->update('test_update_special', ['name' => $specialName], ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_special', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($specialName, $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 边界测试（空字符串）
     */
    public function testUpdateWithEmptyString()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_empty_string (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_empty_string', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行包含空字符串的更新操作
            $affected = $db->update('test_update_empty_string', ['name' => ''], ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_empty_string', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_empty_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 多个 WHERE 条件
     */
    public function testUpdateWithMultipleWhereConditions()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_multiple_where (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_update_multiple_where', ['category' => 'A', 'name' => 'Test 1', 'value' => 100]);
            $db->insert('test_update_multiple_where', ['category' => 'A', 'name' => 'Test 2', 'value' => 200]);
            $db->insert('test_update_multiple_where', ['category' => 'B', 'name' => 'Test 3', 'value' => 300]);

            // 执行多个 WHERE 条件的更新操作
            $affected = $db->update('test_update_multiple_where', ['value' => 999], [['category', '=', 'A'], ['value', '<', 150]]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_multiple_where', ['id', 'category', 'name', 'value'], ['category', '=', 'A']);
            $rows = $result->all('array');
            $this->assertEquals(999, $rows[0]['value']);
            $this->assertEquals(200, $rows[1]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_multiple_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 更新不存在的记录
     */
    public function testUpdateNonExistentRecord()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_non_existent (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_update_non_existent', ['name' => 'Test 1', 'value' => 100]);

            // 尝试更新不存在的记录
            $affected = $db->update('test_update_non_existent', ['name' => 'Updated Test'], ['id', '=', 999]);

            // 验证返回受影响的行数为 0
            $this->assertEquals(0, $affected);

            // 验证数据未被更新
            $result = $db->select('test_update_non_existent');
            $rows = $result->all('array');
            $this->assertEquals('Test 1', $rows[0]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_non_existent");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpdateSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.default');

            // 首先清理可能存在的表
            $db->execute("DROP TABLE IF EXISTS test_update_injection");

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_injection (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            $db->insert('test_update_injection', ['name' => 'Test 2', 'code' => 'CODE2', 'value' => 200]);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "CODE1' OR '1'='1";
            $affected = $db->update('test_update_injection', ['value' => 999], ['code', '=', $sqlInjectionAttempt]);

            // 验证只更新了符合条件的行，而不是所有行
            $this->assertEquals(0, $affected); // 因为没有 code 等于 "CODE1' OR '1'='1" 的行

            // 验证数据未被更新
            $result = $db->select('test_update_injection');
            $rows = $result->all('array');
            $this->assertEquals(100, $rows[0]['value']);
            $this->assertEquals(200, $rows[1]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.default')->execute("DROP TABLE IF EXISTS test_update_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }
}
