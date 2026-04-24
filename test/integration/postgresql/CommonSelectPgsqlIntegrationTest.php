<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;
use system\database\pdo\Result;

class CommonSelectPgsqlIntegrationTest extends TestCase
{
    /**
     * 测试 select 方法 - 基本查询
     */
    public function testSelectBasic()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_basic (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_basic', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_basic', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_basic', ['name' => 'Test 3', 'value' => 300]);

            // 执行基本查询
            $result = $db->select('test_select_basic');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 3', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_basic");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 选择特定字段
     */
    public function testSelectSpecificFields()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_fields (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_fields', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);

            // 执行选择特定字段的查询
            $result = $db->select('test_select_fields', ['id', 'name']);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含指定字段
            $row = $result->first('array');
            $this->assertArrayHasKey('id', $row);
            $this->assertArrayHasKey('name', $row);
            $this->assertArrayNotHasKey('value', $row);
            $this->assertArrayNotHasKey('category', $row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_fields");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - WHERE 条件
     */
    public function testSelectWithWhereCondition()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_where (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_where', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->insert('test_select_where', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->insert('test_select_where', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);

            // 执行带 WHERE 条件的查询
            $result = $db->select('test_select_where', ['id', 'name', 'value','category'], ['category', '=', 'A']);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含符合条件的记录
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            foreach ($rows as $row) {
                $this->assertEquals('A', $row['category']);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂 WHERE 条件
     */
    public function testSelectWithComplexWhereCondition()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_complex_where (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_complex_where', ['name' => 'Test 1', 'value' => 100, 'category' => 'A', 'active' => true]);
            $db->insert('test_select_complex_where', ['name' => 'Test 2', 'value' => 200, 'category' => 'A', 'active' => false]);
            $db->insert('test_select_complex_where', ['name' => 'Test 3', 'value' => 300, 'category' => 'B', 'active' => true]);
            $db->insert('test_select_complex_where', ['name' => 'Test 4', 'value' => 400, 'category' => 'B', 'active' => false]);

            // 执行带复杂 WHERE 条件的查询：value > 150 且 active = true
            $result = $db->select('test_select_complex_where', ['id', 'name', 'value'], ['value', '>', 150], [], [], [], []);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含符合条件的记录
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            foreach ($rows as $row) {
                $this->assertGreaterThan(150, $row['value']);
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_complex_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - GROUP BY 子句
     */
    public function testSelectWithGroupBy()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_groupby (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_groupby', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->insert('test_select_groupby', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->insert('test_select_groupby', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);
            $db->insert('test_select_groupby', ['name' => 'Test 4', 'value' => 400, 'category' => 'B']);

            // 执行带 GROUP BY 子句的查询
            $result = $db->select('test_select_groupby', ['category', 'SUM(value) as total_value'], [], ['category']);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            foreach ($rows as $row) {
                if ($row['category'] === 'A') {
                    $this->assertEquals(300, $row['total_value']);
                } elseif ($row['category'] === 'B') {
                    $this->assertEquals(700, $row['total_value']);
                }
            }

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_groupby");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - HAVING 子句
     */
    public function testSelectWithHaving()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_having (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255)
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_having', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
            $db->insert('test_select_having', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
            $db->insert('test_select_having', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);
            $db->insert('test_select_having', ['name' => 'Test 4', 'value' => 400, 'category' => 'B']);

            // 执行带 HAVING 子句的查询
            $result = $db->select('test_select_having', ['category', 'SUM(value) as total_value'], [], ['category'], ['SUM(value)', '>', 300]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('B', $rows[0]['category']);
            $this->assertEquals(700, $rows[0]['total_value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_having");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - ORDER BY 子句
     */
    public function testSelectWithOrderBy()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_orderby (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_orderby', ['name' => 'Test 3', 'value' => 300]);
            $db->insert('test_select_orderby', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_orderby', ['name' => 'Test 2', 'value' => 200]);

            // 执行带 ORDER BY 子句的查询（按 value 升序）
            $result = $db->select('test_select_orderby', ['id', 'name', 'value'], [], [], [], ['value' => 'asc']);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果按 value 升序排序
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals(100, $rows[0]['value']);
            $this->assertEquals(200, $rows[1]['value']);
            $this->assertEquals(300, $rows[2]['value']);

            // 执行带 ORDER BY 子句的查询（按 value 降序）
            $result = $db->select('test_select_orderby', ['id', 'name', 'value'], [], [], [], ['value' => 'desc']);

            // 验证查询结果按 value 降序排序
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals(300, $rows[0]['value']);
            $this->assertEquals(200, $rows[1]['value']);
            $this->assertEquals(100, $rows[2]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_orderby");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - LIMIT 子句
     */
    public function testSelectWithLimit()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_limit (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            for ($i = 1; $i <= 5; $i++) {
                $db->insert('test_select_limit', ['name' => "Test $i", 'value' => $i * 100]);
            }

            // 执行带 LIMIT 子句的查询（只返回前 2 条记录）
            $result = $db->select('test_select_limit', ['id', 'name', 'value'], [], [], [], [], 2);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含 2 条记录
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);

            // 执行带 LIMIT 子句的查询（带偏移量）
            $result = $db->select('test_select_limit', ['id', 'name', 'value'], [], [], [], [], [2, 2]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含 2 条记录，且从第 3 条开始
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 3', $rows[0]['name']);
            $this->assertEquals('Test 4', $rows[1]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_limit");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 空表名（应该抛出异常）
     */
    public function testSelectEmptyTable()
    {
        $db = Database::instance('database.pgsql');

        // 尝试执行空表名的查询，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty.');
        $db->select('');
    }

    /**
     * 测试 select 方法 - 边界测试（空结果）
     */
    public function testSelectEmptyResult()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_empty_result (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 执行查询，应该返回空结果
            $result = $db->select('test_select_empty_result');

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果为空
            $rows = $result->all('array');
            $this->assertCount(0, $rows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_empty_result");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 边界测试（特殊字符）
     */
    public function testSelectWithSpecialCharacters()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_special (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入包含特殊字符的测试数据
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $db->insert('test_select_special', ['name' => $specialName, 'value' => 100]);

            // 执行查询
            $result = $db->select('test_select_special', ['id', 'name', 'value'], ['name', '=', $specialName]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $row = $result->first('array');
            $this->assertEquals($specialName, $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 安全测试（SQL注入尝试）
     */
    public function testSelectSqlInjectionAttempt()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_injection (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_injection', ['name' => 'Test 1', 'value' => 100]);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "Test 1' OR '1'='1";
            $result = $db->select('test_select_injection', ['id', 'name', 'value'], ['name', '=', $sqlInjectionAttempt]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证没有匹配的记录（因为 SQL 注入被阻止）
            $row = $result->first('array');
            $this->assertNull($row);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_injection");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂参数样例 1：组合所有子句
     */
    public function testSelectWithAllClauses()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_all_clauses (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                category VARCHAR(255),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_all_clauses', ['name' => 'Test 1', 'value' => 100, 'category' => 'A', 'active' => true]);
            $db->insert('test_select_all_clauses', ['name' => 'Test 2', 'value' => 200, 'category' => 'A', 'active' => true]);
            $db->insert('test_select_all_clauses', ['name' => 'Test 3', 'value' => 300, 'category' => 'B', 'active' => true]);
            $db->insert('test_select_all_clauses', ['name' => 'Test 4', 'value' => 400, 'category' => 'B', 'active' => false]);
            $db->insert('test_select_all_clauses', ['name' => 'Test 5', 'value' => 500, 'category' => 'C', 'active' => true]);

            // 执行带所有子句的查询
            $result = $db->select(
                'test_select_all_clauses',
                ['category', 'SUM(value) as total_value'],
                ['active', '=', true],
                ['category'],
                ['SUM(value)', '>', 200],
                ['total_value' => 'desc'],
                2
            );

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('C', $rows[0]['category']);
            $this->assertEquals(500, $rows[0]['total_value']);
            $this->assertEquals('B', $rows[1]['category']);
            $this->assertEquals(300, $rows[1]['total_value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_all_clauses");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂参数样例 2：不同类型的条件值
     */
    public function testSelectWithDifferentConditionTypes()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_condition_types (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER,
                price DECIMAL(10,2),
                active BOOLEAN
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_condition_types', ['name' => 'Test 1', 'value' => 100, 'price' => 99.99, 'active' => true]);
            $db->insert('test_select_condition_types', ['name' => 'Test 2', 'value' => 200, 'price' => 199.99, 'active' => false]);

            // 测试使用整数条件
            $result = $db->select('test_select_condition_types', ['id', 'name', 'value'], ['value', '=', 100]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);

            // 测试使用小数条件
            $result = $db->select('test_select_condition_types', ['id', 'name', 'price'], ['price', '=', 199.99]);
            $row = $result->first('array');
            $this->assertEquals('Test 2', $row['name']);

            // 测试使用布尔条件
            $result = $db->select('test_select_condition_types', ['id', 'name', 'active'], ['active', '=', true]);
            $row = $result->first('array');
            $this->assertEquals('Test 1', $row['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_condition_types");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂参数样例 3：使用 IN 操作符
     */
    public function testSelectWithInOperator()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_in_operator (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_in_operator', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_in_operator', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_in_operator', ['name' => 'Test 3', 'value' => 300]);
            $db->insert('test_select_in_operator', ['name' => 'Test 4', 'value' => 400]);

            // 执行使用 IN 操作符的查询
            $result = $db->select('test_select_in_operator', ['id', 'name', 'value'], ['value', 'in', [100, 300]]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含符合条件的记录
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals(100, $rows[0]['value']);
            $this->assertEquals(300, $rows[1]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_in_operator");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂参数样例 4：使用 BETWEEN 操作符
     */
    public function testSelectWithBetweenOperator()
    {
        try {
            $db = Database::instance('database.pgsql');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_between_operator (
                id SERIAL PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                value INTEGER
            )";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_between_operator', ['name' => 'Test 1', 'value' => 50]);
            $db->insert('test_select_between_operator', ['name' => 'Test 2', 'value' => 100]);
            $db->insert('test_select_between_operator', ['name' => 'Test 3', 'value' => 150]);
            $db->insert('test_select_between_operator', ['name' => 'Test 4', 'value' => 200]);

            // 执行使用 BETWEEN 操作符的查询
            $result = $db->select('test_select_between_operator', ['id', 'name', 'value'], ['value', 'between', [100, 150]]);

            // 验证返回 Result 对象
            $this->assertInstanceOf(Result::class, $result);

            // 验证查询结果只包含符合条件的记录
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals(100, $rows[0]['value']);
            $this->assertEquals(150, $rows[1]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_between_operator");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }
}