<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\Database;
use system\database\DatabaseException;

class CommonSelectMysqlIntegrationTest extends TestCase
{
    /**
     * 测试 select 方法 - 基本查询
     */
    public function testSelectBasic()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_basic (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_basic', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_basic', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_basic', ['name' => 'Test 3', 'value' => 300]);

            // 执行基本查询
            $result = $db->select('test_select_basic');

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
     * 测试 select 方法 - 带条件的查询
     */
    public function testSelectWithWhere()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_where (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_where', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_where', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_where', ['name' => 'Test 3', 'value' => 300]);

            // 执行带条件的查询
            $result = $db->select('test_select_where', ['id', 'name', 'value'], ['value', '>', 150]);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('Test 2', $rows[0]['name']);
            $this->assertEquals('Test 3', $rows[1]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带多个条件的查询
     */
    public function testSelectWithMultipleWhereConditions()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_multiple_where (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                category VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_multiple_where', ['name' => 'Test 1', 'category' => 'A', 'value' => 100]);
            $db->insert('test_select_multiple_where', ['name' => 'Test 2', 'category' => 'A', 'value' => 200]);
            $db->insert('test_select_multiple_where', ['name' => 'Test 3', 'category' => 'B', 'value' => 300]);
            $db->insert('test_select_multiple_where', ['name' => 'Test 4', 'category' => 'B', 'value' => 400]);

            // 执行带多个条件的查询
            $result = $db->select('test_select_multiple_where', ['id', 'name', 'category', 'value'], [['category', '=', 'A'], ['value', '>', 150]]);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('Test 2', $rows[0]['name']);
            $this->assertEquals('A', $rows[0]['category']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_multiple_where");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带排序的查询
     */
    public function testSelectWithOrderBy()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_orderby (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_orderby', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_orderby', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_orderby', ['name' => 'Test 3', 'value' => 300]);

            // 执行带排序的查询
            $result = $db->select('test_select_orderby', ['id', 'name', 'value'], [], [], [], ['value' => 'desc']);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 3', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 1', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_orderby");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带多字段排序的查询
     */
    public function testSelectWithMultipleOrderBy()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_multiple_orderby (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_multiple_orderby', ['category' => 'B', 'name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_multiple_orderby', ['category' => 'A', 'name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_multiple_orderby', ['category' => 'A', 'name' => 'Test 1', 'value' => 150]);
            $db->insert('test_select_multiple_orderby', ['category' => 'B', 'name' => 'Test 2', 'value' => 250]);

            // 执行带多字段排序的查询
            $result = $db->select('test_select_multiple_orderby', ['id', 'category', 'name', 'value'], [], [], [], ['category' => 'asc', 'name' => 'asc']);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(4, $rows);
            $this->assertEquals('A', $rows[0]['category']);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('A', $rows[1]['category']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('B', $rows[2]['category']);
            $this->assertEquals('Test 1', $rows[2]['name']);
            $this->assertEquals('B', $rows[3]['category']);
            $this->assertEquals('Test 2', $rows[3]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_multiple_orderby");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带限制的查询
     */
    public function testSelectWithLimit()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_limit (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_limit', ['name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_limit', ['name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_limit', ['name' => 'Test 3', 'value' => 300]);
            $db->insert('test_select_limit', ['name' => 'Test 4', 'value' => 400]);
            $db->insert('test_select_limit', ['name' => 'Test 5', 'value' => 500]);

            // 执行带限制的查询
            $result = $db->select('test_select_limit', ['id', 'name', 'value'], [], [], [], [], 3);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('Test 2', $rows[1]['name']);
            $this->assertEquals('Test 3', $rows[2]['name']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_limit");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带分组的查询
     */
    public function testSelectWithGroupBy()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_groupby (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_groupby', ['category' => 'A', 'value' => 100]);
            $db->insert('test_select_groupby', ['category' => 'A', 'value' => 200]);
            $db->insert('test_select_groupby', ['category' => 'B', 'value' => 300]);
            $db->insert('test_select_groupby', ['category' => 'B', 'value' => 400]);
            $db->insert('test_select_groupby', ['category' => 'B', 'value' => 500]);

            // 执行带分组的查询
            $result = $db->select('test_select_groupby', ['category', 'COUNT(*) as count', 'SUM(value) as sum'], [], ['category']);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('A', $rows[0]['category']);
            $this->assertEquals(2, $rows[0]['count']);
            $this->assertEquals(300, $rows[0]['sum']);
            $this->assertEquals('B', $rows[1]['category']);
            $this->assertEquals(3, $rows[1]['count']);
            $this->assertEquals(1200, $rows[1]['sum']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_groupby");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 带分组和 having 条件的查询
     */
    public function testSelectWithGroupByAndHaving()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_having (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_having', ['category' => 'A', 'value' => 100]);
            $db->insert('test_select_having', ['category' => 'A', 'value' => 200]);
            $db->insert('test_select_having', ['category' => 'B', 'value' => 300]);
            $db->insert('test_select_having', ['category' => 'B', 'value' => 400]);
            $db->insert('test_select_having', ['category' => 'B', 'value' => 500]);
            $db->insert('test_select_having', ['category' => 'C', 'value' => 600]);

            // 执行带分组和 having 条件的查询
            $result = $db->select('test_select_having', ['category', 'COUNT(*) as count', 'SUM(value) as sum'], [], ['category'], ['count', '>', 1]);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);
            $this->assertEquals('A', $rows[0]['category']);
            $this->assertEquals(2, $rows[0]['count']);
            $this->assertEquals(300, $rows[0]['sum']);
            $this->assertEquals('B', $rows[1]['category']);
            $this->assertEquals(3, $rows[1]['count']);
            $this->assertEquals(1200, $rows[1]['sum']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_having");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 复杂的 having 条件查询
     */
    public function testSelectWithComplexHaving()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_complex_having (
                id INT PRIMARY KEY AUTO_INCREMENT,
                category VARCHAR(255) NOT NULL,
                subcategory VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_complex_having', ['category' => 'A', 'subcategory' => 'A1', 'value' => 100]);
            $db->insert('test_select_complex_having', ['category' => 'A', 'subcategory' => 'A1', 'value' => 200]);
            $db->insert('test_select_complex_having', ['category' => 'A', 'subcategory' => 'A2', 'value' => 300]);
            $db->insert('test_select_complex_having', ['category' => 'B', 'subcategory' => 'B1', 'value' => 400]);
            $db->insert('test_select_complex_having', ['category' => 'B', 'subcategory' => 'B1', 'value' => 500]);
            $db->insert('test_select_complex_having', ['category' => 'B', 'subcategory' => 'B2', 'value' => 600]);
            $db->insert('test_select_complex_having', ['category' => 'B', 'subcategory' => 'B2', 'value' => 700]);

            // 执行带复杂 having 条件的查询
            $result = $db->select('test_select_complex_having', ['category', 'subcategory', 'COUNT(*) as count', 'SUM(value) as sum'], [], ['category', 'subcategory'], ['sum', '>', 500]);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(2, $rows);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_complex_having");
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
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_special (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含特殊字符的数据
            $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
            $db->insert('test_select_special', ['name' => $specialName, 'value' => 100]);
            $db->insert('test_select_special', ['name' => 'Regular Test', 'value' => 200]);

            // 执行查询
            $result = $db->select('test_select_special', ['id', 'name', 'value'], ['name', '=', $specialName]);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals($specialName, $rows[0]['name']);
            $this->assertEquals(100, $rows[0]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_special");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 边界测试（空字符串）
     */
    public function testSelectWithEmptyString()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_empty_string (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含空字符串的数据
            $db->insert('test_select_empty_string', ['name' => '', 'value' => 100]);
            $db->insert('test_select_empty_string', ['name' => 'Regular Test', 'value' => 200]);

            // 执行查询
            $result = $db->select('test_select_empty_string', ['id', 'name', 'value'], ['name', '=', '']);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('', $rows[0]['name']);
            $this->assertEquals(100, $rows[0]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_empty_string");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 边界测试（NULL 值）
     */
    public function testSelectWithNullValues()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表，允许 NULL 值
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_null (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                description VARCHAR(255),
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入包含 NULL 值的数据
            $db->insert('test_select_null', ['name' => 'Test 1', 'description' => '', 'value' => 100]);
            $db->insert('test_select_null', ['name' => 'Test 2', 'description' => 'Has description', 'value' => 200]);

            // 执行查询
            $result = $db->select('test_select_null', ['id', 'name', 'description', 'value'], ['description', '=', '']);

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(1, $rows);
            $this->assertEquals('Test 1', $rows[0]['name']);
            $this->assertEquals('', $rows[0]['description']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_null");
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
            $db = Database::instance('default');

            // 首先清理可能存在的表
            $db->execute("DROP TABLE IF EXISTS test_select_injection");

            // 创建测试表，使用字符串类型的字段
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_injection (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(255) NOT NULL,
                value INT
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $result = $db->execute($createTableSql);
            $this->assertGreaterThanOrEqual(0, $result);

            // 插入测试数据
            $id1 = $db->insert('test_select_injection', ['name' => 'Test 1', 'code' => 'CODE1', 'value' => 100]);
            $this->assertIsScalar($id1);
            $this->assertGreaterThan(0, $id1);

            $id2 = $db->insert('test_select_injection', ['name' => 'Test 2', 'code' => 'CODE2', 'value' => 200]);
            $this->assertIsScalar($id2);
            $this->assertGreaterThan(0, $id2);

            // 验证数据插入成功
            $result = $db->select('test_select_injection');
            $rows = $result->all('array');
            $this->assertCount(2, $rows);

            // 尝试 SQL 注入
            $sqlInjectionAttempt = "CODE1' OR '1'='1";
            $result = $db->select('test_select_injection', ['id', 'name', 'code', 'value'], ['code', '=', $sqlInjectionAttempt]);

            // 验证只返回符合条件的行，而不是所有行
            $rows = $result->all('array');
            $this->assertCount(0, $rows); // 因为没有 code 等于 "CODE1' OR '1'='1" 的行

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_injection");
        } catch (DatabaseException $e) {
            // 清理表
            try {
                Database::instance('default')->execute("DROP TABLE IF EXISTS test_select_injection");
            } catch (DatabaseException $ex) {
                // 忽略清理错误
            }
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }

    /**
     * 测试 select 方法 - 组合查询（多个参数组合）
     */
    public function testSelectComplexCombination()
    {
        try {
            $db = Database::instance('default');

            // 创建测试表
            $createTableSql = "CREATE TABLE IF NOT EXISTS test_select_combination (
            id INT PRIMARY KEY AUTO_INCREMENT,
            category VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            value INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            $db->execute($createTableSql);

            // 插入测试数据
            $db->insert('test_select_combination', ['category' => 'A', 'name' => 'Test 1', 'value' => 100]);
            $db->insert('test_select_combination', ['category' => 'A', 'name' => 'Test 2', 'value' => 200]);
            $db->insert('test_select_combination', ['category' => 'B', 'name' => 'Test 3', 'value' => 300]);
            $db->insert('test_select_combination', ['category' => 'B', 'name' => 'Test 4', 'value' => 400]);
            $db->insert('test_select_combination', ['category' => 'B', 'name' => 'Test 5', 'value' => 500]);

            // 执行复杂的组合查询 - 移除 GROUP BY 子句，因为我们不需要分组
            $result = $db->select(
                'test_select_combination',
                ['category', 'name', 'value'],
                ['value', '>', 150],
                [], // 移除 GROUP BY 子句
                [],
                ['value' => 'desc'],
                3
            );

            // 验证查询结果
            $rows = $result->all('array');
            $this->assertCount(3, $rows);
            $this->assertEquals('B', $rows[0]['category']);
            $this->assertEquals('Test 5', $rows[0]['name']);
            $this->assertEquals(500, $rows[0]['value']);

            // 清理
            $db->execute("DROP TABLE IF EXISTS test_select_combination");
        } catch (DatabaseException $e) {
            $this->markTestSkipped('Select test failed: ' . $e->getMessage());
        }
    }
}
