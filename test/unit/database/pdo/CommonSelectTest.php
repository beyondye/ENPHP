<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;
use system\database\pdo\Result;

class CommonSelectTest extends TestCase
{
    /**
     * 测试 select 方法 - 基本查询
     */
    public function testSelectBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);

        // 执行基本查询
        $result = $sqlite->select('test_table');

        // 验证返回 Result 对象
        $this->assertInstanceOf(Result::class, $result);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
        $this->assertEquals('Test 3', $rows[2]['name']);
    }

    /**
     * 测试 select 方法 - 空表名（应该抛出异常）
     */
    public function testSelectEmptyTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空表名的查询，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty.');
        $sqlite->select('');
    }

    /**
     * 测试 select 方法 - 只包含空白字符的表名（应该抛出异常）
     */
    public function testSelectWhitespaceTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行只包含空白字符的表名的查询，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Select Table Name Is Empty');
        $sqlite->select('   ');
    }

    /**
     * 测试 select 方法 - 指定字段
     */
    public function testSelectWithSpecificFields()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 执行指定字段的查询
        $result = $sqlite->select('test_table', ['name']);

        // 验证查询结果
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);
        $this->assertArrayNotHasKey('id', $row);
        $this->assertArrayNotHasKey('value', $row);
    }

    /**
     * 测试 select 方法 - WHERE 条件
     */
    public function testSelectWithWhereCondition()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);

        // 执行带 WHERE 条件的查询
        $result = $sqlite->select('test_table', ['*'], ['value', '>', 150]);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);
        $this->assertEquals('Test 3', $rows[1]['name']);
    }

    /**
     * 测试 select 方法 - GROUP BY 分组
     */
    public function testSelectWithGroupBy()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);

        // 执行带 GROUP BY 的查询
        $result = $sqlite->select('test_table', ['category', 'SUM(value) as total'], [], ['category']);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('A', $rows[0]['category']);
        $this->assertEquals(300, $rows[0]['total']);
        $this->assertEquals('B', $rows[1]['category']);
        $this->assertEquals(300, $rows[1]['total']);
    }

    /**
     * 测试 select 方法 - HAVING 条件
     */
    public function testSelectWithHaving()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 3', 'value' => 150, 'category' => 'B']);

        // 执行带 HAVING 条件的查询
        $result = $sqlite->select('test_table', ['category', 'SUM(value) as total'], [], ['category'], ['total', '>', 250]);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('A', $rows[0]['category']);
        $this->assertEquals(300, $rows[0]['total']);
    }

    /**
     * 测试 select 方法 - ORDER BY 排序
     */
    public function testSelectWithOrderBy()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 3', 'value' => 300]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行带 ORDER BY 的查询（升序）
        $result = $sqlite->select('test_table', ['*'], [], [], [], ['value' => 'asc']);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
        $this->assertEquals('Test 3', $rows[2]['name']);

        // 执行带 ORDER BY 的查询（降序）
        $result = $sqlite->select('test_table', ['*'], [], [], [], ['value' => 'desc']);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
        $this->assertEquals('Test 1', $rows[2]['name']);
    }

    /**
     * 测试 select 方法 - LIMIT 限制
     */
    public function testSelectWithLimit()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        for ($i = 1; $i <= 5; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 执行带 LIMIT 的查询（只返回前 2 条）
        $result = $sqlite->select('test_table', ['*'], [], [], [], [], 2);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);

        // 执行带 LIMIT 和 OFFSET 的查询（跳过前 2 条，返回 2 条）
        $result = $sqlite->select('test_table', ['*'], [], [], [], [], [2, 2]);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);
    }

    /**
     * 测试 select 方法 - 执行错误（应该抛出异常）
     */
    public function testSelectExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试查询不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Select Execute Error :/');
        $sqlite->select('non_existent_table');
    }

    /**
     * 测试 select 方法 - 边界测试（特殊字符）
     */
    public function testSelectWithSpecialCharacters()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入包含特殊字符的数据
        $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => $specialName, 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行带特殊字符条件的查询
        $result = $sqlite->select('test_table', ['*'], ['name', '=', $specialName]);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals($specialName, $rows[0]['name']);
    }

    /**
     * 测试 select 方法 - 安全测试（SQL注入尝试）
     */
    public function testSelectSqlInjectionAttempt()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 尝试SQL注入
        $sqlInjectionAttempt = "1 OR 1=1";
        $result = $sqlite->select('test_table', ['*'], ['id', '=', $sqlInjectionAttempt]);

        // 验证只返回符合条件的行，而不是所有行
        $rows = $result->all('array');
        $this->assertCount(0, $rows); // 因为没有 id 等于 "1 OR 1=1" 的行
    }

    /**
     * 测试 select 方法 - 复杂查询（组合多个条件）
     */
    public function testSelectComplexQuery()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 1', 'value' => 100, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 2', 'value' => 200, 'category' => 'A']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 3', 'value' => 300, 'category' => 'B']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 4', 'value' => 400, 'category' => 'B']);
        $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', ['name' => 'Test 5', 'value' => 500, 'category' => 'C']);

        // 执行复杂查询
        $result = $sqlite->select(
            'test_table',
            ['category', 'MAX(value) as max_value'],
            ['value', '>', 150],
            ['category'],
            ['max_value', '<', 500],
            ['max_value' => 'desc'],
            2
        );

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('B', $rows[0]['category']);
        $this->assertEquals(400, $rows[0]['max_value']);
        $this->assertEquals('A', $rows[1]['category']);
        $this->assertEquals(200, $rows[1]['max_value']);
    }

    /**
     * 测试 select 方法 - 不同形式的 WHERE 条件
     */
    public function testSelectWithDifferentWhereForms()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        for ($i = 1; $i <= 5; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 测试形式 1: select('table', [], [1]) - 默认字段名为 id，操作符为 =
        $result = $sqlite->select('test_table', [], [1]);
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);

        // 测试形式 2: select('table', [], ['id', 2]) - 字段名为 id，操作符为 =
        $result = $sqlite->select('test_table', [], ['id', 2]);
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);

        // 测试形式 3: select('table', [], ['id', [3, 4]]) - 字段名为 id，操作符为 in
        $result = $sqlite->select('test_table', [], ['id', [3, 4]]);
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);

        // 测试形式 4: select('table', [], ['id', '=', '5']) - 字段名为 id，操作符为 =
        $result = $sqlite->select('test_table', [], ['id', '=', '5']);
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 5', $rows[0]['name']);

        // 测试形式 5: select('table', [], [['id', '>', 2], ['value', '<', 400]]) - 多个条件
        $result = $sqlite->select('test_table', [], [['id', '>', 2], ['value', '<', 400]]);
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
    }

    /**
     * 测试 select 方法 - 空字符串参数
     */
    public function testSelectWithEmptyStringParams()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入包含空字符串的数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => '', 'value' => 100]);
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 执行带空字符串条件的查询
        $result = $sqlite->select('test_table', ['*'], ['name', '=', '']);

        // 验证查询结果
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('', $rows[0]['name']);
        $this->assertEquals(100, $rows[0]['value']);
    }

    /**
     * 测试 select 方法 - 复杂 HAVING 条件
     */
    public function testSelectWithComplexHaving()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            category TEXT,
            subcategory TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $testData = [
            ['name' => 'Test 1', 'value' => 100, 'category' => 'A', 'subcategory' => 'A1'],
            ['name' => 'Test 2', 'value' => 200, 'category' => 'A', 'subcategory' => 'A1'],
            ['name' => 'Test 3', 'value' => 300, 'category' => 'A', 'subcategory' => 'A2'],
            ['name' => 'Test 4', 'value' => 400, 'category' => 'B', 'subcategory' => 'B1'],
            ['name' => 'Test 5', 'value' => 500, 'category' => 'B', 'subcategory' => 'B1'],
            ['name' => 'Test 6', 'value' => 600, 'category' => 'B', 'subcategory' => 'B2'],
            ['name' => 'Test 7', 'value' => 700, 'category' => 'C', 'subcategory' => 'C1'],
        ];

        foreach ($testData as $data) {
            $sqlite->execute('INSERT INTO test_table (name, value, category, subcategory) VALUES (:name, :value, :category, :subcategory)', $data);
        }

        // 测试 1: 复杂 HAVING 条件 - 多个聚合函数
        $result = $sqlite->select(
            'test_table',
            ['category', 'SUM(value) as total_value', 'COUNT(*) as count'],
            [],
            ['category'],
            [['total_value', '>', 500, 'and'], ['count', '>=', 2]]
        );

        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('A', $rows[0]['category']);
        $this->assertEquals(600, $rows[0]['total_value']);
        $this->assertEquals(3, $rows[0]['count']);
        $this->assertEquals('B', $rows[1]['category']);
        $this->assertEquals(1500, $rows[1]['total_value']);
        $this->assertEquals(3, $rows[1]['count']);

        // 测试 2: 复杂 HAVING 条件 - 嵌套分组
        $result = $sqlite->select(
            'test_table',
            ['category', 'subcategory', 'AVG(value) as avg_value'],
            [],
            ['category', 'subcategory'],
            ['avg_value', '>', 300]
        );

        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('B', $rows[0]['category']);
        $this->assertEquals('B1', $rows[0]['subcategory']);
        $this->assertEquals(450, $rows[0]['avg_value']);
        $this->assertEquals('B', $rows[1]['category']);
        $this->assertEquals('B2', $rows[1]['subcategory']);
        $this->assertEquals(600, $rows[1]['avg_value']);
        $this->assertEquals('C', $rows[2]['category']);
        $this->assertEquals('C1', $rows[2]['subcategory']);
        $this->assertEquals(700, $rows[2]['avg_value']);

        // 测试 3: 复杂 HAVING 条件 - 结合 WHERE 条件
        $result = $sqlite->select(
            'test_table',
            ['category', 'MAX(value) as max_value', 'MIN(value) as min_value'],
            ['value', '>', 200],
            ['category'],
            ['max_value', '>', 300]
        );

        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('B', $rows[0]['category']);
        $this->assertEquals(600, $rows[0]['max_value']);
        $this->assertEquals(400, $rows[0]['min_value']);
        $this->assertEquals('C', $rows[1]['category']);
        $this->assertEquals(700, $rows[1]['max_value']);
        $this->assertEquals(700, $rows[1]['min_value']);
    }

    /**
     * 测试 select 方法 - HAVING 条件使用不同操作符
     */
    public function testSelectWithHavingDifferentOperators()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            category TEXT
        )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        $testData = [
            ['name' => 'Test 1', 'value' => 100, 'category' => 'A'],
            ['name' => 'Test 2', 'value' => 200, 'category' => 'A'],
            ['name' => 'Test 3', 'value' => 300, 'category' => 'B'],
            ['name' => 'Test 4', 'value' => 400, 'category' => 'B'],
            ['name' => 'Test 5', 'value' => 500, 'category' => 'B'],
            ['name' => 'Test 6', 'value' => 600, 'category' => 'C'],
        ];

        foreach ($testData as $data) {
            $sqlite->execute('INSERT INTO test_table (name, value, category) VALUES (:name, :value, :category)', $data);
        }

        // 测试 HAVING 条件使用不同操作符
        // 测试 1: 使用 = 操作符
        $result = $sqlite->select(
            'test_table',
            ['category', 'COUNT(*) as count'],
            [],
            ['category'],
            ['count', '=', 2]
        );

        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('A', $rows[0]['category']);

        // 测试 2: 使用 != 操作符
        $result = $sqlite->select(
            'test_table',
            ['category', 'COUNT(*) as count'],
            [],
            ['category'],
            ['count', '!=', 2]
        );

        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('B', $rows[0]['category']);
        $this->assertEquals('C', $rows[1]['category']);

        // 测试 3: 使用 >= 操作符
        $result = $sqlite->select(
            'test_table',
            ['category', 'SUM(value) as total'],
            [],
            ['category'],
            ['total', '>=', 900]
        );

        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('B', $rows[0]['category']);
    }

    /**
     * 测试 select 方法 - 复杂的 LIMIT 场景
     */
    public function testSelectWithComplexLimit()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        value INTEGER
    )";
        $sqlite->execute($createTableSql);

        // 插入测试数据
        for ($i = 1; $i <= 5; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 测试 1: LIMIT 为 0
        $result = $sqlite->select('test_table', ['*'], [], [], [], [], 0);
        $rows = $result->all('array');
        $this->assertCount(0, $rows);

        // 测试 2: LIMIT 大于实际数据量
        $result = $sqlite->select('test_table', ['*'], [], [], [], [], 10);
        $rows = $result->all('array');
        $this->assertCount(5, $rows);

        // 测试 3: OFFSET 大于实际数据量
        $result = $sqlite->select('test_table', ['*'], [], [], [], [], [10, 0]);
        $rows = $result->all('array');
        $this->assertCount(5, $rows);

        // 测试 4: LIMIT 与 ORDER BY 结合
        $result = $sqlite->select('test_table', ['*'], [], [], [], ['value' => 'desc'], 3);
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 5', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);
        $this->assertEquals('Test 3', $rows[2]['name']);

        // 测试 5: LIMIT 与 WHERE 条件结合
        $result = $sqlite->select('test_table', ['*'], ['value', '>', 200], [], [], [], 2);
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 3', $rows[0]['name']);
        $this->assertEquals('Test 4', $rows[1]['name']);

        // 测试 6: 复杂的 LIMIT 和 OFFSET 组合
        $result = $sqlite->select('test_table', ['*'], [], [], [], ['id' => 'asc'], [2, 1]);
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 2', $rows[0]['name']);/*  */
        $this->assertEquals('Test 3', $rows[1]['name']);
    }
}
