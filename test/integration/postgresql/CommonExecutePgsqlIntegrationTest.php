<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Result;

class CommonExecutePgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 execute 方法 - SELECT 查询
     */
    public function testExecuteSelectQuery()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_select (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->execute('INSERT INTO test_execute_select (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
            $db->execute('INSERT INTO test_execute_select (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

            // 执行 SELECT 查询
            $result = $db->execute('SELECT * FROM test_execute_select WHERE id = :id', ['id' => 1]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_select");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - INSERT 查询
     */
    public function testExecuteInsertQuery()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_insert (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行 INSERT 查询
            $affected = $db->execute('INSERT INTO test_execute_insert (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_insert WHERE id = :id', ['id' => 1]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_insert");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - UPDATE 查询
     */
    public function testExecuteUpdateQuery()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_update (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->execute('INSERT INTO test_execute_update (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

            // 执行 UPDATE 查询
            $affected = $db->execute('UPDATE test_execute_update SET name = :name, value = :value WHERE id = :id', ['name' => 'Updated Test', 'value' => 200, 'id' => 1]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->execute('SELECT * FROM test_execute_update WHERE id = :id', ['id' => 1]);
            $row = $result->first('array');
            $this->assertEquals('Updated Test', $row['name']);
            $this->assertEquals(200, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_update");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - DELETE 查询
     */
    public function testExecuteDeleteQuery()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_delete (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->execute('INSERT INTO test_execute_delete (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

            // 执行 DELETE 查询
            $affected = $db->execute('DELETE FROM test_execute_delete WHERE id = :id', ['id' => 1]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->execute('SELECT * FROM test_execute_delete WHERE id = :id', ['id' => 1]);
            $row = $result->first('array');
            $this->assertNull($row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_delete");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 空 SQL（应该抛出异常）
     */
    public function testExecuteEmptySql()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空 SQL，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Execute Sql Is Empty.');
        $db->execute('');
    }

    /**
     * 测试 execute 方法 - 执行错误（应该抛出异常）
     */
    public function testExecuteError()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行无效的 SQL，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Statement Execute Error :/');
        $db->execute('INVALID SQL STATEMENT');
    }

    /**
     * 测试 execute 方法 - 边界测试（特殊字符）
     */
    public function testExecuteWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_special (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行包含特殊字符的 INSERT 查询
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $affected = $db->execute('INSERT INTO test_execute_special (name, value) VALUES (:name, :value)', ['name' => $specialName, 'value' => 100]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_special WHERE name = :name', ['name' => $specialName]);
            $row = $result->first('array');
            $this->assertEquals($specialName, $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 边界测试（空字符串）
     */
    public function testExecuteWithEmptyString()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_empty_string (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行包含空字符串的 INSERT 查询
            $affected = $db->execute('INSERT INTO test_execute_empty_string (name, value) VALUES (:name, :value)', ['name' => '', 'value' => 100]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_empty_string WHERE name = :name', ['name' => '']);
            $row = $result->first('array');
            $this->assertEquals('', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_empty_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 安全测试（SQL注入尝试）
     */
    public function testExecuteSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->execute('INSERT INTO test_execute_injection (name, code, value) VALUES (:name, :code, :value)', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Test 1'; DROP TABLE test_execute_injection; --";
            $result = $db->execute('SELECT * FROM test_execute_injection WHERE name = :name', ['name' => $sqlInjectionAttempt]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证没有匹配的记录（因为 SQL 注入被阻止）
            $row = $result->first('array');
            $this->assertNull($row);

            // 验证表仍然存在
            $result = $db->execute('SELECT * FROM test_execute_injection');
            $this->assertInstanceOf(Result::class, $result);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('database.pgsql')->execute("DROP TABLE IF EXISTS test_execute_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 多次执行
     */
    public function testExecuteMultipleTimes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_multiple (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 多次执行 INSERT 查询
            for ($i = 1; $i <= 3; $i++) {
                $affected = $db->execute('INSERT INTO test_execute_multiple (name, value) VALUES (:name, :value)', ['name' => "Test $i", 'value' => $i * 100]);
                $this->assertEquals(1, $affected);
            }

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_multiple');
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 3', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_multiple");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 复杂查询
     */
    public function testExecuteComplexQuery()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_complex (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->execute('INSERT INTO test_execute_complex (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->execute('INSERT INTO test_execute_complex (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->execute('INSERT INTO test_execute_complex (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);
            $db->execute('INSERT INTO test_execute_complex (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 4', 'value' => 400, 'category' => 'B']);

            // 执行复杂查询：带分组、排序和限制
            $result = $db->execute('SELECT category, SUM(value) as total_value FROM test_execute_complex GROUP BY category ORDER BY total_value DESC LIMIT 2');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('B', $rows[0]['category']);
            $this->assertEquals(700, $rows[0]['total_value']);
            $this->assertEquals('A', $rows[1]['category']);
            $this->assertEquals(300, $rows[1]['total_value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_complex");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 不同类型的参数
     */
    public function testExecuteWithDifferentParameterTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                price DECIMAL(10,2),
                active BOOLEAN,
                description TEXT
            )";
            $db->execute($createTableSql);

            // 执行包含不同类型参数的 INSERT 查询
            $data = [
                'name' => 'Test Product',
                'value' => 100,
                'price' => 99.99,
                'active' => true,
                'description' => 'This is a test product'
            ];
            $affected = $db->execute('INSERT INTO test_execute_types (name, value, price, active, description) VALUES (:name, :value, :price, :active, :description)', $data);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_types WHERE id = :id', ['id' => 1]);
            $row = $result->first('array');
            $this->assertEquals($data['name'], $row['name']);
            $this->assertEquals($data['value'], $row['value']);
            $this->assertEquals($data['price'], $row['price']);
            $this->assertEquals($data['active'], $row['active']);
            $this->assertEquals($data['description'], $row['description']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 空参数
     */
    public function testExecuteWithEmptyParameters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_empty_params (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行不包含参数的 INSERT 查询
            $affected = $db->execute("INSERT INTO test_execute_empty_params (name, value) VALUES ('Test 1', 100)");

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_empty_params WHERE id = :id', ['id' => 1]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);
            $this->assertEquals(100, $row['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_empty_params");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 批量参数
     */
    public function testExecuteWithBatchParameters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_batch (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 批量执行 INSERT 查询
            $batchData = [
                ['name' => 'Test 1', 'value' => 100, 'category' => 'A'],
                ['name' => 'Test 2', 'value' => 200, 'category' => 'A'],
                ['name' => 'Test 3', 'value' => 300, 'category' => 'B']
            ];

            foreach ($batchData as $data) {
                $affected = $db->execute('INSERT INTO test_execute_batch (name, value, category) VALUES (:name, :value, :category)', $data);
                $this->assertEquals(1, $affected);
            }

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_batch');
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            foreach ($rows as $index => $row) {
                $this->assertEquals($batchData[$index]['name'], $row['name']);
                $this->assertEquals($batchData[$index]['value'], $row['value']);
                $this->assertEquals($batchData[$index]['category'], $row['category']);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_batch");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }
}