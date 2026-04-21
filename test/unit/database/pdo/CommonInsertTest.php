<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonInsertTest extends TestCase
{
    /**
     * 测试 insert 方法 - 基本插入功能（单条数据）
     */
    public function testInsertBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行插入操作
        $id = $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 验证返回的 ID
        $this->assertIsScalar($id);
        $this->assertEquals('1', $id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 insert 方法 - 批量插入功能
     */
    public function testInsertMultiple()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行批量插入操作
        $id = $sqlite->insert(
            'test_table',
            [['name' => 'Test 1', 'value' => 100],
            ['name' => 'Test 2', 'value' => 200],
            ['name' => 'Test 3', 'value' => 300]]
        );

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
        $this->assertEquals('Test 3', $rows[2]['name']);
    }

    /**
     * 测试 insert 方法 - 空表名（应该抛出异常）
     */
    public function testInsertEmptyTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空表名的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Table Name Is Empty.');
        $sqlite->insert('', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 insert 方法 - 只包含空白字符的表名（应该抛出异常）
     */
    public function testInsertWhitespaceTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行只包含空白字符的表名的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Table Name Is Empty.');
        $sqlite->insert('   ', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 insert 方法 - 空数据（应该抛出异常）
     */
    public function testInsertEmptyData()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空数据的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Is Empty.');
        $sqlite->insert('test_table', []);
    }

    /**
     * 测试 insert 方法 - 空数据行（应该抛出异常）
     */
    public function testInsertEmptyDataRow()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空数据行的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Is Empty.');
        $sqlite->insert('test_table', []);
    }

    /**
     * 测试 insert 方法 - 无效的数据行（应该抛出异常）
     */
    public function testInsertInvalidDataRow()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行无效数据行的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Row 1 Is Invalid.');
        $sqlite->insert('test_table', [['name' => 'Test 1', 'value' => 100], []]);
    }

    /**
     * 测试 insert 方法 - 数据行字段数量不匹配（应该抛出异常）
     */
    public function testInsertMismatchedFieldCount()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行字段数量不匹配的数据行的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Row 1 Field Count Does Not Match.');
        $sqlite->insert('test_table', [['name' => 'Test 1', 'value' => 100], ['name' => 'Test 2']]);
    }

    /**
     * 测试 insert 方法 - 字段键不是字符串（应该抛出异常）
     */
    public function testInsertNonStringFieldKey()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行字段键不是字符串的插入操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Insert Data Field Key Must Be String.');
        $sqlite->insert('test_table', [0 => 'Test 1', 1 => 100]);
    }

    /**
     * 测试 insert 方法 - 执行错误（应该抛出异常）
     */
    public function testInsertExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试插入不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Insert Execute Error :/');
        $sqlite->insert('non_existent_table', ['name' => 'Test 1', 'value' => 100]);
    }

    /**
     * 测试 insert 方法 - 边界测试（特殊字符）
     */
    public function testInsertWithSpecialCharacters()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行包含特殊字符的插入操作
        $specialName = "Test's data with \"quotes\" and special chars: !@#$%^&*()";
        $id = $sqlite->insert('test_table', ['name' => $specialName, 'value' => 100]);

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals($specialName, $row['name']);
    }

    /**
     * 测试 insert 方法 - 安全测试（SQL注入尝试）
     */
    public function testInsertSqlInjectionAttempt()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 尝试SQL注入
        $sqlInjectionAttempt = "Test'; DROP TABLE test_table; --";
        $id = $sqlite->insert('test_table', ['name' => $sqlInjectionAttempt, 'value' => 100]);

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功，且表未被删除
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals($sqlInjectionAttempt, $row['name']);

        // 再次插入数据，验证表仍然存在
        $id2 = $sqlite->insert('test_table', ['name' => 'Another test', 'value' => 200]);
        $this->assertNotEquals($id, $id2);
    }

    /**
     * 测试 insert 方法 - 验证受影响的行数
     */
    public function testInsertAffectedRows()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行单条插入操作
        $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $this->assertEquals(1, $sqlite->effected());

        // 执行批量插入操作
        $sqlite->insert(
            'test_table',
            [['name' => 'Test 2', 'value' => 200],
            ['name' => 'Test 3', 'value' => 300]]   
        );
        $this->assertEquals(2, $sqlite->effected());
    }

    /**
     * 测试 insert 方法 - 不同类型的数据
     */
    public function testInsertWithDifferentDataTypes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER,
            price REAL,
            active INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行不同类型数据的插入操作
        $data = [
            'name' => 'Test Product',
            'value' => 100,
            'price' => 99.99,
            'active' => 1
        ];
        $id = $sqlite->insert('test_table', $data);

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Test Product', $row['name']);
        $this->assertEquals(100, $row['value']);
        $this->assertEquals(99.99, $row['price']);
        $this->assertEquals(1, $row['active']);
    }

    /**
     * 测试 insert 方法 - 空字符串参数
     */
    public function testInsertWithEmptyStringParams()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行空字符串参数的插入操作
        $id = $sqlite->insert('test_table', ['name' => '', 'value' => 100]);

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 insert 方法 - 插入包含 null 值的数据
     */
    public function testInsertWithNullValues()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 执行包含 null 值的插入操作
        $id = $sqlite->insert('test_table', ['name' => null, 'value' => 100]);

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertNull($row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 insert 方法 - 批量插入包含不同数据的记录
     */
    public function testInsertMultipleWithDifferentData()
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

        // 执行批量插入操作
        $id = $sqlite->insert(
            'test_table',
            [['name' => 'Test 1', 'value' => 100, 'category' => 'A'],
            ['name' => 'Test 2', 'value' => 200, 'category' => 'B'],
            ['name' => 'Test 3', 'value' => 300, 'category' => 'A']]    
        );

        // 验证返回的 ID
        $this->assertIsScalar($id);

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(3, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('A', $rows[0]['category']);
        $this->assertEquals('Test 2', $rows[1]['name']);
        $this->assertEquals('B', $rows[1]['category']);
        $this->assertEquals('Test 3', $rows[2]['name']);
        $this->assertEquals('A', $rows[2]['category']);
    }
}