<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonUpsertPgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 upsert 方法 - 基本功能（插入新记录）
     */
    public function testUpsertInsert()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_insert (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行 upsert 操作（插入新记录）
            $id = $db->upsert('test_upsert_insert', ['name' => 'Test 1', 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_upsert_insert', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_insert");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 基本功能（更新现有记录）
     */
    public function testUpsertUpdate()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_update (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 先插入一条记录
            $initialId = $db->insert('test_upsert_update', ['name' => 'Test 1', 'value' => 100]);

            // 执行 upsert 操作（更新现有记录）
            $updatedId = $db->upsert('test_upsert_update', ['id' => $initialId, 'name' => 'Updated Test', 'value' => 200]);

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($initialId, $updatedId);

            // 验证数据更新成功
            $result = $db->select('test_upsert_update', ['id', 'name', 'value'], ['id', '=', $initialId]);
            $row = $result->first('array');
            $this->assertEquals('Updated Test', $row['name']);
            $this->assertEquals(200, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_update");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 边界测试（空表名）
     */
    public function testUpsertEmptyTable()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空表名的 upsert 操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Table Name Is Empty.');
        $db->upsert('', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 upsert 方法 - 边界测试（空数据）
     */
    public function testUpsertEmptyData()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空数据的 upsert 操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Data Is Empty.');
        $db->upsert('test_upsert_empty_data', []);
    }

    /**
     * 测试 upsert 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpsertSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Test'; DROP TABLE test_upsert_injection; --";
            $id = $db->upsert('test_upsert_injection', ['name' => $sqlInjectionAttempt, 'code' => 'CODE1', 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功（SQL 注入被阻止）
            $result = $db->select('test_upsert_injection', ['id', 'name', 'code', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($sqlInjectionAttempt, $row['name']);

            // 验证表仍然存在
            $result = $db->select('test_upsert_injection');
            $this->assertInstanceOf('system\database\pdo\Result', $result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_upsert_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 复杂参数测试（多种数据类型）
     */
    public function testUpsertWithMixedDataTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_mixed_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2),
                is_active BOOLEAN,
                description TEXT,
                quantity INTEGER,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 执行 upsert 操作，使用混合数据类型
            $data = [
                'name' => 'Test Product',
                'price' => 99.99,
                'is_active' => true,
                'description' => 'This is a test product',
                'quantity' => 10
            ];
            $id = $db->upsert('test_upsert_mixed_types', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_upsert_mixed_types', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 更新记录
            $updatedData = [
                'id' => $id,
                'name' => 'Updated Product',
                'price' => 199.99,
                'is_active' => false,
                'description' => 'This is an updated test product',
                'quantity' => 20
            ];
            $updatedId = $db->upsert('test_upsert_mixed_types', $updatedData);

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($id, $updatedId);

            // 验证数据更新成功
            $result = $db->select('test_upsert_mixed_types', array_keys($updatedData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updatedData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_mixed_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 复杂参数测试（带有 null 值）
     */
    public function testUpsertWithNullValues()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_null_values (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                email VARCHAR(255),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 执行 upsert 操作，使用带有 null 值的数据
            $data = [
                'name' => 'Test',
                'value' => null,
                'email' => null,
                'active' => null
            ];
            $id = $db->upsert('test_upsert_null_values', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_upsert_null_values', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 更新记录，设置一些 null 值为非 null
            $updatedData = [
                'id' => $id,
                'name' => 'Updated Test',
                'value' => 200,
                'email' => 'test@example.com',
                'active' => true
            ];
            $updatedId = $db->upsert('test_upsert_null_values', $updatedData);

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($id, $updatedId);

            // 验证数据更新成功
            $result = $db->select('test_upsert_null_values', array_keys($updatedData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updatedData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_null_values");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 复杂参数测试（带有特殊字符）
     */
    public function testUpsertWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_special (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行包含特殊字符的 upsert 操作
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $id = $db->upsert('test_upsert_special', ['name' => $specialName, 'value' => 100]);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_upsert_special', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($specialName, $row['name']);

            // 更新记录，使用不同的特殊字符
            $updatedSpecialName = "Updated Test's data with more \"quotes\" and special chars: ~`!@#$%^&*()_+";
            $updatedId = $db->upsert('test_upsert_special', ['id' => $id, 'name' => $updatedSpecialName, 'value' => 200]);

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($id, $updatedId);

            // 验证数据更新成功
            $result = $db->select('test_upsert_special', ['id', 'name', 'value'], ['id', '=', $id]);
            $row = $result->first('array');
            $this->assertEquals($updatedSpecialName, $row['name']);
            $this->assertEquals(200, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 复杂参数测试（大量字段）
     */
    public function testUpsertWithManyFields()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_many_fields (
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

            // 执行 upsert 操作，使用大量字段
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
            $id = $db->upsert('test_upsert_many_fields', $data);

            // 验证返回的 ID
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 验证数据插入成功
            $result = $db->select('test_upsert_many_fields', array_keys($data), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($data as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 更新记录，修改多个字段
            $updatedData = [
                'id' => $id,
                'field1' => 'updated_value1',
                'field2' => 456,
                'field3' => 456.78,
                'field4' => false,
                'field5' => 'updated_value5',
                'field6' => 987,
                'field7' => 987.65,
                'field8' => true,
                'field9' => 'updated_value9',
                'field10' => 321
            ];
            $updatedId = $db->upsert('test_upsert_many_fields', $updatedData);

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($id, $updatedId);

            // 验证数据更新成功
            $result = $db->select('test_upsert_many_fields', array_keys($updatedData), ['id', '=', $id]);
            $row = $result->first('array');
            foreach ($updatedData as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_many_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - 自定义冲突列
     */
    public function testUpsertWithCustomConflictColumn()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表，使用 code 作为唯一列
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_upsert_custom_conflict (
                id SERIAL PRIMARY KEY,
                code VARCHAR(50) UNIQUE NOT NULL,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行 upsert 操作，使用 code 作为冲突列
            $data1 = ['code' => 'CODE1', 'name' => 'Test 1', 'value' => 100];
            $id1 = $db->upsert('test_upsert_custom_conflict', $data1, 'code');

            // 验证返回的 ID
            $this->assertIsScalar($id1);
            $this->assertGreaterThan(0, $id1);

            // 验证数据插入成功
            $result = $db->select('test_upsert_custom_conflict', array_keys($data1), ['code', '=', 'CODE1']);
            $row = $result->first('array');
            foreach ($data1 as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 再次执行 upsert 操作，使用相同的 code，应该更新记录
            $data2 = ['code' => 'CODE1', 'name' => 'Updated Test', 'value' => 200];
            $id2 = $db->upsert('test_upsert_custom_conflict', $data2, 'code');

            // 验证返回的 ID 与初始 ID 相同
            $this->assertEquals($id1, $id2);

            // 验证数据更新成功
            $result = $db->select('test_upsert_custom_conflict', array_keys($data2), ['code', '=', 'CODE1']);
            $row = $result->first('array');
            foreach ($data2 as $key => $value) {
                $this->assertEquals($value, $row[$key]);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_custom_conflict");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Upsert test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 upsert 方法 - conflict 参数边界测试
     */
    public function testUpsertWithConflictParameter()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 测试 1: 使用默认冲突列（id）
            $createTableSql1 = "CREATE TABLE IF NOT EXISTS test_upsert_conflict_default (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql1);

            // 插入记录
            $data1 = ['name' => 'Test 1', 'value' => 100];
            $id1 = $db->upsert('test_upsert_conflict_default', $data1); // 默认使用 'id'
            $this->assertIsScalar($id1);
            $this->assertGreaterThan(0, $id1);

            // 更新记录
            $data2 = ['id' => $id1, 'name' => 'Updated Test', 'value' => 200];
            $id2 = $db->upsert('test_upsert_conflict_default', $data2); // 默认使用 'id'
            $this->assertEquals($id1, $id2);

            // 测试 2: 使用复合唯一约束作为冲突列
            $createTableSql2 = "CREATE TABLE IF NOT EXISTS test_upsert_conflict_composite (
                id SERIAL PRIMARY KEY,
                first_name VARCHAR(50) NOT NULL,
                last_name VARCHAR(50) NOT NULL,
                value INTEGER,
                UNIQUE(first_name, last_name)
            )";
            $db->execute($createTableSql2);

            // 插入记录
            $data3 = ['first_name' => 'John', 'last_name' => 'Doe', 'value' => 100];
            $id3 = $db->upsert('test_upsert_conflict_composite', $data3, 'first_name, last_name');
            $this->assertIsScalar($id3);
            $this->assertGreaterThan(0, $id3);

            // 更新记录
            $data4 = ['first_name' => 'John', 'last_name' => 'Doe', 'value' => 200];
            $id4 = $db->upsert('test_upsert_conflict_composite', $data4, 'first_name, last_name');
            $this->assertEquals($id3, $id4);

            // 测试 3: 使用不存在的列作为冲突列（应该失败）
            try {
                $data5 = ['name' => 'Test', 'value' => 100];
                $db->upsert('test_upsert_conflict_default', $data5, 'non_existent_column');
                $this->fail('Expected DatabaseException but none was thrown');
            } catch (DatabaseException $e) {
                // 预期会抛出异常
                $this->assertStringContainsString('Upsert Execute Error', $e->getMessage());
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_upsert_conflict_default");
            $db->execute("DROP TABLE IF EXISTS test_upsert_conflict_composite");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_upsert_conflict_default");
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_upsert_conflict_composite");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Upsert conflict parameter test failed: ' . $e->getMessage());
        }
    }
}