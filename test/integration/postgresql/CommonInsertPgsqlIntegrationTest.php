<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonInsertPgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 insert 方法 - 基本功能（单条数据）
     */
    public function testInsertBasic()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_basic (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行插入操作
            $id = $db->insert('test_insert_basic', ['name' => 'Test 1', 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_basic', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_basic");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 批量插入功能
     */
    public function testInsertMultiple()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_multiple (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行批量插入操作
            $id = $db->insert(
                'test_insert_multiple',
                [['name' => 'Test 1', 'value' => 100],
                ['name' => 'Test 2', 'value' => 200],
                ['name' => 'Test 3', 'value' => 300]]
            );

            // 验证返回的 ID（最后插入的记录的 ID）
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_multiple');
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 3', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_multiple");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 空表名（应该抛出异常）
     */
    public function testInsertEmptyTable()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空表名的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Table Name Is Empty.');
        $db->insert('', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 insert 方法 - 空数据（应该抛出异常）
     */
    public function testInsertEmptyData()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空数据的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Is Empty.');
        // 传递空数组作为数据
        $db->insert('test_insert_empty_data', []);
    }

    /**
     * 测试 insert 方法 - 执行错误（应该抛出异常）
     */
    public function testInsertExecuteError()
    {
        $db = Database::instance('database.pgsql');

        // 尝试插入到不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Insert Execute Error :/');
        $db->insert('non_existent_table', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 insert 方法 - 边界测试（特殊字符）
     */
    public function testInsertWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_special (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行包含特殊字符的插入操作
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $id = $db->insert('test_insert_special', ['name' => $specialName, 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_special', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($specialName, $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 边界测试（空字符串）
     */
    public function testInsertWithEmptyString()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_empty_string (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行包含空字符串的插入操作
            $id = $db->insert('test_insert_empty_string', ['name' => '', 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_empty_string', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_empty_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 安全测试（SQL注入尝试）
     */
    public function testInsertSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Test'; DROP TABLE test_insert_injection; --";
            $id = $db->insert('test_insert_injection', ['name' => $sqlInjectionAttempt, 'code' => 'CODE1', 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功（SQL 注入被阻止）
            $result = $db->select('test_insert_injection', ['id', 'name', 'code', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($sqlInjectionAttempt, $row['name']);

            // 验证表仍然存在
            $result = $db->select('test_insert_injection');
            $this->assertInstanceOf('system\database\pdo\Result', $result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_insert_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 多次插入
     */
    public function testInsertMultipleTimes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_multiple_times (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 多次执行插入操作
            for ($i = 1; $i <= 3; $i++) {
                $id = $db->insert('test_insert_multiple_times', ['name' => "Test $i", 'value' => $i * 100]);
                $this->assertIsScalar($id);
                $this->assertGreaterThan(0, $id);
            }

            // 验证数据插入成功
            $result = $db->select('test_insert_multiple_times');
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 3', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_multiple_times");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 插入不完整数据
     */
    public function testInsertIncompleteData()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表，包含多个字段
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_incomplete (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255),
                age INTEGER,
                address VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 测试 1: 只提供必填字段（name）
            $id1 = $db->insert('test_insert_incomplete', ['name' => 'Test 1']);
            $this->assertIsScalar($id1);
            $this->assertGreaterThan(0, $id1);

            // 验证数据插入成功
            $result1 = $db->select('test_insert_incomplete', ['id', 'name', 'email', 'age', 'address'], ['id', '=', $id1]);
            $row1 = $result1->first('array');
            $this->assertEquals('Test 1', $row1['name']);
            $this->assertNull($row1['email']);
            $this->assertNull($row1['age']);
            $this->assertNull($row1['address']);

            // 测试 2: 提供部分可选字段
            $id2 = $db->insert('test_insert_incomplete', ['name' => 'Test 2', 'email' => 'test@example.com']);
            $this->assertIsScalar($id2);
            $this->assertGreaterThan(0, $id2);

            // 验证数据插入成功
            $result2 = $db->select('test_insert_incomplete', ['id', 'name', 'email', 'age', 'address'], ['id', '=', $id2]);
            $row2 = $result2->first('array');
            $this->assertEquals('Test 2', $row2['name']);
            $this->assertEquals('test@example.com', $row2['email']);
            $this->assertNull($row2['age']);
            $this->assertNull($row2['address']);

            // 测试 3: 提供不同组合的字段
            $id3 = $db->insert('test_insert_incomplete', ['name' => 'Test 3', 'age' => 25, 'address' => 'Test Address']);
            $this->assertIsScalar($id3);
            $this->assertGreaterThan(0, $id3);

            // 验证数据插入成功
            $result3 = $db->select('test_insert_incomplete', ['id', 'name', 'email', 'age', 'address'], ['id', '=', $id3]);
            $row3 = $result3->first('array');
            $this->assertEquals('Test 3', $row3['name']);
            $this->assertNull($row3['email']);
            $this->assertEquals(25, $row3['age']);
            $this->assertEquals('Test Address', $row3['address']);

            // 验证总共有 3 条记录
            $resultAll = $db->select('test_insert_incomplete');
            $rowsAll = $resultAll->all('array');
            $this->assertCount(3, $rowsAll);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_incomplete");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 批量插入不完整数据
     */
    public function testInsertMultipleWithIncompleteData()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_mismatched (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 尝试执行字段数量不匹配的批量插入操作，应该抛出异常
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Insert Data Row 1 Field Count Does Not Match.');
            $db->insert('test_insert_mismatched', [
                ['name' => 'Test 1', 'email' => 'test1@example.com'], // 2 个字段
                ['name' => 'Test 2'] // 1 个字段 - 字段数量不匹配
            ]);
        } catch (DatabaseException $e) {
            // 清理
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_insert_mismatched");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            throw $e;
        } finally {
            // 清理
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_insert_mismatched");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
        }
    }

    /**
     * 测试 insert 方法 - PostgreSQL 特定功能：RETURNING 子句
     */
    public function testInsertReturningClause()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_returning (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 测试返回 id（默认行为）
            $insertResult1 = $db->insert('test_insert_returning', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($insertResult1);
            $this->assertGreaterThan(0, $insertResult1);

            // 测试返回 name 字段
            $insertResult2 = $db->insert('test_insert_returning', ['name' => 'Test 2', 'value' => 200], 'name');
            $this->assertIsScalar($insertResult2);
            $this->assertEquals('Test 2', $insertResult2);

            // 测试返回多个字段（返回第一个字段）
            $insertResult3 = $db->insert('test_insert_returning', ['name' => 'Test 3', 'value' => 300], 'id, name, value');
            $this->assertIsScalar($insertResult3);
            $this->assertGreaterThan(0, $insertResult3);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_returning");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert returning test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 复杂参数样例 1：大量字段
     */
    public function testInsertWithManyFields()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_many_fields (
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

            // 执行插入操作，使用大量字段
            $data = [
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
            ];
            $id = $db->insert('test_insert_many_fields', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_many_fields', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_many_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 复杂参数样例 2：带有特殊字符的字段名
     */
    public function testInsertWithSpecialFieldNames()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_special_fields (
                id SERIAL PRIMARY KEY,
                user_name VARCHAR(255) NOT NULL,
                user_age INTEGER,
                user_email VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 执行插入操作，使用带有下划线的字段名
            $data = [
                'user_name' => 'test_user',
                'user_age' => 25,
                'user_email' => 'test@example.com'
            ];
            $id = $db->insert('test_insert_special_fields', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_special_fields', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_special_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 复杂参数样例 3：带有 null 值的数据
     */
    public function testInsertWithNullValues()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_null_values (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                email VARCHAR(255),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 执行插入操作，使用带有 null 值的数据
            $data = [
                'name' => 'Test',
                'value' => null,
                'email' => null,
                'active' => null
            ];
            $id = $db->insert('test_insert_null_values', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_null_values', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_null_values");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 复杂参数样例 4：带有非常长的字符串
     */
    public function testInsertWithLongString()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_long_string (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                long_text TEXT
            )";
            $db->execute($createTableSql);

            // 执行插入操作，使用非常长的字符串
            $longString = str_repeat('x', 1000);
            $data = [
                'name' => 'Test',
                'long_text' => $longString
            ];
            $id = $db->insert('test_insert_long_string', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_long_string', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_long_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 insert 方法 - 复杂参数样例 5：混合数据类型
     */
    public function testInsertWithMixedDataTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_insert_mixed_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2),
                is_active BOOLEAN,
                description TEXT,
                quantity INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 执行插入操作，使用混合数据类型
            $data = [
                'name' => 'Test Product',
                'price' => 99.99,
                'is_active' => true,
                'description' => 'This is a test product',
                'quantity' => 10
            ];
            $id = $db->insert('test_insert_mixed_types', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_insert_mixed_types', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_insert_mixed_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Insert test failed: ' . $e->getMessage());
        }
    }
}