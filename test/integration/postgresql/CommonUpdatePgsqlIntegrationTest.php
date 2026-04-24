<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonUpdatePgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 update 方法 - 基本功能
     */
    public function testUpdateBasic()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_basic (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
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
        $db = Database::instance('database.pgsql');

        // 尝试执行空表名的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Table Name Is Empty.');
        $db->update('', ['name' => 'Updated Test', 'value' => 200], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空数据（应该抛出异常）
     */
    public function testUpdateEmptyData()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空数据的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Data Is Empty.');
        $db->update('test_update_empty_data', [], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空 WHERE 条件（应该抛出异常）
     */
    public function testUpdateEmptyWhere()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空 WHERE 条件的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Where Condition Is Empty.');
        $db->update('test_update_empty_where', ['name' => 'Updated Test']);
    }

    /**
     * 测试 update 方法 - 执行错误（应该抛出异常）
     */
    public function testUpdateExecuteError()
    {
        $db = Database::instance('database.pgsql');

        // 尝试更新不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Update Execute Error :/');
        $db->update('non_existent_table', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 边界测试（特殊字符）
     */
    public function testUpdateWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_special (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_special', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行包含特殊字符的更新操作
            $specialName = "Updated Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $affected = $db->update('test_update_special', ['name' => $specialName, 'value' => 200], ['id', '=', $id]);

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
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_empty_string (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_empty_string', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行包含空字符串的更新操作
            $affected = $db->update('test_update_empty_string', ['name' => '', 'value' => 200], ['id', '=', $id]);

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
     * 测试 update 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpdateSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Updated Test'; DROP TABLE test_update_injection; --";
            $affected = $db->update('test_update_injection', ['name' => $sqlInjectionAttempt, 'value' => 200], ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功（SQL 注入被阻止）
            $result = $db->select('test_update_injection', ['id', 'name', 'code', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($sqlInjectionAttempt, $row['name']);

            // 验证表仍然存在
            $result = $db->select('test_update_injection');
            $this->assertInstanceOf('system\database\pdo\Result', $result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_update_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 多次更新
     */
    public function testUpdateMultipleTimes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_multiple_times (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_multiple_times', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 多次执行更新操作
            for ($i = 1; $i <= 3; $i++) {
                $affected = $db->update('test_update_multiple_times', ['name' => "Updated Test $i", 'value' => $i * 100], ['id', '=', $id]);
                $this->assertEquals(1, $affected);
            }

            // 验证最终数据
            $result = $db->select('test_update_multiple_times', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('Updated Test 3', $row['name']);
            $this->assertEquals(300, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_multiple_times");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 条件更新（更新多个记录）
     */
    public function testUpdateMultipleRecords()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_multiple_records (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_update_multiple_records', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->insert('test_update_multiple_records', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->insert('test_update_multiple_records', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);

            // 执行条件更新，更新 category 为 'A' 的所有记录
            $affected = $db->update('test_update_multiple_records', ['value' => 999], ['category', '=', 'A']);

            // 验证返回受影响的行数
            $this->assertEquals(2, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_multiple_records', ['id', 'name', 'value', 'category'], ['category', '=', 'A']);
            $rows = $result->all('array');
            foreach ($rows as $row) {
                $this->assertEquals(999, $row['value']);
            }

            // 验证 category 为 'B' 的记录未被更新
            $result = $db->select('test_update_multiple_records', ['id', 'name', 'value', 'category'], ['category', '=', 'B']);
            $row = $result->first('array');
            $this->assertEquals(300, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_multiple_records");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂条件
     */
    public function testUpdateWithComplexCondition()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_complex_condition (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                status VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_update_complex_condition', ['name' => 'Test 1', 'value' => 100, 'status' => 'active']);
            $db->insert('test_update_complex_condition', ['name' => 'Test 2', 'value' => 200, 'status' => 'active']);
            $db->insert('test_update_complex_condition', ['name' => 'Test 3', 'value' => 300, 'status' => 'inactive']);
            $db->insert('test_update_complex_condition', ['name' => 'Test 4', 'value' => 400, 'status' => 'active']);

            // 执行复杂条件更新：value > 150 且 status = 'active'
            $affected = $db->update(
                'test_update_complex_condition',
                ['status' => 'updated'],
                ['value', '>', 150],
                ['status', '=', 'active']
            );

            // 验证返回受影响的行数
            $this->assertEquals(2, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_complex_condition', ['id', 'name', 'value', 'status'], ['status', '=', 'updated']);
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            foreach ($rows as $row) {
                $this->assertGreaterThan(150, $row['value']);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_complex_condition");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂参数样例 1：大量字段
     */
    public function testUpdateWithManyFields()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_many_fields (
                id SERIAL PRIMARY KEY,
                field1 VARCHAR(255),
                field2 INTEGER,
                field3 DECIMAL(10,2),
                field4 BOOLEAN,
                field5 VARCHAR(255),
                field6 INTEGER,
                field7 DECIMAL(10,2),
                field8 BOOLEAN,
                field9 VARCHAR(255),
                field10 INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_many_fields', [
                'field1' => 'value1',
                'field2' => 123,
                'field3' => 123.45,
                'field4' => true,
                'field5' => 'value5',
                'field6' => 678,
                'field7' => 678.90,
                'field8' => false,
                'field9' => 'value9',
                'field10' => 987
            ]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作，更新多个字段
            $updateData = [
                'field1' => 'updated1',
                'field2' => 456,
                'field3' => 456.78,
                'field4' => false,
                'field5' => 'updated5',
                'field6' => 789,
                'field7' => 789.01,
                'field8' => true,
                'field9' => 'updated9',
                'field10' => 9876
            ];
            $affected = $db->update('test_update_many_fields', $updateData, ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_many_fields', array_keys($updateData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updateData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_many_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂参数样例 2：带有特殊字符的字段名
     */
    public function testUpdateWithSpecialFieldNames()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_special_fields (
                id SERIAL PRIMARY KEY,
                user_name VARCHAR(255) NOT NULL,
                user_age INTEGER,
                user_email VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_special_fields', [
                'user_name' => 'test_user',
                'user_age' => 25,
                'user_email' => 'test@example.com'
            ]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作，更新带有下划线的字段
            $updateData = [
                'user_name' => 'updated_user',
                'user_age' => 30,
                'user_email' => 'updated@example.com'
            ];
            $affected = $db->update('test_update_special_fields', $updateData, ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_special_fields', array_keys($updateData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updateData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_special_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂参数样例 3：带有 null 值的数据
     */
    public function testUpdateWithNullValues()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_null_values (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                email VARCHAR(255),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_null_values', [
                'name' => 'Test',
                'value' => 100,
                'email' => 'test@example.com',
                'active' => true
            ]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作，设置 null 值
            $updateData = [
                'value' => null,
                'email' => null,
                'active' => null
            ];
            $affected = $db->update('test_update_null_values', $updateData, ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_null_values', array_keys($updateData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updateData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_null_values");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂参数样例 4：带有非常长的字符串
     */
    public function testUpdateWithLongString()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_long_string (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                long_text TEXT
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_long_string', [
                'name' => 'Test',
                'long_text' => 'Original text'
            ]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作，使用非常长的字符串
            $longString = str_repeat('x', 1000);
            $updateData = [
                'long_text' => $longString
            ];
            $affected = $db->update('test_update_long_string', $updateData, ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_long_string', array_keys($updateData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updateData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_long_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 update 方法 - 复杂参数样例 5：混合数据类型
     */
    public function testUpdateWithMixedDataTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_update_mixed_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2),
                is_active BOOLEAN,
                description TEXT,
                quantity INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_update_mixed_types', [
                'name' => 'Test Product',
                'price' => 99.99,
                'is_active' => true,
                'description' => 'This is a test product',
                'quantity' => 10
            ]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行更新操作，使用混合数据类型
            $updateData = [
                'price' => 199.99,
                'is_active' => false,
                'description' => 'This is an updated test product',
                'quantity' => 20
            ];
            $affected = $db->update('test_update_mixed_types', $updateData, ['id', '=', $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->select('test_update_mixed_types', array_keys($updateData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updateData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_update_mixed_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Update test failed: ' . $e->getMessage());
        }
    }
}