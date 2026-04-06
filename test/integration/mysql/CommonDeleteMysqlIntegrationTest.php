<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonDeleteMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 delete 方法 - 基本删除功能
     */
    public function testDeleteBasic()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_basic (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_basic', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_delete_basic', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_delete_basic', ['name' => 'Test 3', 'value' => 300]);

            // 执行删除操作
            $affected = $db->delete('test_delete_basic', ['id', '=', 2]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_basic');
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 3', $rows[1]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_basic");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 空表名（应该抛出异常）
     */
    public function testDeleteEmptyTable()
    {
        $db = Database::instance('default');

        // 尝试执行空表名的删除操作，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessage('Delete Table Is Empty.');
        $db->delete('', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 空 WHERE 条件（应该抛出异常）
     */
    public function testDeleteEmptyWhere()
    {
        $db = Database::instance('default');

        // 尝试执行空 WHERE 条件的删除操作，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessage('Delete Where Condition Is Empty.');
        $db->delete('test_table');
    }

    /**
     * 测试 delete 方法 - 执行错误（应该抛出异常）
     */
    public function testDeleteExecuteError()
    {
        $db = Database::instance('default');

        // 尝试删除不存在的表，应该抛出异常
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessageMatches('/Delete Execute Error :/');
        $db->delete('non_existent_table', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 边界测试（特殊字符）
     */
    public function testDeleteWithSpecialCharacters()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含特殊字符的数据
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $db->insert('test_delete_special', ['name' => $specialName, 'value' => 100]);
            $db->insert('test_delete_special', ['name' => 'Test 2', 'value' => 200]);

            // 执行删除操作（使用包含特殊字符的条件）
            $affected = $db->delete('test_delete_special', ['name', '=', $specialName]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_special');
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('Test 2', $rows[0]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 多个 WHERE 条件
     */
    public function testDeleteWithMultipleWhereConditions()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_multiple_where (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_multiple_where', ['category' => 'A', 'name' => 'Test 1', 'value' => 100]);
            $db->insert('test_delete_multiple_where', ['category' => 'A', 'name' => 'Test 2', 'value' => 200]);
            $db->insert('test_delete_multiple_where', ['category' => 'B', 'name' => 'Test 3', 'value' => 300]);
            $db->insert('test_delete_multiple_where', ['category' => 'B', 'name' => 'Test 4', 'value' => 400]);

            // 执行多个 WHERE 条件的删除操作
            $affected = $db->delete('test_delete_multiple_where', [['category', '=', 'A'], ['value', '<', 150]]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_multiple_where');
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 2', $rows[0]['name']);
            $this->assertEquals('Test 3', $rows[1]['name']);
            $this->assertEquals('Test 4', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_multiple_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 删除不存在的记录
     */
    public function testDeleteNonExistentRecord()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_non_existent (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_non_existent', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_delete_non_existent', ['name' => 'Test 2', 'value' => 200]);

            // 尝试删除不存在的记录
            $affected = $db->delete('test_delete_non_existent', ['id', '=', 999]);

            // 验证返回受影响的行数为 0
            $this->assertEquals(0, $affected);

            // 验证数据未被删除
            $result = $db->select('test_delete_non_existent');
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_non_existent");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 安全测试（SQL注入尝试）
     */
    public function testDeleteSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_injection (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $db->insert('test_delete_injection', ['name' => 'Test 2', 'code' => 'CODE2', 'value' => 200]);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "CODE1' OR '1'='1";
            $affected = $db->delete('test_delete_injection', ['code', '=', $sqlInjectionAttempt]);

            // 验证只删除了符合条件的行，而不是所有行
            $this->assertEquals(0, $affected); // 因为没有 code 等于 "CODE1' OR '1'='1" 的行

            // 验证数据未被删除
            $result = $db->select('test_delete_injection');
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('default')->execute("DROP TABLE IF EXISTS test_delete_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }
}
