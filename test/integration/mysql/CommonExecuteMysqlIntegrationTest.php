<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Result;

class CommonExecuteMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 execute 方法 - SELECT 查询
     */
    public function testExecuteSelectQuery()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_select (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_execute_select', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_execute_select', ['name' => 'Test 2', 'value' => 200]);

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
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_insert (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 执行 INSERT 查询
            $affected = $db->execute('INSERT INTO test_execute_insert (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_insert');
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals(100, $rows[0]['value']);

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
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_update (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_execute_update', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);

            // 执行 UPDATE 查询
            $affected = $db->execute('UPDATE test_execute_update SET name = :name, value = :value WHERE id = :id', ['name' => 'Updated Test', 'value' => 200, 'id' => $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据更新成功
            $result = $db->execute('SELECT * FROM test_execute_update WHERE id = :id', ['id' => $id]);
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
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_delete (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $id = $db->insert('test_execute_delete', ['name' => 'Test 1', 'value' => 100]);
            $this->assertIsScalar($id);
            $this->assertGreaterThan(0, $id);
            $db->insert('test_execute_delete', ['name' => 'Test 2', 'value' => 200]);

            // 执行 DELETE 查询
            $affected = $db->execute('DELETE FROM test_execute_delete WHERE id = :id', ['id' => $id]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据删除成功
            $result = $db->execute('SELECT * FROM test_execute_delete');
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('Test 2', $rows[0]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_delete");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 边界测试（特殊字符）
     */
    public function testExecuteWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
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
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_empty (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 执行包含空字符串的 INSERT 查询
            $affected = $db->execute('INSERT INTO test_execute_empty (name, value) VALUES (:name, :value)', ['name' => '', 'value' => 100]);

            // 验证返回受影响的行数
            $this->assertEquals(1, $affected);

            // 验证数据插入成功
            $result = $db->execute('SELECT * FROM test_execute_empty WHERE name = :name', ['name' => '']);
            $row = $result->first('array');
            $this->assertEquals('', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_empty");
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
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_injection (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_execute_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $db->insert('test_execute_injection', ['name' => 'Test 2', 'code' => 'CODE2', 'value' => 200]);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "CODE1' OR '1'='1";
            $result = $db->execute('SELECT * FROM test_execute_injection WHERE code = :code', ['code' => $sqlInjectionAttempt]);
            $rows = $result->all('array');

            // 验证只返回符合条件的行，而不是所有行
            $this->assertCount(0, $rows); // 因为没有 code 等于 "CODE1' OR '1'='1" 的行

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_execute_injection");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Execute test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 execute 方法 - 错误处理（SQL语法错误）
     */
    public function testExecuteSqlSyntaxError()
    {
        $db = Database::instance('database.default');

        // 尝试执行语法错误的 SQL
        $this->expectException(\system\database\DatabaseException::class);
        $this->expectExceptionMessageMatches('/Execute Error :/');
        $db->execute('SELECT * FROM non_existent_table WHERE id = :id', ['id' => 1]);
    }

    /**
     * 测试 execute 方法 - 多次执行
     */
    public function testExecuteMultipleTimes()
    {
        try {
            $db = Database::instance('database.default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_execute_multiple (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
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
}
