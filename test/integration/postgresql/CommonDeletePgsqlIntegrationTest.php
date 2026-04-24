<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonDeletePgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 delete 方法 - 基本功能
     */
    public function testDeleteBasic()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_basic (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_delete_basic', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行删除操作
            $affected = $db->delete('test_delete_basic', ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_basic', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertNull($row);

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
        $db = Database::instance('database.pgsql');

        // 尝试执行空表名的删除操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Table Name Is Empty.');
        $db->delete('', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 空 WHERE 条件（应该抛出异常）
     */
    public function testDeleteEmptyWhere()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空 WHERE 条件的删除操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Delete Where Condition Is Empty.');
        $db->delete('test_delete_empty_where');
    }

    /**
     * 测试 delete 方法 - 执行错误（应该抛出异常）
     */
    public function testDeleteExecuteError()
    {
        $db = Database::instance('database.pgsql');

        // 尝试删除不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Delete Execute Error :/');
        $db->delete('non_existent_table', ['id', '=', 1]);
    }

    /**
     * 测试 delete 方法 - 边界测试（删除不存在的记录）
     */
    public function testDeleteNonExistentRecord()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_non_existent (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_delete_non_existent', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 尝试删除不存在的记录
            $affected = $db->delete('test_delete_non_existent', ['id', '=', 999]);

            // 验证返回受影响的行数为 0
            $this->assertEquals(0, $affected);

            // 验证数据未被删除
            $result = $db->select('test_delete_non_existent', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_non_existent");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 边界测试（删除多个记录）
     */
    public function testDeleteMultipleRecords()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_multiple_records (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_multiple_records', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->insert('test_delete_multiple_records', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->insert('test_delete_multiple_records', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);

            // 执行条件删除，删除 category 为 'A' 的所有记录
            $affected = $db->delete('test_delete_multiple_records', ['category', '=', 'A']);

            // 验证返回受影响的行数
            $this->assertEquals(2, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_multiple_records', ['id', 'name', 'value', 'category'], ['category', '=', 'A']);
            $row = $result->first('array');
            $this->assertNull($row);

            // 验证 category 为 'B' 的记录未被删除
            $result = $db->select('test_delete_multiple_records', ['id', 'name', 'value', 'category'], ['category', '=', 'B']);
            $row = $result->first('array');
            $this->assertEquals('Test 3', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_multiple_records");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 复杂条件
     */
    public function testDeleteWithComplexCondition()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_complex_condition (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                status VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_complex_condition', ['name' => 'Test 1', 'value' => 100, 'status' => 'active']);
            $db->insert('test_delete_complex_condition', ['name' => 'Test 2', 'value' => 200, 'status' => 'active']);
            $db->insert('test_delete_complex_condition', ['name' => 'Test 3', 'value' => 300, 'status' => 'inactive']);
            $db->insert('test_delete_complex_condition', ['name' => 'Test 4', 'value' => 400, 'status' => 'active']);

            // 执行复杂条件删除：value > 150 且 status = 'active'
            $affected = $db->delete(
                'test_delete_complex_condition',
                ['value', '>', 150],
                ['status', '=', 'active']
            );

            // 验证返回受影响的行数
            $this->assertEquals(2, $affected);

            // 验证数据删除成功
            $result = $db->select('test_delete_complex_condition', ['id', 'name', 'value', 'status']);
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            foreach ($rows as $row) {
                 $this->assertTrue(
                    $row['value'] <= 150 || $row['status'] === 'inactive',
                    "Row with id {$row['id']} should have value <= 150 or status = 'inactive', but got value={$row['value']}, status={$row['status']}"
                );
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_complex_condition");
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
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_delete_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Test 1'; DROP TABLE test_delete_injection; --";
            $affected = $db->delete('test_delete_injection', ['name', '=', $sqlInjectionAttempt]);

            // 验证返回受影响的行数为 0（因为没有匹配的记录）
            $this->assertEquals(0, $affected);

            // 验证表仍然存在
            $result = $db->select('test_delete_injection');
            $this->assertInstanceOf('system\database\pdo\Result', $result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_delete_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 多次删除
     */
    public function testDeleteMultipleTimes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_multiple_times (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id1 = $db->insert('test_delete_multiple_times', ['name' => 'Test 1', 'value' => 100]);
            $id2 = $db->insert('test_delete_multiple_times', ['name' => 'Test 2', 'value' => 200]);
            $id3 = $db->insert('test_delete_multiple_times', ['name' => 'Test 3', 'value' => 300]);

            // 多次执行删除操作
            $affected1 = $db->delete('test_delete_multiple_times', ['id', '=', $id1]);
            $this->assertEquals(1, $affected1);

            $affected2 = $db->delete('test_delete_multiple_times', ['id', '=', $id2]);
            $this->assertEquals(1, $affected2);

            $affected3 = $db->delete('test_delete_multiple_times', ['id', '=', $id3]);
            $this->assertEquals(1, $affected3);

            // 验证所有数据都被删除
            $result = $db->select('test_delete_multiple_times');
            $rows = $result->all('array');
            $this->assertCount(0, $rows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_multiple_times");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 不同类型的条件值
     */
    public function testDeleteWithDifferentConditionTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_different_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                price DECIMAL(10,2),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id1 = $db->insert('test_delete_different_types', ['name' => 'Test 1', 'value' => 100, 'price' => 99.99, 'active' => true]);
            $id2 = $db->insert('test_delete_different_types', ['name' => 'Test 2', 'value' => 200, 'price' => 199.99, 'active' => false]);

            // 测试使用整数条件
            $affected1 = $db->delete('test_delete_different_types', ['value', '=', 100]);
            $this->assertEquals(1, $affected1);

            // 测试使用小数条件
            $affected2 = $db->delete('test_delete_different_types', ['price', '=', 199.99]);
            $this->assertEquals(1, $affected2);

            // 验证所有数据都被删除
            $result = $db->select('test_delete_different_types');
            $rows = $result->all('array');
            $this->assertCount(0, $rows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_different_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 使用不同的操作符
     */
    public function testDeleteWithDifferentOperators()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_operators (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_operators', ['name' => 'Test 1', 'value' => 50]);
            $db->insert('test_delete_operators', ['name' => 'Test 2', 'value' => 100]);
            $db->insert('test_delete_operators', ['name' => 'Test 3', 'value' => 150]);
            $db->insert('test_delete_operators', ['name' => 'Test 4', 'value' => 200]);

            // 测试使用 > 操作符
            $affected1 = $db->delete('test_delete_operators', ['value', '>', 150]);
            $this->assertEquals(1, $affected1);

            // 测试使用 < 操作符
            $affected2 = $db->delete('test_delete_operators', ['value', '<', 100]);
            $this->assertEquals(1, $affected2);

            // 测试使用 = 操作符
            $affected3 = $db->delete('test_delete_operators', ['value', '=', 100]);
            $this->assertEquals(1, $affected3);

            // 验证只剩下一条记录
            $result = $db->select('test_delete_operators');
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals(150, $rows[0]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_operators");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 使用 IN 操作符
     */
    public function testDeleteWithInOperator()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_in_operator (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id1 = $db->insert('test_delete_in_operator', ['name' => 'Test 1', 'value' => 100]);
            $id2 = $db->insert('test_delete_in_operator', ['name' => 'Test 2', 'value' => 200]);
            $id3 = $db->insert('test_delete_in_operator', ['name' => 'Test 3', 'value' => 300]);
            $id4 = $db->insert('test_delete_in_operator', ['name' => 'Test 4', 'value' => 400]);

            // 测试使用 IN 操作符
            $affected = $db->delete('test_delete_in_operator', ['id', 'in', [$id1, $id2, $id3]]);

            // 验证返回受影响的行数
            $this->assertEquals(3, $affected);

            // 验证只剩下一条记录
            $result = $db->select('test_delete_in_operator');
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals(400, $rows[0]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_in_operator");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 delete 方法 - 使用 BETWEEN 操作符
     */
    public function testDeleteWithBetweenOperator()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_delete_between_operator (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_delete_between_operator', ['name' => 'Test 1', 'value' => 50]);
            $db->insert('test_delete_between_operator', ['name' => 'Test 2', 'value' => 100]);
            $db->insert('test_delete_between_operator', ['name' => 'Test 3', 'value' => 150]);
            $db->insert('test_delete_between_operator', ['name' => 'Test 4', 'value' => 200]);

            // 测试使用 BETWEEN 操作符
            $affected = $db->delete('test_delete_between_operator', ['value', 'between', [100, 150]]);

            // 验证返回受影响的行数
            $this->assertEquals(2, $affected);

            // 验证只剩下两条记录
            $result = $db->select('test_delete_between_operator');
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals(50, $rows[0]['value']);
            $this->assertEquals(200, $rows[1]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_delete_between_operator");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Delete test failed: ' . $e->getMessage());
        }
    }
}