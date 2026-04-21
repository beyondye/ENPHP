<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class SqliteTest extends TestCase
{
 

    /**
     * 测试 Sqlite 构造函数 - 默认配置（内存数据库）
     */
    public function testConstructorDefaultConfig()
    {
        $sqlite = new Sqlite();
        $this->assertInstanceOf(Sqlite::class, $sqlite);
    }

    /**
     * 测试 Sqlite 构造函数 - 自定义配置
     */
    public function testConstructorCustomConfig()
    {
        $config = [
            'database' => ':memory:',
            'persistent' => true
        ];
        $sqlite = new Sqlite($config);
        $this->assertInstanceOf(Sqlite::class, $sqlite);
    }

    /**
     * 测试 Sqlite 构造函数 - 空数据库名称（应该抛出异常）
     */
    public function testConstructorEmptyDatabase()
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('SQLite Database Name Is Required.');
        new Sqlite(['database' => '']);
    }

    /**
     * 测试 Sqlite 构造函数 - 连接错误（应该抛出异常）
     */
    public function testConstructorConnectionError()
    {
        // 使用一个无效的数据库路径来触发连接错误
        // 假设 /invalid/path 不存在或无法访问
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessageMatches('/SQLite Database Connection Error :/');
        new Sqlite(['database' => '/invalid/path/to/database.sqlite']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 空数据（应该抛出异常）
     */
    public function testUpsertEmptyData()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Upsert Data Is Empty.');
        $sqlite->upsert('test_table', []);
    }

    /**
     * 测试 Sqlite upsert 方法 - 插入新数据
     */
    public function testUpsertInsert()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入数据
        $id = $sqlite->upsert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $this->assertIsScalar($id);  // 改为标量检查
        $this->assertEquals('1', $id);  // 使用字符串比较

        // 验证数据插入成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Test 1', $row['name']);
        $this->assertEquals(100, $row['value']);
    }

    /**
     * 测试 Sqlite upsert 方法 - 更新现有数据
     */
    public function testUpsertUpdate()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入初始数据
        $id = $sqlite->upsert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 更新数据
        $updatedId = $sqlite->upsert('test_table', ['id' => $id, 'name' => 'Updated Test', 'value' => 200]);
        $this->assertEquals($id, $updatedId);

        // 验证数据更新成功
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
    }

    /**
     * 测试 Sqlite select 方法 - 基本查询
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
        $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $sqlite->insert('test_table', ['name' => 'Test 2', 'value' => 200]);

        // 执行查询
        $result = $sqlite->select('test_table', ['*']);
        $rows = $result->all('array');

        // 验证查询结果
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
    }

    /**
     * 测试 Sqlite insert 方法 - 插入多条数据
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

        // 插入多条数据
        $id = $sqlite->insert(
            'test_table',
            [['name' => 'Test 1', 'value' => 100],
            ['name' => 'Test 2', 'value' => 200],
            ['name' => 'Test 3', 'value' => 300]]
        );

        // 验证插入成功
        $this->assertIsScalar($id);
        $result = $sqlite->execute('SELECT COUNT(*) as count FROM test_table');
        $row = $result->first('array');
        $this->assertEquals(3, $row['count']);
    }

    /**
     * 测试 Sqlite update 方法
     */
    public function testUpdate()
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
        $id = $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 更新数据
        $affected = $sqlite->update('test_table', ['name' => 'Updated Test', 'value' => 200], ['id', '=', $id]);

        // 验证更新成功
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT * FROM test_table WHERE id = :id', ['id' => $id]);
        $row = $result->first('array');
        $this->assertEquals('Updated Test', $row['name']);
        $this->assertEquals(200, $row['value']);
    }

    /**
     * 测试 Sqlite delete 方法
     */
    public function testDelete()
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
        $id1 = $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);
        $id2 = $sqlite->insert('test_table', ['name' => 'Test 2', 'value' => 200]);

        // 删除数据
        $affected = $sqlite->delete('test_table', ['id', '=', $id1]);

        // 验证删除成功
        $this->assertEquals(1, $affected);
        $result = $sqlite->execute('SELECT COUNT(*) as count FROM test_table');
        $row = $result->first('array');
        $this->assertEquals(1, $row['count']);
    }

    /**
     * 测试 Sqlite 事务方法
     */
    public function testTransaction()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 开始事务
        $this->assertTrue($sqlite->transaction());

        // 插入数据
        $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 回滚事务
        $this->assertTrue($sqlite->rollback());

        // 验证数据未插入
        $result = $sqlite->execute('SELECT COUNT(*) as count FROM test_table');
        $row = $result->first('array');
        $this->assertEquals(0, $row['count']);

        // 再次开始事务
        $this->assertTrue($sqlite->transaction());

        // 插入数据
        $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 提交事务
        $this->assertTrue($sqlite->commit());

        // 验证数据已插入
        $result = $sqlite->execute('SELECT COUNT(*) as count FROM test_table');
        $row = $result->first('array');
        $this->assertEquals(1, $row['count']);
    }

    /**
     * 测试 Sqlite close 方法
     */
    public function testClose()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);
        $this->assertTrue($sqlite->close());
    }

  

    /**
     * 测试 Sqlite effected 方法
     */
    public function testEffected()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入数据
        $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 验证 effected 方法
        $this->assertEquals(1, $sqlite->effected());
    }

    /**
     * 测试 Sqlite lastid 方法
     */
    public function testLastid()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 创建测试表
        $createTableSql = "CREATE TABLE IF NOT EXISTS test_table (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            value INTEGER
        )";
        $sqlite->execute($createTableSql);

        // 插入数据
        $id = $sqlite->insert('test_table', ['name' => 'Test 1', 'value' => 100]);

        // 验证 lastid 方法
        $this->assertEquals($id, $sqlite->lastid());
    }

    /**
     * 辅助方法：检查数组是否包含指定键
     */
    private function isset(array $array, string $key): void
    {
        $this->assertArrayHasKey($key, $array);
    }
}