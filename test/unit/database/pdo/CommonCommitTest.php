<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use system\database\pdo\Sqlite;
use system\database\DatabaseException;

class CommonCommitTest extends TestCase
{
    /**
     * 测试 commit 方法 - 基本功能
     */
    public function testCommitBasic()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 开始事务
        $sqlite->transaction();

        // 执行 commit 方法
        $result = $sqlite->commit();

        // 验证返回值
        $this->assertTrue($result);
    }

    /**
     * 测试 commit 方法 - 在事务中提交
     */
    public function testCommitInTransaction()
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
        $sqlite->transaction();

        // 插入数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 提交事务
        $result = $sqlite->commit();
        $this->assertTrue($result);

        // 验证数据已被插入
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(1, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
    }

    /**
     * 测试 commit 方法 - 多次提交
     */
    public function testCommitMultipleTimes()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 开始事务
        $sqlite->transaction();

        // 第一次提交
        $result1 = $sqlite->commit();
        $this->assertTrue($result1);

        // 第二次提交（在没有活动事务时会抛出异常）
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('No active transaction to commit.');
        $sqlite->commit();
    }

    /**
     * 测试 commit 方法 - 关闭连接后提交
     */
    public function testCommitAfterClose()
    {
        $sqlite = new Sqlite(['database' => ':memory:']);

        // 关闭连接
        $sqlite->close();

        // 尝试提交，应该抛出异常
        $this->expectException(Error::class);
        $sqlite->commit();
    }

    /**
     * 测试 commit 方法 - 嵌套事务
     */
    public function testCommitNestedTransaction()
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
        $sqlite->transaction();

        // 插入第一条数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 1', 'value' => 100]);

        // 提交事务
        $result = $sqlite->commit();
        $this->assertTrue($result);

        // 再次开始事务
        $sqlite->transaction();

        // 插入第二条数据
        $sqlite->execute('INSERT INTO test_table (name, value) VALUES (:name, :value)', ['name' => 'Test 2', 'value' => 200]);

        // 再次提交事务
        $result = $sqlite->commit();
        $this->assertTrue($result);

        // 验证两条数据都已被插入
        $result = $sqlite->execute('SELECT * FROM test_table');
        $rows = $result->all('array');
        $this->assertCount(2, $rows);
        $this->assertEquals('Test 1', $rows[0]['name']);
        $this->assertEquals('Test 2', $rows[1]['name']);
    }
}