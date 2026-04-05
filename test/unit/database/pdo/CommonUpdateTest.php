<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonUpdateTest extends TestCase
{
    /**
     * 测试 update 方法 - 基本更新功能
     */
    public function testUpdateBasic()
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

        // 执行更新操作
        $affected = $sqlite->update('test_table', ['name' => 'Updated Test', 'value' => 200], ['id', '=', 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
    }

    /**
     * 测试 update 方法 - 空表名（应该抛出异常）
     */
    public function testUpdateEmptyTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行空表名的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Table Is Empty.');
        $sqlite->update('', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 只包含空白字符的表名（应该抛出异常）
     */
    public function testUpdateWhitespaceTable()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试执行只包含空白字符的表名的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Table Is Empty.');
        $sqlite->update('   ', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空数据（应该抛出异常）
     */
    public function testUpdateEmptyData()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 尝试执行空数据的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Data Is Empty.');
        $sqlite->update('test_table', [], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 空 WHERE 条件（应该抛出异常）
     */
    public function testUpdateEmptyWhere()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 尝试执行空 WHERE 条件的更新操作，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Update Where Condition Is Empty.');
        $sqlite->update('test_table', ['name' => 'Updated Test']);
    }

    /**
     * 测试 update 方法 - 执行错误（应该抛出异常）
     */
    public function testUpdateExecuteError()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 尝试更新不存在的表，应该抛出异常
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/Update Execute Error :/');
        $sqlite->update('non_existent_table', ['name' => 'Updated Test'], ['id', '=', 1]);
    }

    /**
     * 测试 update 方法 - 边界测试（特殊字符）
     */
    public function testUpdateWithSpecialCharacters()
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

        // 执行包含特殊字符的更新操作
        $specialName = "Updated Test's data with \"quotes\" and special chars: !@#$%^&*()";
        $affected = $sqlite->update('test_table', ['name' => $specialName], ['id', '=', 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals($specialName, $row['name']);
    }

    /**
     * 测试 update 方法 - 安全测试（SQL注入尝试）
     */
    public function testUpdateSqlInjectionAttempt()
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
        $affected = $sqlite->update('test_table', ['value' => 999], ['id', '=', $sqlInjectionAttempt]);

        // 验证只更新了符合条件的行，而不是所有行
        $this->assertEquals(0, $affected); // 因为没有 id 等于 "1 OR 1=1" 的行

        // 验证数据未被更新
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertEquals(100, $rows[0]['value']);
        $this->assertEquals(200, $rows[1]['value']);
    }

    /**
     * 测试 update 方法 - 验证受影响的行数
     */
    public function testUpdateAffectedRows()
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

        // 执行更新操作
        $affected = $sqlite->update('test_table', ['value' => 300], ['value', '<', 150]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);
        $this->assertEquals(1, $sqlite->effected());

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE value = :value', ['value' => 300]);
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 update 方法 - 不同形式的 WHERE 条件
     */
    public function testUpdateWithDifferentWhereForms()
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
        for ($i = 1; $i <= 3; $i++) {
            $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test ' . $i, 'value' => $i * 100]);
        }

        // 测试形式 1: update('table', $data, [1]) - 默认字段名为 id，操作符为 =
        $affected = $sqlite->update('test_table', ['value' => 999], [1]);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals(999, $row['value']);

        // 测试形式 2: update('table', $data, ['id', 2]) - 字段名为 id，操作符为 =
        $affected = $sqlite->update('test_table', ['value' => 888], ['id', 2]);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 2]);
        $row = $result->first('array');
        $this->assertEquals(888, $row['value']);

        // 测试形式 3: update('table', $data, ['id', [3]]) - 字段名为 id，操作符为 in
        $affected = $sqlite->update('test_table', ['value' => 777], ['id', [3]]);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 3]);
        $row = $result->first('array');
        $this->assertEquals(777, $row['value']);

        // 测试形式 4: update('table', $data, ['id', '=', '3']) - 字段名为 id，操作符为 =
        $affected = $sqlite->update('test_table', ['value' => 666], ['id', '=', '3']);
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 3]);
        $row = $result->first('array');
        $this->assertEquals(666, $row['value']);
    }

    /**
     * 测试 update 方法 - 空字符串参数
     */
    public function testUpdateWithEmptyStringParams()
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

        // 执行空字符串参数的更新操作
        $affected = $sqlite->update('test_table', ['name' => ''], ['id', '=', 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals('', $row['name']);

        // 执行使用空字符串作为条件的更新操作
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);
        $affected = $sqlite->update('test_table', ['value' => 999], ['name', '=', '']);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE name = :name', ['name' => '']);
        $row = $result->first('array');
        $this->assertEquals(999, $row['value']);
    }

    /**
     * 测试 update 方法 - 不同类型的数据
     */
    public function testUpdateWithDifferentDataTypes()
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

        // 插入测试数据
        $sqlite->execute('INSERT INTO test_table (name, value, price, active) VALUES (:name, :value, :price, :active)', ['name' => 'Test 1', 'value' => 100, 'price' => 99.99, 'active' => 1]);

        // 执行不同类型数据的更新操作
        $data = [
            'name' => 'Updated Test',
            'value' => 200,
            'price' => 199.99,
            'active' => 0
        ];
        $affected = $sqlite->update('test_table', $data, ['id', '=', 1]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => 1]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
        $this->assertEquals(199.99, $row['price']);
        $this->assertEquals(0, $row['active']);
    }

    /**
     * 测试 update 方法 - 多个 WHERE 条件
     */
    public function testUpdateWithMultipleWhereConditions()
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

        // 执行多个 WHERE 条件的更新操作
        $affected = $sqlite->update('test_table', ['value' => 999], [['category', '=', 'A'], ['value', '<', 150]]);

        // 验证返回受影响的行数
        $this->assertEquals(1, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE category = :category', ['category' => 'A']);
        $rows = $result->all('array');
        $this->assertEquals(999, $rows[0]['value']);
        $this->assertEquals(200, $rows[1]['value']);
    }

    /**
     * 测试 update 方法 - 更新不存在的记录
     */
    public function testUpdateNonExistentRecord()
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

        // 尝试更新不存在的记录
        $affected = $sqlite->update('test_table', ['name' => 'Updated Test'], ['id', '=', 999]);

        // 验证返回受影响的行数为 0
        $this->assertEquals(0, $affected);

        // 验证数据未被更新
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 update 方法 - 更新所有符合条件的记录
     */
    public function testUpdateAllMatchingRecords()
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

        // 更新所有 category 为 A 的记录
        $affected = $sqlite->update('test_table', ['value' => 999], ['category', '=', 'A']);

        // 验证返回受影响的行数
        $this->assertEquals(2, $affected);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE category = :category', ['category' => 'A']);
        $rows = $result->all('array');
        $this->assertEquals(999, $rows[0]['value']);
        $this->assertEquals(999, $rows[1]['value']);
    }
}